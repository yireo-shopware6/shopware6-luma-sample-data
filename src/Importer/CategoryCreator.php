<?php declare(strict_types=1);

namespace Yireo\LumaSampleData\Importer;

use Exception;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CategoryCreator
{
    public function __construct(
        #[Autowire(service: 'category.repository')] private EntityRepository $categoryRepository
    ) {
    }

    /**
     * @param string $categoryName
     * @param string $parentCategoryName
     * @param int $level
     * @return bool
     */
    public function create(string $categoryName, string $parentCategoryName = '', int $level = 1): bool
    {
        if ($this->categoryExists($categoryName)) {
            return true;
        }

        $categoryId = Uuid::fromStringToHex('luma.category.'.$categoryName);

        try {
            $parentCategory = $this->getCategoryByName($parentCategoryName);
        } catch (Exception $e) {
            $parentCategory = null;
        }

        if (empty($parentCategory)) {
            $parentCategory = $this->getRootCategory();
        }

        $categoryData = [
            'id' => $categoryId,
            'parentId' => $parentCategory->getId(),
            'name' => $categoryName,
            'productAssignmentType' => 'product',
            'level' => $level,
            'active' => true,
            'visible' => true,
        ];

        $this->categoryRepository->upsert([$categoryData], Context::createDefaultContext());

        return true;
    }

    /**
     * @param string $categoryName
     * @return CategoryEntity
     * @throws Exception
     */
    public function getCategoryByName(string $categoryName): CategoryEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $categoryName));
        $result = $this->categoryRepository->search($criteria, Context::createDefaultContext());
        $category = $result->getEntities()->first();
        if ($category instanceof CategoryEntity) {
            return $category;
        }

        throw new Exception('Category with name "'.$categoryName.'" not found');
    }

    public function getRootCategory(): CategoryEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('level', 1));
        $result = $this->categoryRepository->search($criteria, Context::createDefaultContext());

        return $result->getEntities()->first();
    }

    /**
     * @param string $categoryName
     * @return bool
     */
    private function categoryExists(string $categoryName): bool
    {
        try {
            return (bool)$this->getCategoryByName($categoryName);
        } catch (Exception $e) {
            return false;
        }
    }
}