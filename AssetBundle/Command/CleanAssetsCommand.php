<?php

namespace CleverAge\EAVManager\AssetBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Gaufrette\Filesystem;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Sidus\FileUploadBundle\Configuration\ResourceTypeConfiguration;
use Sidus\FileUploadBundle\Entity\ResourceRepository;
use Sidus\FileUploadBundle\Manager\ResourceManager;
use Sidus\FileUploadBundle\Model\ResourceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Cleanup extra files, orphan files and entities with missing files
 *
 * If no option specified, display interactive dialog and ask for each step
 * If at least an option is specified: Trigger only the specified actions
 * If shell is not interactive, will default to "no" at each steps and cancel the actions UNLESS --force is used
 */
class CleanAssetsCommand extends ContainerAwareCommand
{
    /** @var ResourceManager */
    protected $resourceManager;

    /** @var Registry */
    protected $doctrine;

    /** @var Filesystem[] */
    protected $fileSystems = [];

    /** @var FilesystemMap */
    protected $fileSystemMaps = [];

    /** @var array */
    protected $extraFiles = [];

    /** @var array */
    protected $missingFiles = [];

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('eavmanager:asset:clean')
            ->addOption('delete-extra', null, InputOption::VALUE_NONE, 'Delete files with no corresponding entity')
            ->addOption('delete-orphans', null, InputOption::VALUE_NONE, 'Delete orphan entities with no relations')
            ->addOption('delete-missing', null, InputOption::VALUE_NONE, 'Delete entities with missing file')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force actions (no interaction)')
            ->addOption('simulate', null, InputOption::VALUE_NONE, 'Do not remove anything, only simulate the action')
            ->setDescription('Cleanup orphan files and extra assets');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \LogicException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws \UnexpectedValueException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Check if this command can be launched ?
        $this->resourceManager = $this->getContainer()->get('sidus_file_upload.resource.manager');
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->fileSystemMaps = $this->getContainer()->get('knp_gaufrette.filesystem_map');

        foreach ($this->resourceManager->getResourceConfigurations() as $resourceConfiguration) {
            $fsKey = $resourceConfiguration->getFilesystemKey();
            if (!array_key_exists($fsKey, $this->fileSystems)) {
                $fs = $this->fileSystemMaps->get($fsKey);
                $this->fileSystems[$fsKey] = $fs;
            }
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws RuntimeException
     * @throws LogicException
     * @throws \RuntimeException
     *
     * @return int|null
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws MappingException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $executeAll = true;
        if ($input->getOption('delete-extra') || $input->getOption('delete-orphans')
            || $input->getOption('delete-missing')
        ) {
            $executeAll = false;
        }

        $this->computeFileSystemDifferences();

        if ($executeAll || $input->getOption('delete-extra')) {
            $this->executeDeleteExtra($input, $output);
        }

//        if ($executeAll || $input->getOption('delete-missing')) {
//            $this->executeDeleteMissing($input, $output);
//        }

        if ($executeAll || $input->getOption('delete-orphans')) {
            $this->executeDeleteOrphans($input, $output);
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('<info>Success</info>');
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \UnexpectedValueException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws RuntimeException
     * @throws \RuntimeException
     */
    protected function executeDeleteExtra(InputInterface $input, OutputInterface $output)
    {
        /** @var array $extraFiles */
        foreach ($this->extraFiles as $fsKey => $extraFiles) {
            $count = count($extraFiles);
            $files = implode(', ', $extraFiles);
            $m = '<error>NO FILE REMOVED : Please use the --force option in non-interactive mode to prevent';
            $m .= ' any mistake</error>';

            $messages = [
                'no_item' => "<comment>No file to remove in fs '{$fsKey}'</comment>",
                'info' => "<comment>The following files will be deleted in fs '{$fsKey}': {$files}</comment>",
                'skipping' => '<comment>Skipping file removal.</comment>',
                'error' => $m,
                'question' => "Are you sure you want to remove {$count} files in fs '{$fsKey}' ? y/[n]\n",
            ];

            if (!$this->askQuestion($input, $output, $extraFiles, $messages)) {
                continue;
            }

            $fs = $this->fileSystems[$fsKey];
            foreach ($extraFiles as $extraFile) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $output->writeln("<comment>Removing file {$extraFile}</comment>");
                }
                if (!$input->getOption('simulate')) {
                    $fs->delete($extraFile);
                }
            }

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln("<comment>{$count} files deleted in fs '{$fsKey}'</comment>");
            }
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function executeDeleteMissing(InputInterface $input, OutputInterface $output)
    {
        // @todo Implement : Careful with the problematic of the different fs / different entities
        $output->writeln('<error>Deleting entities with missing files is not supported for the moment.</error>');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @throws MappingException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws RuntimeException
     * @throws \BadMethodCallException
     */
    protected function executeDeleteOrphans(InputInterface $input, OutputInterface $output)
    {
        $associations = [];
        $reverseAssociations = [];
        $resourceEntities = [];
        foreach ($this->resourceManager->getResourceConfigurations() as $resourceConfiguration) {
            $resourceEntities[] = $resourceConfiguration->getEntity();
        }
        /** @var ClassMetadata[] $metadatas */
        $metadatas = $this->doctrine->getManager()->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            foreach ($resourceEntities as $entity) {
                if ($metadata->getName() === $entity) {
                    foreach ($metadata->getAssociationMappings() as $fieldName => $association) {
                        $associations[] = $association;
                    }
                }
                foreach ($metadata->getAssociationsByTargetClass($entity) as $fieldName => $association) {
                    $reverseAssociations[] = $association;
                }
            }
        }

        $foundEntities = $this->findAssociatedEntities($associations, $reverseAssociations);

        $this->removeOrphanEntities($input, $output, $foundEntities);
    }

    /**
     * @param array $associations
     * @param array $reverseAssociations
     *
     * @throws \InvalidArgumentException
     * @throws MappingException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws RuntimeException
     * @throws \BadMethodCallException
     *
     * @return array
     */
    protected function findAssociatedEntities(array $associations, array $reverseAssociations)
    {
        $foundEntities = [];

        // Collecting all resource entities with associations to other entities
        foreach ($associations as $association) {
            throw new \RuntimeException("Please test this code or contact the author: We never had this case in our data set, we can't assure it's going to behave like expected");
            $className = $association['sourceEntity'];
            /** @var EntityRepository $repository */
            $repository = $this->doctrine->getRepository($className);
            /** @var ClassMetadata $metadata */
            $metadata = $this->doctrine->getManager()->getClassMetadata($className);
            $qb = $repository
                ->createQueryBuilder('e')
                ->select("e.{$metadata->getSingleIdentifierColumnName()} AS id")
                ->where("e.{$association['fieldName']} IS NOT NULL")
            ;

            foreach ($qb->getQuery()->getArrayResult() as $result) {
                $value = $result['id'];
                $foundEntities[$className][$value] = $value;
            }
        }

        // Collecting all resource entities associated to other entities
        foreach ($reverseAssociations as $association) {
            $className = $association['targetEntity'];
            /** @var EntityRepository $repository */
            $repository = $this->doctrine->getRepository($association['sourceEntity']);
            /** @var ClassMetadata $metadata */
            $metadata = $this->doctrine->getManager()->getClassMetadata($className);
            $qb = $repository
                ->createQueryBuilder('e')
                ->select("r.{$metadata->getSingleIdentifierColumnName()} AS id")
                ->innerJoin("e.{$association['fieldName']}", 'r')
            ;

            foreach ($qb->getQuery()->getArrayResult() as $result) {
                $value = $result['id'];
                $foundEntities[$className][$value] = $value;
            }
        }

        return $foundEntities;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $foundEntities
     *
     * @throws \InvalidArgumentException
     * @throws MappingException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws RuntimeException
     * @throws \BadMethodCallException
     */
    protected function removeOrphanEntities(InputInterface $input, OutputInterface $output, array $foundEntities)
    {
        $em = $this->doctrine->getManager();

        foreach ($this->resourceManager->getResourceConfigurations() as $resourceConfiguration) {
            $repository = $this->getRepository($resourceConfiguration);
            $className = $resourceConfiguration->getEntity();
            /** @var ClassMetadata $metadata */
            $metadata = $this->doctrine->getManager()->getClassMetadata($className);
            $ids = isset($foundEntities[$className]) ? $foundEntities[$className] : [];
            $qb = $repository
                ->createQueryBuilder('e')
                ->where("e.{$metadata->getSingleIdentifierColumnName()} NOT IN (:ids)")
                ->setParameter('ids', $ids)
            ;

            $results = [];
            /** @var ResourceInterface $result */
            foreach ($qb->getQuery()->getResult() as $result) {
                // We filter the results based on their type, it's really important with single-table inheritance as
                // Doctrine will load all subtype for a current class and this cannot be done easily in the query.
                if ($result->getType() !== $resourceConfiguration->getCode()) {
                    continue;
                }
                $results[] = $result;
            }

            $messages = $this->getEntityRemovalMessages($metadata, $results);
            if (!$this->askQuestion($input, $output, $results, $messages)) {
                continue;
            }

            /** @var ResourceInterface $result */
            foreach ($results as $result) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $m = "<comment>Removing : {$result->getType()} #{$result->getId()}";
                    $m .= " : {$result->getFileName()} - {$result->getOriginalFileName()}</comment>";
                    $output->writeln($m);
                }
                if (!$input->getOption('simulate')) {
                    $em->remove($result);
                }
            }

            /** @noinspection DisconnectedForeachInstructionInspection */
            if (!$input->getOption('simulate')) {
                $em->flush();
            }
        }
    }

    /**
     * @param ClassMetadata $metadata
     * @param array         $results
     *
     * @return array
     * @throws \BadMethodCallException
     */
    protected function getEntityRemovalMessages(ClassMetadata $metadata, array $results)
    {
        $className = $metadata->getName();

        $ids = [];
        $primaryKeyReflection = $metadata->getSingleIdReflectionProperty();
        foreach ($results as $result) {
            $ids[] = $primaryKeyReflection->getValue($result);
        }
        $list = implode(', ', $ids);
        $info = "<comment>The following entities of class '{$className}' will be deleted: {$list}</comment>";

        $error = '<error>NO ENTITY REMOVED : Please use the --force option in non-interactive mode to prevent';
        $error .= ' any mistake</error>';

        $count = count($results);

        return [
            'no_item' => "<comment>No entity to remove of class '{$className}'</comment>",
            'info' => $info,
            'skipping' => '<comment>Skipping entity removal.</comment>',
            'error' => $error,
            'question' => "Are you sure you want to remove {$count} entities for class '{$className}' ? y/[n]\n",
        ];
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $items
     * @param array           $messages
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    protected function askQuestion(
        InputInterface $input,
        OutputInterface $output,
        array $items,
        array $messages
    ) {
        $count = count($items);
        if ($count === 0) {
            if (isset($messages['no_item']) && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($messages['no_item']);
            }

            return false;
        }

        if (isset($messages['info']) && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln($messages['info']);
        }

        if (!$input->getOption('force') && $input->isInteractive()) {
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');
            $questionMessage = "Are you sure you wan't to do this action ? y/[n]\n";
            if (isset($messages['question'])) {
                $questionMessage = $messages['question'];
            }
            $question = new Question($questionMessage, 'n');
            if ('y' !== strtolower($questionHelper->ask($input, $output, $question))) {
                if (isset($messages['skipping']) && $output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln($messages['skipping']);
                }

                return false;
            }
        } elseif (!$input->getOption('force')) {
            if (isset($messages['error']) && $output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln($messages['error']);
            }

            return false;
        }

        return true;
    }

    /**
     * Compute de differences between what's in the storage system and what's in the database
     */
    protected function computeFileSystemDifferences()
    {
        $entityFileNameByFilesystems = [];
        foreach ($this->resourceManager->getResourceConfigurations() as $resourceConfiguration) {
            $fsKey = $resourceConfiguration->getFilesystemKey();
            $fileNames = $this->getRepository($resourceConfiguration)->getFileNames()->toArray();
            if (!array_key_exists($fsKey, $entityFileNameByFilesystems)) {
                $entityFileNameByFilesystems[$fsKey] = $fileNames;
            } else {
                $entityFileNameByFilesystems[$fsKey] = array_merge($entityFileNameByFilesystems[$fsKey], $fileNames);
            }
        }

        foreach ($this->fileSystems as $fsKey => $fileSystem) {
            $existingFileNames = [];
            foreach ($fileSystem->keys() as $entityFileName) {
                if (in_array($entityFileName, ['.gitkeep'], true)) {
                    continue;
                }
                $existingFileNames[$entityFileName] = $entityFileName;
            }
            $entityFileNames = $entityFileNameByFilesystems[$fsKey];
            $this->extraFiles[$fsKey] = array_diff_key($existingFileNames, $entityFileNames);
            $this->missingFiles[$fsKey] = array_diff_key($entityFileNames, $existingFileNames);
        }
    }

    /**
     * @param ResourceTypeConfiguration $resourceConfiguration
     *
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    protected function getFileNames(ResourceTypeConfiguration $resourceConfiguration)
    {
        $fs = $this->resourceManager->getFilesystemForType($resourceConfiguration->getCode());

        return $fs->keys();
    }

    /**
     * @param ResourceTypeConfiguration $resourceConfiguration
     *
     * @return ResourceRepository
     */
    protected function getRepository(ResourceTypeConfiguration $resourceConfiguration)
    {
        return $this->doctrine->getRepository($resourceConfiguration->getEntity());
    }
}
