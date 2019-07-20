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
    Command\Cookie\SetCommand::getDefaultName()   => Command\Cookie\SetCommand::class,
    Command\Cookie\ResetCommand::getDefaultName() => Command\Cookie\ResetCommand::class,
    Command\Cookie\GetCommand::getDefaultName()   => Command\Cookie\GetCommand::class,

    Command\Passenger\AddCommand::getDefaultName()    => Command\Passenger\AddCommand::class,
    Command\Passenger\RemoveCommand::getDefaultName() => Command\Passenger\RemoveCommand::class,
    Command\Passenger\GetCommand::getDefaultName()    => Command\Passenger\GetCommand::class,

    Command\Parser\SuggestStation::getDefaultName() => Command\Parser\SuggestStation::class,
    Command\Parser\SearchTrains::getDefaultName()   => Command\Parser\SearchTrains::class,
    Command\Parser\SearchCoaches::getDefaultName()  => Command\Parser\SearchCoaches::class,
    Command\Parser\ReserveTicket::getDefaultName()  => Command\Parser\ReserveTicket::class,
));

$application = new Application('Booking.uz.gov.ua Parser Console');
$application->setCommandLoader($commandLoader);
$application->run();