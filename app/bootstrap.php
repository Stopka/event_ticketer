<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(['89.177.82.157','2001:470:6f:2aa:226a:8aff:fe90:c79b']);
$configurator->enableTracy(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
    ->addDirectory(__DIR__.'/../libs')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

//TODO přesunout do configu
Vodacek\Forms\Controls\DateInput::register();

$container = $configurator->createContainer();

return $container;
