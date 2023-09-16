<?php declare(strict_types=1);

namespace Yireo\LumaSampleData\Importer;

use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Kernel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MediaCreator
{
    public function __construct(
        #[Autowire(service: 'product.repository')] private EntityRepository $productRepository,
        #[Autowire(service: 'media.repository')] private EntityRepository $mediaRepository,
        #[Autowire(service: 'product_media.repository')] private EntityRepository $productMediaRepository,
        #[Autowire(service: 'kernel')] private Kernel $kernel,
        private ColumnMapper $columnMapper,
        private MediaService $mediaService,
        private FileSaver $fileSaver

    ) {
    }

    public function create(array $importData): bool
    {
        $context = Context::createDefaultContext();

        $importImages = explode(',', $importData['Images']);
        foreach ($importImages as $importImage) {
            $filePath = $this->getImportDirectory().basename($importImage);
            $mediaId = $this->uploadImage($filePath);
            $product = $this->getProductEntityFromImportData($importData);
            $this->linkMediaToProduct($product, $filePath, $mediaId);
        }

        $this->productRepository->update([
            [
                'id' => $product->getId(),
                'coverId' => $mediaId,
            ],
        ], $context);

        return true;
    }

    private function isAlreadyUploaded(string $filePath): string
    {
        [$fileName, $fileExtension] = $this->getFilePartsFromFilename($filePath);

        $criteria = new Criteria;
        $criteria->addFilter(new EqualsFilter('fileName', $fileName));
        $criteria->addFilter(new EqualsFilter('fileExtension', $fileExtension));

        $context = Context::createDefaultContext();
        $result = $this->mediaRepository->search($criteria, $context);

        $first = $result->first();
        if ($first) {
            return $first->getId();
        }

        return '';
    }

    private function uploadImage(string $filePath): string
    {
        $context = Context::createDefaultContext();

        if (false === file_exists($filePath)) {
            throw new \Exception('Image "'.$filePath.'" does not exist');
        }

        if ($mediaId = $this->isAlreadyUploaded($filePath)) {
            return $mediaId;
        }

        [$fileName, $fileExtension] = $this->getFilePartsFromFilename($filePath);
        $mimeType = mime_content_type($filePath);
        $fileSize = filesize($filePath);

        $mediaFile = new MediaFile($filePath, $mimeType, $fileExtension, $fileSize);
        $mediaId = $this->mediaService->createMediaInFolder('/public/media', $context, false);
        $this->fileSaver->persistFileToMedia(
            $mediaFile,
            $fileName,
            $mediaId,
            $context
        );

        return $mediaId;
    }

    private function linkMediaToProduct(ProductEntity $product, string $filePath, string $mediaId)
    {
        $context = Context::createDefaultContext();
        $this->productMediaRepository->upsert([
            [
                'id' => Uuid::fromStringToHex('luma.media_product.'.$filePath),
                'productId' => $product->getId(),
                'mediaId' => $mediaId,
            ],
        ], $context);
    }

    private function getImportDirectory()
    {
        $bundleResource = $this->kernel->locateResource('@YireoLumaSampleData');

        return $bundleResource.'/Resources/media/';
    }

    private function getMimetypeFromFilename(string $filename): string
    {
        if (preg_match('/\.png$/', $filename)) {
            return 'image/png';
        }

        return 'image/jpeg';
    }

    /**
     * @param string $filename
     * @return string[]
     * @throws \Exception
     */
    private function getFilePartsFromFilename(string $filename): array
    {
        if (preg_match('/^(.*)\.(png|jpg|jpeg)$/', basename($filename), $match)) {
            return [$match[1], $match[2]];
        }

        throw new \Exception('Unable to split filename "'.$filename.'"');
    }

    /**
     * @param array $importData
     * @return ProductEntity
     */
    private function getProductEntityFromImportData(array $importData): ProductEntity
    {
        $column = $this->columnMapper->mapFieldToColumn('productNumber');
        $productNumber = $importData[$column];
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $result = $this->productRepository->search($criteria, Context::createDefaultContext());

        return $result->first();
    }
}