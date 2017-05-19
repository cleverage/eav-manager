<?php

namespace CleverAge\EAVManager\ImportBundle\Command;

use CleverAge\EAVManager\ImportBundle\Entity\ImportHistory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generic CLI to start an import.
 */
class ImportDataCommand extends ContainerAwareCommand
{
    /** @var ProgressBar */
    protected $progress;

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('eavmanager:import')
            ->setDescription('Import data from various sources')
            ->addArgument('import_code', InputArgument::IS_ARRAY + InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|null|void
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
     * Advance and save the progress.
     *
     * @TODO save data in a context file
     *
     * @param array $proceededData
     * @param array $resultingData
     */
    public function onProgress(array $proceededData, array $resultingData)
    {
        $this->progress->advance(count($proceededData));
    }

    /**
     * @param InputInterface $input
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return array
     */
    protected function getImportCodes(InputInterface $input): array
    {
        return $input->getArgument('import_code');
    }
}
