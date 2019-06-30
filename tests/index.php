<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');
$containerBuilder->compile();

/** @var \Raisaev\UzTicketsParser\Parser $parser */
$parser = $containerBuilder->get(\Raisaev\UzTicketsParser\Parser::class);

$suggestionsFrom = $parser->getStationsSuggestions('Днепр-Главный');
$suggestionsTo   = $parser->getStationsSuggestions('Киев');

$date = new \DateTime('30.06.2019', new \DateTimeZone('Europe/Kiev'));
$trains = $parser->getTrains(
    $suggestionsFrom[0], $suggestionsTo[0], $date
);

var_dump(
    $trains[0]->getNumber(),
    $trains[0]->getStationFromDate('H:i'),
    $trains[0]->getStationToDate('H:i'),
    $trains[0]->getTripTime('%H:%I'),
    $trains[0]->getSeats()
);

$coaches = $parser->getCoaches(
    $suggestionsFrom[0], $suggestionsTo[0],
    $trains[0]->getNumber(), \Raisaev\UzTicketsParser\Entity\Train\Seat::TYPE_BERTH, $date
);

var_dump(
    $coaches
);
die;