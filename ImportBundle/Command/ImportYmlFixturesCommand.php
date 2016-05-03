<?php

namespace CleverAge\EAVManager\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class ImportYmlFixturesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eavmanager:import:import-fixtures')
            ->setDescription('Import Yml fixtures');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Doctrine Optimization
        $this->getContainer()->get('doctrine')->getConnection()->getConfiguration()->setSQLLogger(null);

        // Disable Doctrine's event listener for publishing
        $this->getContainer()->get('sidus_eav_publishing.doctrine_orm.subscriber')->setDisabled(true);

        $eavDataImporter = $this->getContainer()->get('eavmanager_import.eav_data_importer');
        $eavDataImporter->setOutput($output);

        $fixturesPath = $this->getContainer()->get('file_locator')->locate('@CleverAgeEAVManagerImportBundle/Resources/fixtures');
        $finder = new Finder();
        /** @var SplFileInfo[] $files */
        $files = $finder->in($fixturesPath)->name('*.yml')->sortByName()->files();

        foreach ($files as $file) {
            if (!$eavDataImporter->loadDump(Yaml::parse($file->getContents(), true), $file->getFilename())) {
                $output->writeln("\n<comment>Stopping...</comment>");

                return 1;
            }
        }

        // Close import and archive context
        $eavDataImporter->terminate();

        return 0;
    }

}
