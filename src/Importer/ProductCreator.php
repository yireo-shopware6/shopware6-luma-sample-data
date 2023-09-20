<?php declare(strict_types=1);

namespace Yireo\LumaSampleData\Importer;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProductCreator
{
    private ?string $salesChannelId = null;

    public function __construct(
        #[Autowire(service: 'product.repository')] private EntityRepository $productRepository,
        #[Autowire(service: 'product_visibility.repository')] private EntityRepository $productVisibilityRepository,
        #[Autowire(service: 'sales_channel.repository')] private EntityRepository $salesChannelRepository,
        private ColumnMapper $columnMapper,
        private Connection $connection,
        private CategoryCreator $categoryCreator
    ) {
    }

    public function create($importData): bool
    {
        if (isset($importData['Categories'])) {
            [$breadcrumbCategories] = explode('|', $importData['Categories']);
            $breadcrumbCategories = explode('>', $breadcrumbCategories);

            $parentCategoryName = '';
            $level = 2;
            foreach ($breadcrumbCategories as $breadcrumbCategory) {
                $this->categoryCreator->create($breadcrumbCategory, $parentCategoryName, $level);
                $parentCategoryName = $breadcrumbCategory;
                $level++;
            }
        }

        $categoriesData = [];
        foreach ($breadcrumbCategories as $breadcrumbCategory) {
            $productCategory = $this->categoryCreator->getCategoryByName($breadcrumbCategory);
            $categoriesData[] = ['id' => $productCategory->getId()];
        }

        $productId = Uuid::fromStringToHex('luma.product.'.$importData['SKU']);
        $productData = [
            'id' => $productId,
            'name' => $this->getFromProductData('name', $importData),
            'description' => $this->getFromProductData('description', $importData),
            'taxId' => $this->getTaxId(),
            'stock' => rand(20, 100),
            'price' => [
                [
                    'currencyId' => $this->getCurrencyId(),
                    'gross' => $this->getFromProductData('price.gross', $importData),
                    'net' => $this->getFromProductData('price.gross', $importData),
                    'linked' => true,
                ],
            ],
            'productNumber' => $this->getFromProductData('productNumber', $importData),
            'categories' => $categoriesData,
        ];

        $this->productRepository->upsert([$productData], Context::createDefaultContext());
        $this->addVisibilityRecord($productId);

        return true;
    }

    /**
     * @param string $field
     * @param array $data
     * @return mixed
     */
    private function getFromProductData(string $field, array $data): mixed
    {
        $column = $this->columnMapper->mapFieldToColumn($field);
        if (empty($column)) {
            return '';
        }

        if (!array_key_exists($column, $data)) {
            return '';
        }

        return $data[$column];
    }

    private function getTaxId(): string
    {
        $result = $this->connection->fetchOne('SELECT LOWER(HEX(`id`)) FROM `tax` LIMIT 1');

        if (!$result) {
            throw new RuntimeException('No tax found, please make sure that basic data is available');
        }

        return (string)$result;
    }

    private function getStorefrontSalesChannelId(): string
    {
        if ($this->salesChannelId === null) {
            $this->salesChannelId = $this->searchStorefrontSalesChannelId();
        }

        return $this->salesChannelId;
    }

    private function searchStorefrontSalesChannelId(): string
    {
        $context = Context::createDefaultContext();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT));

        $context = Context::createDefaultContext();
        $result = $this->salesChannelRepository->searchIds($criteria, $context);
        return $result->firstId();
    }

    private function getCurrencyId(): string
    {
        return Defaults::CURRENCY;
    }

    private function addVisibilityRecord(string $productId): bool
    {
        $context = Context::createDefaultContext();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $result = $this->productVisibilityRepository->search($criteria, $context);
        $visibility = $result->first();
        if ($visibility) {
            return true;
        }

        $this->productVisibilityRepository->create([
            [
                'id' => Uuid::randomHex(),
                'productId' => $productId,
                'salesChannelId' => $this->getStorefrontSalesChannelId(),
                'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
            ],
        ], $context);

        return true;
    }
}
