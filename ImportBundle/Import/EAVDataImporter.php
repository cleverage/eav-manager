<?php

namespace CleverAge\EAVManager\ImportBundle\Import;

use CleverAge\EAVManager\ImportBundle\Entity\ImportErrorLog;
use CleverAge\EAVManager\ImportBundle\Entity\ImportHistory;
use CleverAge\EAVManager\ImportBundle\Exception\InvalidImportException;
use CleverAge\EAVManager\ImportBundle\Transformer\EAVValueTransformerInterface;
use Sidus\FileUploadBundle\Controller\BlueimpController;
use CleverAge\EAVManager\ImportBundle\Model\ImportConfig;
use Doctrine\ORM\EntityManager;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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
class EAVDataImporter implements ContainerAwareInterface
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var EntityManager */
    protected $manager;

    /** @var BlueimpController[] */
    protected $uploadManagers;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ContainerInterface */
    protected $container;

    /** @var bool */
    protected $idFallback = false;

    /** @var int */
    protected $lastFlushTime;

    /**
     * @param FamilyRegistry        $familyRegistry
     * @param ValidatorInterface    $validator
     * @param EntityManager         $manager
     * @param array                 $uploadManagers
     * @param DenormalizerInterface $dataDenormalizer
     */
    public function __construct(
        FamilyRegistry $familyRegistry,
        ValidatorInterface $validator,
        EntityManager $manager,
        array $uploadManagers,
        DenormalizerInterface $dataDenormalizer
    ) {
        $this->familyRegistry = $familyRegistry;
        $this->validator = $validator;
        $this->manager = $manager;
        $this->uploadManagers = $uploadManagers;
        $this->denormalizer = $dataDenormalizer;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Import an array of data using the given configuration into the EAV manager
     *
     * @TODO Manage restart
     * @TODO find a way to keep references...
     *
     * @param ImportConfig  $config
     * @param callable|null $onProgress
     *
     * @throws \Exception
     *
     * @return ImportHistory
     */
    public function import(ImportConfig $config, callable $onProgress = null): ImportHistory
    {
        $history = new ImportHistory();
        $history->setStartedAt(new \DateTime());
        $history->setImportCode($config->getCode());
        $history->setStatus(ImportHistory::STATUS_IN_PROGRESS);

        $this->manager->persist($history);
        $this->manager->flush();

        $totalCount = null;
        $loadedCount = 0;

        try {
            $data = $config->getSource()->getData();
            $totalCount = count($data);
            $loadedCount = 0;

            // By default import everything at once
            $batchSize = $config->getOption('batch_size', $totalCount);

            while (count($data)) {
                $dataBatch = array_slice($data, 0, $batchSize, true);
                $data = array_slice($data, $batchSize, null, true);

                $loadedEntities = [];
                $this->manager->beginTransaction();

                // Create the entities from the current batch
                foreach ($dataBatch as $reference => $entityData) {
                    try {
                        $entity = $this->loadData($config->getFamily(), $entityData, $reference, $config);
                        $loadedEntities[$reference] = $entity;
                    } catch (InvalidImportException $e) {
                        $errorLog = ImportErrorLog::createFromError($e);
                        $history->addErrorLog($errorLog);
                    } catch (\UnexpectedValueException $e) {
                        $errorLog = new ImportErrorLog();
                        $errorLog->setMessage($e->getMessage());
                        $errorLog->setErrorJson(
                            json_encode(
                                [
                                    'reference' => $reference,
                                    'entity_data' => $entityData,
                                ]
                            )
                        );
                        $history->addErrorLog($errorLog);
                    } catch (\Exception $e) {
                        $this->manager->rollback();
                        throw $e;
                    }
                }

                $loadedCount += count($loadedEntities);
                $history->setMessage("Loaded {$loadedCount}/{$totalCount} entities");

                // Persist them
                $this->manager->persist($history);
                $this->manager->flush();
                $this->manager->commit();

                if ($onProgress) {
                    $onProgress($dataBatch, $loadedEntities);
                }

                // Flush memory
                foreach ($loadedEntities as $loadedEntity) {
                    $this->manager->detach($loadedEntity);
                }

                gc_collect_cycles();
            }

            $history->setStatus(ImportHistory::STATUS_SUCCESS);
        } catch (\Throwable $e) {
            if ($this->manager->isOpen()) {
                $history->setStatus(ImportHistory::STATUS_ERROR);
                $history->setMessage("Error after {$loadedCount}/{$totalCount} loaded entities: ".$e->getMessage());
                $history->setFinishedAt(new \DateTime());

                $this->manager->persist($history);
                $this->manager->flush();
            }

            throw $e;
        }

        $history->setFinishedAt(new \DateTime());

        $this->manager->persist($history);
        $this->manager->flush();
        $this->manager->clear();

        return $history;
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
        // Extract the data using the mapping
        $data = $this->mapValues($data, $config);

        // Global transformation
        if ($config && $config->getTransformer()) {
            $data = $config->getTransformer()->reverseTransform($family, $data);
        }

        // The denormalizer needs a reference to the family
        if (!array_key_exists('family', $data)) {
            $data['family'] = $family;
        }

        // Entity creation from data, using the EAV denormalizer
        /** @var DataInterface $entity */
        $entity = $this->denormalizer->denormalize($data, $family->getDataClass());

        // Data validation
        $violations = $this->validator->validate($entity);
        if (count($violations)) {
            throw InvalidImportException::create($family, $reference, $violations);
        }

        // Final save
        $this->manager->persist($entity);

        if (null === $this->lastFlushTime) {
            // If first imported data, log the time
            $this->lastFlushTime = time();
        }

        return $entity;
    }

    /**
     * @param array        $data
     * @param ImportConfig $importConfig
     *
     * @return array
     *
     * @throws \LogicException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \UnexpectedValueException
     */
    protected function mapValues(array $data, ImportConfig $importConfig): array
    {
        $mappedData = [];
        foreach ($importConfig->getMapping() as $attributeCode => $config) {
            if (isset($config['virtual']) && $config['virtual']) {
                continue;
            }

            // The imported column may not match the attribute name
            $importCode = $attributeCode;
            if ($config && array_key_exists('code', $config)) {
                // Note that the null value is allowed
                $importCode = $config['code'];
            }

            // Always use an array for checking
            $codesToCheck = $importCode;
            $hasMultipleCodes = true;
            if (!is_array($codesToCheck)) {
                $hasMultipleCodes = false;
                $codesToCheck = [$codesToCheck];
            }

            // Check column existence
            $valueMapping = [];
            foreach ($codesToCheck as $code) {
                if ($code === null) {
                    // Special case to allow the usage of 'code: ~' in config
                    $originalValue = false;
                } elseif (!$code || !array_key_exists($code, $data)) {
                    $m = "Missing data '{$code}' in import to be mapped to '{$attributeCode}' attribute";
                    // TODO use ImportErrorException
                    throw new \UnexpectedValueException($m);
                } else {
                    $originalValue = $data[$code];
                }
                $valueMapping[$code] = $originalValue;
            }

            // We may flatten the array if it was flat at first
            if ($hasMultipleCodes) {
                $value = $valueMapping;
            } else {
                $value = reset($valueMapping);
            }

            $mappedData[$attributeCode] = $this->transformValue($attributeCode, $value, $importConfig);
        }

        return $mappedData;
    }

    /**
     * @param string       $attributeCode
     * @param mixed        $value
     * @param ImportConfig $importConfig
     *
     * @throws \LogicException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \UnexpectedValueException
     *
     * @return mixed
     */
    protected function transformValue($attributeCode, $value, ImportConfig $importConfig)
    {
        // Check and filter out null values
        $currentFilterCallback = function ($item) use ($importConfig) {
            return $this->filterNullValue($item, $importConfig);
        };
        if (is_array($value) && !count(array_filter($value, $currentFilterCallback))) {
            return null;
        }
        if (!$this->filterNullValue($value, $importConfig)) {
            return null;
        }

        $attributeConfig = $importConfig->getMapping()[$attributeCode];
        $transformer = null;
        $attribute = null;
        $family = $importConfig->getFamily();

        // Find default transformers if no transformer is provided
        if ($family->hasAttribute($attributeCode) &&
            !(is_array($attributeConfig) && array_key_exists('transformer', $attributeConfig))
        ) {
            $attribute = $family->getAttribute($attributeCode);

            // If we put this in a transformer
            if ($attribute->isCollection()) {
                $transformer = $this->container->get('eavmanager.import.transformer.collection');
            }

            // Default DataTransformer for dates
            if (is_string($value) && !is_numeric($value)) {
                if ($attribute->getType()->getDatabaseType() === 'dateValue') {
                    $transformer = $this->container->get('eavmanager.import.transformer.simple_date');
                }
                if ($attribute->getType()->getDatabaseType() === 'datetimeValue') {
                    $transformer = $this->container->get('eavmanager.import.transformer.simple_datetime');
                }
            }

            if (is_array($value)) {
                $transformer = $this->container->get('eavmanager.import.transformer.attribute_concat');
            }

            // This default configuration will crash for multiple date/datetime attributes, @fixme
        }

        // Custom transformer in configuration
        if (isset($attributeConfig['transformer'])) {
            $transformer = $this->container->get(ltrim($attributeConfig['transformer'], '@'));
            if (!$transformer instanceof DataTransformerInterface
                && !$transformer instanceof EAVValueTransformerInterface
            ) {
                $m = "Transformer for attribute mapping '{$attributeCode}' must be a DataTransformerInterface";
                $m .= ' or EAVValueTransformerInterface';
                // TODO use ImportErrorException
                throw new \UnexpectedValueException($m);
            }
        }

        // Apply the transformer
        if ($transformer) {
            if ($transformer instanceof EAVValueTransformerInterface) {
                if (!$attribute) {
                    throw new \Exception(
                        "Attribute {$attributeCode} does not exists in family {$family}"
                    );
                }
                $value = $transformer->reverseTransform($family, $attribute, $value, $attributeConfig);
            } else {
                $value = $transformer->reverseTransform($value);
            }
        }

        return $value;
    }

    /**
     * Helper method to use as a callback
     * Return false if the value is a NULL that should be ignored
     *
     * @param string       $value
     * @param ImportConfig $importConfig
     *
     * @return bool
     */
    // @codingStandardsIgnoreLine
    public function filterNullValue($value, ImportConfig $importConfig): bool
    {
        return !($value === '\\N' && $importConfig->getOption('ignore_mysql_null'));
    }
}
