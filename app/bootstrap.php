<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;
use Nette\Diagnostics\Debugger;

if($configurator->detectDebugMode(array("94.112.79.165", '78.108.107.255')))
    $configurator->setDebugMode(TRUE);
    
$configurator->enableDebugger(__DIR__ . '/../log', "bh.majkl@gmail.com");
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../vendor/others')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');

$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();
return $container;
