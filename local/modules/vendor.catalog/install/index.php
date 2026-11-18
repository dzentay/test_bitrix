<?php

declare(strict_types=1);

/**
 * Installer stub for a real 1C-Bitrix installation.
 *
 * In standalone mode this file is not used.
 */
class vendor_catalog extends CModule
{
    public $MODULE_ID = 'vendor.catalog';
    public $MODULE_NAME = 'Vendor Catalog';
    public $MODULE_DESCRIPTION = 'Product catalog REST API module';
    public $PARTNER_NAME = 'Vendor';

    public function DoInstall(): void
    {
        $this->InstallDB();
    }

    public function InstallDB(): void
    {
        // In a real Bitrix installation, create vendor_catalog_product here
        // using $DB->Query() or Bitrix\Main\Application::getConnection().
    }

    public function UnInstallDB(): void
    {
        // Drop vendor_catalog_product in a real Bitrix installation.
    }
}