<?php

declare(strict_types=1);

namespace Vendor\Catalog\Application;

final class Bootstrap
{
    private static ?Container $container = null;

    public static function init(): void
    {
        Env::load();

        date_default_timezone_set(Env::get('APP_TIMEZONE', 'Europe/Moscow') ?? 'Europe/Moscow');

        error_reporting(E_ALL);

        if (Env::bool('APP_DEBUG', false)) {
            ini_set('display_errors', '1');
        } else {
            ini_set('display_errors', '0');
        }
    }

    public static function container(): Container
    {
        if (self::$container === null) {
            self::$container = new Container();
        }

        return self::$container;
    }
}