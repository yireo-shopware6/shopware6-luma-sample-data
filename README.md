# YireoLumaSampleData

**This Shopware 6 plugin allows you to import Luma sample products into your Shopware instance. The Luma sample data are based upon the demo-data used in Magento 2 Luma. Note that this plugin mainly serves an educational purpose.**

**STATUS: In progress. Categories and products are imported, including product media images. The main thing that is still lacking are properties and product variants.**

WARNING: This package contains about 500Mb worth of images. Just so you know.

## Installation
```bash
composer require yireo/shopware6-luma-sample-data:@dev --dev
bin/console plugin:refresh
bin/console plugin:install --activate YireoLumaSampleData
```

## Usage
Run the following command to start importing data:
```bash
php -d memory_limit=-1 bin/console luma:import
```

Additional flags:
- `--recreate-products=1`: Recreate products if they already exist in the database.
- `--recreate-media=1`: Recreate media images if they already exist in the database.

## TODO
- Add variants
- Check CSV feed for other options
- Turn this into async actions instead
