<?php

namespace CleverAge\EAVManager\ImportBundle\Import;

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
use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
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
    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var EntityManager */
    protected $manager;

    /** @var BlueimpController[] */
    protected $uploadManagers;

    /** @var DirectoryConfigurationHandler */
    protected $directoryConfigurationHandler;

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
     * @param FamilyConfigurationHandler    $familyConfigurationHandler
     * @param ValidatorInterface            $validator
     * @param EntityManager                 $manager
     * @param array                         $uploadManagers
     * @param DirectoryConfigurationHandler $directoryConfigurationHandler
     */
    public function __construct(
        FamilyConfigurationHandler $familyConfigurationHandler,
        ValidatorInterface $validator,
        EntityManager $manager,
        array $uploadManagers,
        DirectoryConfigurationHandler $directoryConfigurationHandler
    ) {
        $this->familyConfigurationHandler = $familyConfigurationHandler;
        $this->validator = $validator;
        $this->manager = $manager;
        $this->uploadManagers = $uploadManagers;
        $this->directoryConfigurationHandler = $directoryConfigurationHandler;
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
            $family = $this->familyConfigurationHandler->getFamily($familyCode);
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
        $accessor = PropertyAccess::createPropertyAccessor();
        if ($config && $config->getOption('update_existing_only')) {
            $entity = $this->getEntityByReference($family, $reference, true);
            if (null === $entity) {
                return null;
            }
        } else {
            $entity = $this->getEntityOrCreate($family, $reference);
        }

        foreach ($data as $attributeCode => $value) {
            if ($family->hasAttribute($attributeCode)) {
                // EAV Model property
                $this->setEntityValue($entity, $family->getAttribute($attributeCode), $value, $config);
            } else {
                // Standard relational property
                $accessor->setValue($entity, $attributeCode, $value);
            }
        }
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
     * @param DataInterface      $entity
     * @param AttributeInterface $attribute
     * @param mixed              $value
     * @param ImportConfig       $config
     *
     * @throws \Exception
     */
    protected function setEntityValue(
        DataInterface $entity,
        AttributeInterface $attribute,
        $value,
        ImportConfig $config = null
    ) {
        $ignoreMissing = false;
        $append = false;
        if ($config) {
            $attributeConfig = $config->getAttributeMapping($attribute->getCode());
            if (isset($attributeConfig['ignore_missing'])) {
                $ignoreMissing = $attributeConfig['ignore_missing'];
            }
            if (isset($attributeConfig['append'])) {
                $append = $attributeConfig['append'];
            }
        }
        if ($attribute->getType()->isRelation()) {
            $value = $this->resolveReferences($entity, $attribute, $value, $ignoreMissing, true);
        }

        // Special case for fields that are not strings/texts:
        if ($value === '' && !in_array($attribute->getType()->getDatabaseType(), ['stringValue', 'textValue'], true)) {
            $value = null; // Setting a non-text field to an empty string makes no sense
        }

        if ($attribute->getType()->isEmbedded()) {
            $this->handleEmbedded($entity, $attribute, $value, $append);
        } else {
            $entity->set($attribute->getCode(), $value);
        }
    }

    /**
     * @param DataInterface      $data
     * @param AttributeInterface $attribute
     * @param string             $value
     * @param bool               $append
     *
     * @throws \Exception
     */
    protected function handleEmbedded(DataInterface $data, AttributeInterface $attribute, $value, $append = false)
    {
        $entityValues = $this->resolveReferences($data, $attribute, $value);

        if (!$attribute->isCollection()) {
            $data->setValueData($attribute, $entityValues);

            return;
        }

        if (!$append) {
            $data->setValuesData($attribute, $entityValues);

            return;
        }

        foreach ($entityValues as $entityValue) {
            $data->addValueData($attribute, $entityValue);
        }
    }

    /**
     * @param DataInterface      $data
     * @param AttributeInterface $attribute
     * @param mixed              $values
     * @param bool               $ignoreMissing
     * @param bool               $partialLoad
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function resolveReferences(
        DataInterface $data,
        AttributeInterface $attribute,
        $values,
        $ignoreMissing = false,
        $partialLoad = false
    ) {
        if (!$attribute->isCollection()) {
            return $this->resolveReference($data, $attribute, $values, $ignoreMissing, $partialLoad);
        }
        $resolvedValues = [];
        /** @var array $values */
        foreach ($values as $value) {
            $entity = $this->resolveReference($data, $attribute, $value, $ignoreMissing, $partialLoad);
            if ($entity) { // Removing empty values: doesn't make sense in a collection.
                $resolvedValues[] = $entity;
            }
        }

        return $resolvedValues;
    }

    /**
     * @param DataInterface      $data
     * @param AttributeInterface $attribute
     * @param string|array       $reference
     * @param bool               $ignoreMissing
     * @param bool               $partialLoad
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function resolveReference(
        DataInterface $data,
        AttributeInterface $attribute,
        $reference,
        $ignoreMissing = false,
        $partialLoad = false
    ) {
        if (null === $reference) {
            return null;
        }

        $familyCode = null;
        if (isset($attribute->getFormOptions()['family'])) {
            $familyCode = $attribute->getFormOptions()['family'];
        } elseif ($attribute->getOption('family')) {
            $familyCode = $attribute->getOption('family');
        } else {
            $class = $this->getTargetClass($data->getFamily(), $attribute);

            return $this->createEntity($class, $reference); // @todo Not pertinent outside of uploads, use find instead
        }
        $family = $this->familyConfigurationHandler->getFamily($familyCode);

        // Case where the reference is actually an embed data
        if (is_array($reference)) {
            return $this->loadData($family, $reference);
        }

        return $this->getEntityByReference($family, $reference, $ignoreMissing, $partialLoad);
    }

    /**
     * @param FamilyInterface    $parentFamily
     * @param AttributeInterface $attribute
     *
     * @throws MappingException|\UnexpectedValueException
     */
    protected function getTargetClass(FamilyInterface $parentFamily, AttributeInterface $attribute)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $this->manager->getClassMetadata($parentFamily->getValueClass());
        $mapping = $classMetadata->getAssociationMapping($attribute->getType()->getDatabaseType());
        if (empty($mapping['targetEntity'])) {
            throw new \UnexpectedValueException(
                "Unable to find target class for attribute type: '{$attribute->getType()->getCode()}'"
            );
        }

        return $mapping['targetEntity'];
    }

    /**
     * @param string $class
     * @param mixed  $value
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function createEntity($class, $value)
    {
        if (is_a($class, SidusResource::class, true)) {
            return $this->handleFileUpload($class, $value);
        }
        throw new \UnexpectedValueException("Unsupported class {$class}");
    }

    /**
     * @param string $class
     * @param string $value
     *
     * @throws \Exception
     *
     * @return SidusResource
     */
    protected function handleFileUpload($class, $value)
    {
        if (!$value) {
            return null;
        }
        $filePath = $this->directoryConfigurationHandler->getDownloadsDirectory().'/'.$value;
        if (!file_exists($filePath)) {
            $error = "Unable to find file {$filePath}";
            if ($this->output) {
                $this->output->writeln("\n<error>{$error}</error>");
            }
            $this->importContext->addError($error);

            return null;
        }
        $type = call_user_func([$class, 'getType']);
        if (!isset($this->uploadManagers[$type])) {
            throw new \UnexpectedValueException("Unknown upload type {$type}");
        }
        $uploadManager = $this->uploadManagers[$type];

        // Copy file to tmp
        $tmpFilePath = sys_get_temp_dir().'/'.basename($value);
        if (!@copy($filePath, $tmpFilePath)) {
            throw new RuntimeException("Unable to copy file {$filePath} to temporary destination {$tmpFilePath}");
        }

        $file = new HttpFile($tmpFilePath);
        $response = new EmptyResponse();
        $file = $uploadManager->handleManualUpload($file, $response);
        if (!$file instanceof SidusResource) {
            $errorClass = get_class($file);
            throw new \UnexpectedValueException("Unexpected response from file upload, got: {$errorClass}");
        }
        $file->setOriginalFileName(basename($value));

        return $file;
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

    /**
     * @param FamilyInterface $family
     * @param mixed           $reference
     * @param bool            $ignoreMissing
     * @param bool            $partialLoad
     *
     * @throws \Exception
     *
     * @return DataInterface
     */
    protected function getEntityByReference(
        FamilyInterface $family,
        $reference,
        $ignoreMissing = false,
        $partialLoad = false
    ) {
        if (is_a($reference, $family->getDataClass())) {
            return $reference; // Case where data was already transformed elsewhere
        }

        if ('' === $reference || null === $reference) {
            return null;
        }

        if (!is_string($reference) && !is_int($reference)) {
            $type = is_object($reference) ? get_class($reference) : gettype($reference);
            throw new \UnexpectedValueException("Reference must be a string or an integer, '{$type}' given");
        }

        // If reference is already saved
        if (array_key_exists($reference, $this->referencesToSave)) {
            return $this->referencesToSave[$reference];
        }

        if ($this->importContext->hasReference($family->getCode(), $reference)) {
            $repo = $this->getRepository($family);

            return $repo->findByPrimaryKey($family, $reference, $partialLoad);
        }

        $entity = $this->findByIdentifier($family, $reference, $partialLoad);
        if ($entity || $ignoreMissing) {
            return $entity;
        }
        throw ReferenceNotFoundException::create($family, $reference);
    }

    /**
     * @param FamilyInterface $family
     * @param string          $reference
     *
     * @throws \Exception
     *
     * @return DataInterface
     */
    protected function getEntityOrCreate(FamilyInterface $family, $reference)
    {
        if (null !== $reference) {
            if ($this->importContext->hasReference($family->getCode(), $reference)) {
                return $this->getEntityByReference($family, $reference);
            }

            $entity = $this->findByIdentifier($family, $reference);
            if ($entity) {
                return $entity;
            }
        }

        if ($family->isSingleton()) {
            $entity = $this->getRepository($family)->getInstance($family);
        } else {
            $entity = $family->createData();
        }
        $attributeAsIdentifier = $family->getAttributeAsIdentifier();
        if ($attributeAsIdentifier) {
            $entity->set($attributeAsIdentifier->getCode(), $reference);
        }

        return $entity;
    }

    /**
     * @param FamilyInterface $family
     * @param string          $reference
     * @param bool            $partialLoad
     *
     * @throws \UnexpectedValueException
     * @throws ORMException
     * @throws ReferenceNotFoundException
     * @throws NoResultException
     * @throws MappingException
     * @throws \LogicException
     *
     * @return null|DataInterface
     */
    protected function findByIdentifier(FamilyInterface $family, $reference, $partialLoad = false)
    {
        $repository = $this->getRepository($family);

        try {
            return $repository->findByIdentifier($family, $reference, $this->idFallback, $partialLoad);
        } catch (NonUniqueResultException $e) {
            throw NonUniqueReferenceException::create($family, $reference, $e);
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return DataRepository
     */
    protected function getRepository(FamilyInterface $family)
    {
        return $this->manager->getRepository($family->getDataClass());
    }
}
