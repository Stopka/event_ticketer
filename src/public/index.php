<?php

$container = require __DIR__ . '/../app/bootstrap.php';
/** @var \Nette\Application\Application $app */
$app = $container->getByType(Nette\Application\Application::class);
$app->run();
