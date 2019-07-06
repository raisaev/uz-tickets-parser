<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Raisaev\UzTicketsParser\Command;

chdir(__DIR__ . '/../');
require __DIR__. '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');
$containerBuilder->compile();

$commandLoader = new ContainerCommandLoader($containerBuilder, array(
    Command\CookieSetCommand::getDefaultName()   => Command\CookieSetCommand::class,
    Command\CookieResetCommand::getDefaultName() => Command\CookieResetCommand::class,
    Command\CookieGetCommand::getDefaultName()   => Command\CookieGetCommand::class,

    Command\ParserSuggestStation::getDefaultName() => Command\ParserSuggestStation::class,
    Command\ParserSearchTrains::getDefaultName()   => Command\ParserSearchTrains::class,
    Command\ParserSearchCoaches::getDefaultName()  => Command\ParserSearchCoaches::class,
));

$application = new Application('Booking.uz.gov.ua Parser Console');
$application->setCommandLoader($commandLoader);
$application->run();