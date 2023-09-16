# YireoLumaSampleData

**This Shopware 6 plugin allows you to import Luma sample products into your Shopware instance. The Luma sample data are based upon the demo-data used in Magento 2 Luma. Note that this plugin mainly serves an educational purpose.**

WARNING: This package contains about 500Mb worth of images. Just so you know.

## Installation
```bash
bin/console plugin:refresh
bin/console plugin:install --activate YireoLumaSampleData
bin/console luma:import
```

## TODO
- Add variants
- Check CSV feed for other options