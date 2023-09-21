<?php declare(strict_types=1);

namespace Yireo\LumaSampleData\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\LumaSampleData\Importer\CsvFileReader;
use Yireo\LumaSampleData\Importer\MediaCreator;
use Yireo\LumaSampleData\Importer\ProductCreator;

#[AsCommand(name: 'luma:import', description: 'Import Magento Luma sample data')]
class ImportCommand extends Command
{
    public function __construct(
        private CsvFileReader $csvFileReader,
        private ProductCreator $productCreator,
        private MediaCreator $mediaCreator,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addOption(
            'recreate-products',
            null,
            InputOption::VALUE_OPTIONAL,
            'Recreate products if they already exist',
            false,
        );

        $this->addOption(
            'recreate-media',
            null,
            InputOption::VALUE_OPTIONAL,
            'Recreate media if they already exist',
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csvReader = $this->csvFileReader->getCsvReader('products.csv');
        $progressBar = new ProgressBar($output, $csvReader->count());
        $progressBar->start();

        $recreateProducts = (bool)$input->getOption('recreate-products');
        $recreateMedia = (bool)$input->getOption('recreate-media');

        foreach ($csvReader->getRecords() as $record) {
            $this->productCreator->create($record, $recreateProducts);
            $this->mediaCreator->create($record, $recreateMedia);
            $progressBar->advance();
        }

        $progressBar->finish();

        return Command::SUCCESS;
    }
}