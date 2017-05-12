<?php

namespace CleverAge\EAVManager\ImportBundle\Command;

use CleverAge\EAVManager\ImportBundle\Entity\ImportHistory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generic CLI to start an import
 */
class ImportDataCommand extends ContainerAwareCommand
{

    /** @var ProgressBar */
    protected $progress;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('eavmanager:import')
            ->setDescription('Import the webactu data from the old database')
            ->addArgument('import_code', InputArgument::IS_ARRAY + InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws \UnexpectedValueException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importConfigurations = $this->getContainer()->get('eavmanager.import_configuration.handler');

        foreach ($this->getImportCodes($input) as $importCode) {
            $importConfig = $importConfigurations->getImport($importCode);

            $this->progress = new ProgressBar($output, count($importConfig->getSource()->getData()));
            $this->progress->setFormat(
                ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
            );

            $output->writeln("<info>Importing family {$importConfig->getFamily()->getCode()}</info>");
            $eavImporter = $importConfig->getService();
            $eavImporter->setOutput($output);

            $history = $eavImporter->import($importConfig, [$this, 'onProgress']);
            if ($history->getStatus() === ImportHistory::STATUS_SUCCESS) {
                $this->progress->finish();
                $output->writeln('');
                $output->writeln('<info>This import have been processed with success</info>');
                $output->writeln($history->getMessage());
            } else {
                $output->writeln('');
                $output->writeln('<error>Something went wrong with this import</error>');
                $output->writeln($history->getMessage());
            }
        }

        $output->writeln('<info>Import is over</info>');
    }

    /**
     * Advance and save the progress
     *
     * @TODO save data in a context file
     *
     * @param array $proceededData
     * @param array $resultingData
     */
    // @codingStandardsIgnoreLine
    public function onProgress(array $proceededData, array $resultingData)
    {
        $this->progress->advance(count($proceededData));
    }

    /**
     * @return array
     */
    protected function getImportCodes(InputInterface $input): array
    {
        return $input->getArgument('import_code');
    }


}
