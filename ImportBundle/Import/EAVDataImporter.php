<?php

namespace CleverAge\EAVManager\ImportBundle\Import;

use Sidus\EAVModelBundle\Serializer\Denormalizer\EAVDataDenormalizer;
use Sidus\FileUploadBundle\Controller\BlueimpController;
use CleverAge\EAVManager\ImportBundle\Configuration\DirectoryConfigurationHandler;
use CleverAge\EAVManager\ImportBundle\DataTransfer\ImportContext;
use CleverAge\EAVManager\ImportBundle\Exception\NonUniqueReferenceException;
use CleverAge\EAVManager\ImportBundle\Exception\ReferenceNotFoundException;
use CleverAge\EAVManager\ImportBundle\Model\ImportConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Exception;
use Oneup\UploaderBundle\Uploader\Response\EmptyResponse;
use RuntimeException;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FileUploadBundle\Entity\Resource as SidusResource;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Imports data in the EAV model from an array:
 * $dump = [
 *      '<FamilyCode>' => [
 *          '<identifier>' => [
 *              'attributeCode => '...',
 *          ],
 *      ],
 * ];
 *
 * or directly from a flat array, specifying the family
 */
class EAVDataImporter
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var EntityManager */
    protected $manager;

    /** @var BlueimpController[] */
    protected $uploadManagers;

    /** @var DirectoryConfigurationHandler */
    protected $directoryConfigurationHandler;

    /** @var EAVDataDenormalizer */
    protected $dataDenormalizer;

    /** @var ImportContext */
    protected $importContext;

    /** @var OutputInterface */
    protected $output;

    /** @var DataInterface[] */
    protected $referencesToSave = [];

    /** @var bool */
    protected $idFallback = false;

    /** @var int */
    protected $lastFlushTime;

    /**
     * @param FamilyRegistry                $familyRegistry
     * @param ValidatorInterface            $validator
     * @param EntityManager                 $manager
     * @param array                         $uploadManagers
     * @param DirectoryConfigurationHandler $directoryConfigurationHandler
     * @param EAVDataDenormalizer           $dataDenormalizer
     */
    public function __construct(
        FamilyRegistry $familyRegistry,
        ValidatorInterface $validator,
        EntityManager $manager,
        array $uploadManagers,
        DirectoryConfigurationHandler $directoryConfigurationHandler,
        EAVDataDenormalizer $dataDenormalizer
    ) {
        $this->familyRegistry = $familyRegistry;
        $this->validator = $validator;
        $this->manager = $manager;
        $this->uploadManagers = $uploadManagers;
        $this->directoryConfigurationHandler = $directoryConfigurationHandler;
        $this->dataDenormalizer = $dataDenormalizer;
    }

    /**
     * @param array        $dump
     * @param null         $filename
     * @param ImportConfig $importConfig
     *
     * @throws Exception
     *
     * @return bool
     */
    public function loadDump(array $dump, $filename = null, ImportConfig $importConfig = null)
    {
        $importContext = $this->getImportContext($importConfig);
        if ($filename && $importContext->hasProcessedFile($filename)) {
            return true;
        }
        $this->manager->beginTransaction();
        $batch = $importContext->getBatchCount();
        /** @var array $datas */
        foreach ($dump as $familyCode => $datas) {
            $i = 0;
            $family = $this->familyRegistry->getFamily($familyCode);
            if ($this->output) {
                $this->output->writeln("<info>Importing family {$family->getCode()}</info>");
                $progress = new ProgressBar($this->output, count($datas));
                $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            }
            if ($family->isSingleton() && count($datas) > 1) {
                $m = "Family {$family->getCode()} is a singleton but more tha one data was provided";
                throw new \UnexpectedValueException($m);
            }
            foreach ($datas as $reference => $data) {
                /** @noinspection DisconnectedForeachInstructionInspection */
                if (isset($progress)) {
                    $progress->advance();
                }
                if ($importContext->hasReference($family->getCode(), $reference)) {
                    continue;
                }
                try {
                    $this->loadData($family, $data, $reference);
                } catch (Exception $e) {
                    $this->manager->rollback();
                    throw $e;
                }
                // Flush every $batch
                $i++;
                if ($i % $batch === 0) {
                    $this->saveContext();
                    $this->manager->beginTransaction();
                }
            }
            if (isset($progress)) {
                $progress->finish();
                $this->output->writeln('');
            }
        }
        if ($filename) {
            $importContext->addProcessedFile($filename);
        }
        $this->saveContext();

        return true;
    }


    /**
     * @param FamilyInterface $family
     * @param array           $dump
     * @param ProgressBar     $progress
     * @param ImportConfig    $config
     *
     * @throws Exception
     *
     * @return bool
     */
    public function loadBatch(
        FamilyInterface $family,
        array $dump,
        ProgressBar $progress = null,
        ImportConfig $config = null
    ) {
        $importContext = $this->getImportContext($config);
        $hasTransaction = false;
        foreach ($dump as $reference => $data) {
            /** @noinspection DisconnectedForeachInstructionInspection */
            if (isset($progress)) {
                $progress->advance();
            }
            if ($importContext->hasReference($family->getCode(), $reference)) {
                // Skip already imported references
                continue;
            }
            if (!$hasTransaction) {
                $this->manager->beginTransaction();
                $hasTransaction = true;
            }
            try {
                $this->loadData($family, $data, $reference, $config);
            } catch (Exception $e) {
                $this->manager->rollback();
                throw $e;
            }
        }
        if ($hasTransaction) {
            $this->saveContext();
        }

        return true;
    }

    /**
     * @throws RuntimeException
     */
    public function terminate()
    {
        $this->importContext->terminate();
        $timestamp = $this->importContext->getEndedAt()->format(\DateTime::W3C);
        $archiveDirectory = $this->directoryConfigurationHandler->getArchiveDirectory();
        $currentPath = $this->importContext->getCurrentPath();
        $configCode = $this->importContext->getConfigCode();
        $codeSuffix = $configCode ? '-'.$configCode : '';
        $destPath = "{$archiveDirectory}/{$timestamp}{$codeSuffix}.json";
        if (!@rename($currentPath, $destPath)) {
            throw new RuntimeException("Unable to archive current import to '{$destPath}'");
        }
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param FamilyInterface $family
     * @param array           $data
     * @param string          $reference
     * @param ImportConfig    $config
     *
     * @throws \Exception
     *
     * @return DataInterface|null
     */
    public function loadData(FamilyInterface $family, array $data, $reference = null, ImportConfig $config = null)
    {
        $entity = $this->dataDenormalizer->denormalize($data, $family->getCode());

        $violations = $this->validator->validate($entity);
        /** @var ConstraintViolationInterface $violation */
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($violations as $violation) {
            /** @noinspection DisconnectedForeachInstructionInspection */
            $message = "Invalid fixtures data for family '{$family->getCode()}' (reference: ";
            $message .= "{$reference}) and property '{$violation->getPropertyPath()}' : '{$violation->getMessage()}'";
            $message .= ", given '{$violation->getInvalidValue()}'";
            throw new \UnexpectedValueException($message);
        }
        $this->persist($entity, $reference);

        if (null === $this->lastFlushTime) {
            // If first imported data, log the time
            $this->lastFlushTime = time();
        }

        return $entity;
    }

    /**
     * @param bool $flush
     *
     * @throws RuntimeException
     * @throws OptimisticLockException
     */
    public function saveContext($flush = true)
    {
        if ($flush) {
            if ($this->output && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $this->output->writeln("\n<comment>Flushing & commiting...</comment>");
            }
            $this->manager->flush();
            $this->manager->commit();
        }

        $referenceCount = count($this->referencesToSave);
        foreach ($this->referencesToSave as $reference => $entity) {
            $this->importContext->addReference($entity->getFamilyCode(), $reference, $entity->getId());
        }
        $this->referencesToSave = [];
        unset($entity);

        $contextPath = $this->importContext->getCurrentPath();
        if (!@file_put_contents($contextPath, json_encode($this->importContext))) {
            throw new RuntimeException("Unable to save current context to '{$contextPath}'");
        }

        if ($flush) {
            // Optimize memory consumption
            $this->manager->clear();
            gc_collect_cycles();

            if ($this->output && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $timeDiff = time() - $this->lastFlushTime;
                $this->output->writeln(" <comment>{$referenceCount} imported references in {$timeDiff}s</comment>");
            }

            if ($this->output && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $this->output->writeln('<comment>OK</comment>');
            }

            $this->lastFlushTime = time();
        }
    }

    /**
     * @param ImportConfig $importConfig
     *
     * @throws \UnexpectedValueException
     *
     * @return ImportContext
     */
    public function getImportContext(ImportConfig $importConfig = null)
    {
        if (null === $this->importContext) {
            $currentImportPath = $this->getCurrentImportPath($importConfig);
            if (file_exists($currentImportPath)) {
                $this->importContext = $this->loadImport($currentImportPath);
            } else {
                $this->importContext = new ImportContext();
                $this->importContext->setCurrentPath($currentImportPath);
            }
        }

        return $this->importContext;
    }

    /**
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return ImportContext[]
     */
    public function getRunningImports()
    {
        $baseDirectory = $this->directoryConfigurationHandler->getBaseDirectory();
        $finder = new Finder();
        /** @var SplFileInfo[] $files */
        $files = $finder->in($baseDirectory)->name('current_import*.json')->files();

        $imports = [];
        foreach ($files as $file) {
            $imports[$file->getBasename()] = $this->loadImport($file->getPathname());
        }

        return $imports;
    }

    /**
     * @param int $count
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return ImportContext[]
     */
    public function getArchivedImports($count = 10)
    {
        $archiveDirectory = $this->directoryConfigurationHandler->getArchiveDirectory();
        $finder = new Finder();
        /** @var SplFileInfo[] $files */
        $files = $finder->in($archiveDirectory)->sortByModifiedTime()->name('*.json')->files();

        $imports = [];
        $i = 0;
        foreach ($files as $file) {
            if ($i >= $count) {
                break;
            }
            $i++;
            $imports[$file->getBasename()] = $this->loadImport($file->getPathname());
        }

        return $imports;
    }

    /**
     * @param string $filePath
     *
     * @throws \UnexpectedValueException
     *
     * @return ImportContext
     */
    protected function loadImport($filePath)
    {
        $data = json_decode(file_get_contents($filePath), true);
        if (null === $data) {
            throw new \UnexpectedValueException("Unable to load import context : '{$filePath}'");
        }

        return ImportContext::unserialize($data);
    }

    /**
     * @param ImportConfig|null $importConfig
     *
     * @return string
     */
    protected function getCurrentImportPath(ImportConfig $importConfig = null)
    {
        $baseDirectory = $this->directoryConfigurationHandler->getBaseDirectory();
        if ($importConfig) {
            return "{$baseDirectory}/current_import-{$importConfig->getCode()}.json";
        }

        return "{$baseDirectory}/current_import.json";
    }


    /**
     * @param DataInterface $entity
     * @param mixed         $reference
     *
     * @throws ORMInvalidArgumentException
     */
    protected function persist(DataInterface $entity, $reference = null)
    {
        $this->manager->persist($entity);
        if (null !== $reference) {
            $this->referencesToSave[$reference] = $entity;
        }
    }
}
