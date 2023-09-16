<?php declare(strict_types=1);

namespace Yireo\LumaSampleData\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\LumaSampleData\Importer\CsvFileReader;
use Yireo\LumaSampleData\Importer\MediaCreator;
use Yireo\LumaSampleData\Importer\ProductCreator;

#[AsCommand(name:'luma:import', description: 'Import Magento Luma sample data')]
class ImportCommand extends Command
{
    public function __construct(
        private CsvFileReader $csvFileReader,
        private ProductCreator $productCreator,
        private MediaCreator $mediaCreator,
        string $name = null) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csvReader = $this->csvFileReader->getCsvReader('products.csv');
        foreach ($csvReader->getRecords() as $record) {
            $this->productCreator->create($record);
            $this->mediaCreator->create($record);
        }

        return Command::SUCCESS;
    }
}