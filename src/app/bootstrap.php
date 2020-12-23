<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Ticketer\Configurators\TicketerConfigurator();

$configurator->setDebugMode(true);
$configurator->enableTracy(__DIR__ . '/../var/log');
$configurator->setTempDirectory(__DIR__ . '/../var/temp');

$confDir = __DIR__ . '/config';
$configurator->addConfig("$confDir/main.neon");
$configurator->addConfigIfExists("$confDir/config.environmental.neon");
$configurator->addConfigIfExists("$confDir/config.local.neon");

return $configurator->createContainer();
