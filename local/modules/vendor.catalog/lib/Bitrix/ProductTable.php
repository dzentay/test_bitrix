<?php

declare(strict_types=1);

namespace Vendor\Catalog\Bitrix;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

/**
 * D7 ORM table definition for a real 1C-Bitrix installation.
 *
 * In standalone mode this class is not loaded.
 */
class ProductTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'vendor_catalog_product';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new StringField('NAME', [
                'required' => true,
                'size' => 255,
            ]),
            new StringField('CODE', [
                'required' => true,
                'size' => 255,
            ]),
            new FloatField('PRICE', [
                'required' => true,
                'default_value' => 0,
            ]),
            new StringField('BRAND', [
                'size' => 255,
                'nullable' => true,
            ]),
            new BooleanField('IN_STOCK', [
                'values' => [0, 1],
                'default_value' => 0,
            ]),
            new DatetimeField('CREATED_AT', [
                'required' => true,
            ]),
            new DatetimeField('UPDATED_AT', [
                'nullable' => true,
            ]),
        ];
    }
}