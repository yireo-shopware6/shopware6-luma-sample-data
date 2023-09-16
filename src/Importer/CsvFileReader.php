<?php declare(strict_types=1);

namespace Yireo\LumaSampleData\Importer;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Shopware\Core\Kernel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use League\Csv\Reader;

class CsvFileReader
{
    public function __construct(
        #[Autowire(service: 'kernel')] private Kernel $kernel
    ) {
    }

    public function getCsvReader(string $file): Reader
    {
        $bundleResource = $this->kernel->locateResource('@YireoLumaSampleData');
        $csv = Reader::createFromPath($bundleResource.'Resources/data/'.$file, 'r');
        $csv->setHeaderOffset(0);

        return $csv;
    }
}
