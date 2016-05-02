<?php

namespace CleverAge\EAVManager\InstallerBundle\Import;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Exception;
use Oneup\UploaderBundle\Uploader\Response\EmptyResponse;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use CleverAge\EAVManager\AssetBundle\Controller\BlueimpController;
use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FileUploadBundle\Entity\Resource as SidusResource;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use CleverAge\EAVManager\InstallerBundle\DataTransfer\ImportContext;

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

    /** @var string */
    protected $baseDirectory;

    /** @var string */
    protected $downloadsDirectory;

    /** @var string */
    protected $lastImportPath;

    /** @var string */
    protected $archiveDirectory;

    /** @var ImportContext */
    protected $importContext;

    /** @var OutputInterface */
    protected $output;

    /** @var DataInterface[] */
    protected $referencesToSave;

    /**
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     * @param ValidatorInterface $validator
     * @param EntityManager $manager
     * @param array $uploadManagers
     * @param string $baseDirectory
     * @param string $downloadsDirectory
     * @throws Exception
     */
    public function __construct(
        FamilyConfigurationHandler $familyConfigurationHandler,
        ValidatorInterface $validator,
        EntityManager $manager,
        array $uploadManagers,
        $baseDirectory,
        $downloadsDirectory
    ) {
        $this->familyConfigurationHandler = $familyConfigurationHandler;
        $this->validator = $validator;
        $this->manager = $manager;
        $this->uploadManagers = $uploadManagers;
        $this->baseDirectory = rtrim($baseDirectory, '/');
        $this->downloadsDirectory = rtrim($downloadsDirectory, '/');
        $this->archiveDirectory = $this->baseDirectory . '/archives';

        if (@mkdir($this->baseDirectory) && !is_dir($this->baseDirectory)) {
            throw new Exception("Unable to create directory {$this->baseDirectory}");
        }
        if (@mkdir($this->archiveDirectory) && !is_dir($this->archiveDirectory)) {
            throw new Exception("Unable to create directory {$this->archiveDirectory}");
        }
        $this->lastImportPath = "{$this->baseDirectory}/current_import.json";
        if (file_exists($this->lastImportPath)) {
            $data = json_decode(file_get_contents($this->lastImportPath), true);
            $this->importContext = ImportContext::unserialize($data);
        } else {
            $this->importContext = new ImportContext();
        }
    }


    /**
     * @param array $dump
     * @param null $filename
     * @return bool
     * @throws Exception
     */
    public function loadDump(array $dump, $filename = null)
    {
        if ($filename && $this->importContext->hasProcessedFile($filename)) {
            return true;
        }
        $this->manager->beginTransaction();
        $batch = $this->importContext->getBatchCount();
        foreach ($dump as $familyCode => $datas) {
            $i = 0;
            $i2 = 0;
            $family = $this->familyConfigurationHandler->getFamily($familyCode);
            if ($this->output) {
                $this->output->writeln("<info>Importing family {$family->getCode()}</info>");
                $progress = new ProgressBar($this->output, count($datas));
                $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            }
            foreach ($datas as $reference => $data) {
                if (isset($progress)) {
                    $progress->advance();
                }
                if ($this->importContext->hasReference($family->getCode(), $reference)) {
                    continue;
                }
                try {
                    $this->loadData($family, $data, $reference);
                } catch (Exception $e) {
                    $this->manager->rollback();
                    throw $e;
                }
                // Flush every $batch and restart script every $batch * 10 iteration
                $i++;
                if ($i % $batch === 0) {
                    $this->saveContext();
                    $i2++;
                    if ($i2 % 10 === 0) {
                        return false; // Necessary (evil) optimization
                    }
                    $this->manager->beginTransaction();
                }
            }
            if (isset($progress)) {
                $progress->finish();
                $this->output->writeln('');
            }
        }
        if ($filename) {
            $this->importContext->addProcessedFile($filename);
        }
        $this->saveContext();
        return true;
    }

    /**
     * @param FamilyInterface $family
     * @param array $data
     * @param string $reference
     * @return DataInterface
     * @throws \Exception
     */
    protected function loadData(FamilyInterface $family, array $data, $reference = null)
    {
        $entity = $this->getEntityOrCreate($family, $reference);
        foreach ($data as $attributeCode => $value) {
            if ($family->hasAttribute($attributeCode)) {
                $this->setEntityValue($entity, $family->getAttribute($attributeCode), $value);
            } else {
                $entity->set($attributeCode, $value);
            }
        }
        $violations = $this->validator->validate($entity);
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
//            dump(json_decode($this->container->get('serializer')->serialize($entity, 'json'), true));
            throw new \UnexpectedValueException("Invalid fixtures data for family '{$family->getCode()}' (reference: ".
                "{$reference}) and property '{$violation->getPropertyPath()}' : '{$violation->getMessage()}', given '{$violation->getInvalidValue()}'");
        }
        $this->persist($entity, $reference);
        return $entity;
    }

    /**
     * @param DataInterface $entity
     * @param AttributeInterface $attribute
     * @param mixed $value
     * @throws \Exception
     */
    protected function setEntityValue(DataInterface $entity, AttributeInterface $attribute, $value)
    {
        if ($attribute->getType()->isRelation()) {
            $value = $this->resolveReferences($entity, $attribute, $value);
        }
        if ($attribute->isMultiple()) {
            $entity->setValuesData($attribute, $value);
        } else {
            $entity->setValueData($attribute, $value);
        }
    }

    /**
     * @param DataInterface $data
     * @param AttributeInterface $attribute
     * @param $values
     * @return array
     * @throws \Exception
     */
    protected function resolveReferences(DataInterface $data, AttributeInterface $attribute, $values)
    {
        if (!$attribute->isMultiple()) {
            return $this->resolveReference($data, $attribute, $values);
        }
        $resolvedValues = [];
        foreach ($values as $value) {
            $resolvedValues[] = $this->resolveReference($data, $attribute, $value);
        }
        return $resolvedValues;
    }

    /**
     * @param DataInterface $data
     * @param AttributeInterface $attribute
     * @param $reference
     * @return mixed
     * @throws \Exception
     */
    protected function resolveReference(DataInterface $data, AttributeInterface $attribute, $reference)
    {
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
            return $this->createEntity($class, $reference);
        }
        $family = $this->familyConfigurationHandler->getFamily($familyCode);

        if (is_array($reference)) {
            return $this->loadData($family, $reference);
        }
        return $this->getEntityByReference($family, $reference);
    }

    /**
     * @param FamilyInterface $parentFamily
     * @param AttributeInterface $attribute
     * @throws MappingException|\UnexpectedValueException
     */
    protected function getTargetClass(FamilyInterface $parentFamily, AttributeInterface $attribute)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $this->manager->getClassMetadata($parentFamily->getValueClass());
        $mapping = $classMetadata->getAssociationMapping($attribute->getType()->getDatabaseType());
        if (empty($mapping['targetEntity'])) {
            throw new \UnexpectedValueException("Unable to find target class for attribute type: '{$attribute->getType()->getCode()}'");
        }
        return $mapping['targetEntity'];
    }

    /**
     * @param string $class
     * @param mixed $value
     * @return mixed
     * @throws \Exception
     */
    protected function createEntity($class, $value)
    {
        if (is_a($class, SidusResource::class, true)) {
            return $this->handleFileUpload($class, $value);
        }
        throw new \UnexpectedValueException("Unsupported class {$class}");
    }

    /**
     * @param $class
     * @param $value
     * @return SidusResource
     * @throws \Exception
     */
    protected function handleFileUpload($class, $value)
    {
        $filePath = $this->downloadsDirectory.'/'.$value;
        if (!file_exists($filePath)) {
            $error = "Unable to find file {$value}";
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

        $file = new HttpFile($filePath);
        $response = new EmptyResponse();
        $file = $uploadManager->handleManualUpload($file, $response);
        if (!$file instanceof SidusResource) {
            $errorClass = get_class($file);
            throw new \UnexpectedValueException("Unexpected response from file upload, got: {$errorClass}");
        }
        $file->setOriginalFileName($value);
        return $file;
    }


    /**
     * @param DataInterface $entity
     * @param string $reference
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
     * @throws Exception
     */
    protected function saveContext()
    {
        if ($this->output && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln("\n<comment>Flushing & commiting...</comment>");
        }
        $this->manager->flush();
        $this->manager->commit();

        foreach ($this->referencesToSave as $reference => $entity) {
            $this->importContext->addReference($entity->getFamilyCode(), $reference, $entity->getId());
        }

        if (!@file_put_contents($this->lastImportPath, json_encode($this->importContext))) {
            throw new Exception("Unable to save current context to {$this->lastImportPath}");
        }

        // Optimize memory consumption
        $this->manager->clear();
        gc_collect_cycles();

        if ($this->output && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('<comment>OK</comment>');
        }
    }

    /**
     * @throws Exception
     */
    public function terminate()
    {
        $this->importContext->terminate();
        $timestamp = $this->importContext->getEndedAt()->format(\DateTime::W3C);
        if (!@rename($this->lastImportPath, $this->archiveDirectory.'/'.$timestamp.'.json')) {
            throw new Exception('Unable to archive current import');
        }
    }

    /**
     * @param FamilyInterface $family
     * @param string $reference
     * @return DataInterface
     * @throws \Exception
     */
    protected function getEntityByReference(FamilyInterface $family, $reference)
    {
        if (!$this->importContext->hasReference($family->getCode(), $reference)) {
            throw new \UnexpectedValueException("Reference not found {$reference} for family {$family->getCode()}");
        }
        return $this->manager->find($family->getDataClass(), $this->importContext->getReference($family->getCode(), $reference));
    }

    /**
     * @param FamilyInterface $family
     * @param string $reference
     * @return DataInterface
     * @throws \Exception
     */
    protected function getEntityOrCreate(FamilyInterface $family, $reference)
    {
        if ($this->importContext->hasReference($family->getCode(), $reference)) {
            return $this->getEntityByReference($family, $reference);
        }
        return $family->createData();
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
}
