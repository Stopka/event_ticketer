<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new App\Configurator\Configurator();

$configurator->setDebugMode(true);
$configurator->enableTracy(__DIR__ . '/../var/log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../var/temp');

$confDir = __DIR__ . '/config';
$configurator->addConfig("$confDir/config.neon");
$configurator->addConfigIfExists("$confDir/config.local.neon");
$configurator->addConfigIfExists("$confDir/environment.php");
$configurator->addConfigIfExists("$confDir/environmentOverride.php");

$container = $configurator->createContainer();

return $container;
