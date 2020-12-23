<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();

$configurator = new Nette\Configurator();
$configurator->setDebugMode(false);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
    ->addDirectory(__DIR__ . '/../app')
    ->register();

$configurator->addConfig(__DIR__ . '/../app/config/main.neon');
$configurator->addConfig(__DIR__ . '/../app/config/parameters.neon');

return $configurator->createContainer();
