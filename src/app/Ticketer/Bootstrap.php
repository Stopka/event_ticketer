<?php

declare(strict_types=1);

namespace Ticketer;

use Tester\Environment;
use Ticketer\Configurators\TicketerConfigurator;

class Bootstrap
{
    public static function boot(): TicketerConfigurator
    {
        $configurator = new TicketerConfigurator();
        $confDir = __DIR__ . '/../config';

        $configurator->setDebugModeConfigIfExists($confDir . '/debug.php');

        $configurator->enableTracy(__DIR__ . '/../../var/log');
        $configurator->setTempDirectory(__DIR__ . '/../../var/tmp');

        $configurator->addConfig("$confDir/main.neon");
        $configurator->addConfigIfExists("$confDir/parameters.environmental.php");
        $configurator->addConfigIfExists("$confDir/parameters.local.neon");

        return $configurator;
    }


    public static function bootForTests(): TicketerConfigurator
    {
        $configurator = self::boot();
        Environment::setup();

        return $configurator;
    }
}
