<?php declare(strict_types=1);

namespace Yireo\LumaSampleData\Importer;

class ColumnMapper
{
    /**
     * @param string $column
     * @return string
     */
    public function mapColumnToField(string $column): string
    {
        $mapping = $this->getMapping();
        if (array_key_exists($column, $mapping) === false) {
            return '';
        }

        return (string)$mapping[$column];
    }

    /**
     * @param string $field
     * @return string
     */
    public function mapFieldToColumn(string $field): string
    {
        $mapping = $this->getMapping();
        if (in_array($field, $mapping) === false) {
            return '';
        }

        return (string) array_search($field, $mapping);
    }

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return [
            'ID' => 'id',
            'Type' => '',
            'SKU' => 'productNumber',
            'Name' => 'name',
            'Published' => '',
            'Is featured?' => '',
            'Visibility in catalog' => '',
            'Short description' => '',
            'description' => 'description',
            'Date sale price starts' => '',
            'Date sale price ends' => '',
            'Tax status' => '',
            'Tax class' => '',
            'In stock?' => 'in_stock',
            'Stock' => 'stock',
            'Backorders allowed?' => '',
            'Sold individually?' => '',
            'Weight (lbs)' => 'weight',
            'Length (in)' => '',
            'Width (in)' => '',
            'Height (in)' => '',
            'Allow customer reviews?' => '',
            'Purchase note' => '',
            'Sale price' => 'price.sale',
            'Regular price' => 'price.gross',
            'Categories' => '',
            'Tags' => '',
            'Shipping class' => '',
            'Images' => '',
            'Download limit' => '',
            'Download expiry days' => '',
            'Parent' => '',
            'Grouped products' => '',
            'Upsells' => '',
            'Cross-sells' => '',
            'External URL' => '',
            'Button text' => '',
            'Position' => '',
            'Attribute 1 name' => '',
            'Attribute 1 value(s)' => '',
            'Attribute 2 name' => '',
            'Attribute 2 value(s)' => '',
            'Attribute 3 name' => '',
            'Attribute 3 value(s)' => '',
            'Attribute 4 name' => '',
            'Attribute 4 value(s)' => '',
            'Attribute 5 name' => '',
            'Attribute 5 value(s)' => '',
            'Meta: _wpcom_is_markdown' => '',
            'Download 1 name' => '',
            'Download 1 URL' => '',
            'Download 2 name' => '',
            'Download 2 URL' => '',
        ];
    }
}