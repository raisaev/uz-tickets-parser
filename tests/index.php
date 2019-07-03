<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

chdir(__DIR__ . '/../');
require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');
$containerBuilder->compile();

/** @var \Raisaev\UzTicketsParser\Parser $parser */
$parser = $containerBuilder->get(\Raisaev\UzTicketsParser\Parser::class);

$suggestionsFrom = $parser->getStationsSuggestions('Днепр-Главный')[0];
$suggestionsTo   = $parser->getStationsSuggestions('Киев')[0];

$date = new \DateTime('01.07.2019', new \DateTimeZone('Europe/Kiev'));
$trains = $parser->getTrains($suggestionsFrom, $suggestionsTo, $date);

var_dump($parser->getCombinedErrorMessage());

$train = reset($trains);
if (!empty($train)) {

    var_dump(
        $train->getNumber(),
        $train->getStationFromDate('H:i'),
        $train->getStationToDate('H:i'),
        $train->getTripTime('%H:%I'),
        $train->getSeats()
    );

    $coaches = $parser->getCoaches(
        $suggestionsFrom, $suggestionsTo,
        $train->getNumber(), \Raisaev\UzTicketsParser\Entity\Train\Seat::TYPE_BERTH, $date
    );

    var_dump($parser->getCombinedErrorMessage());
    var_dump($coaches);
}

die;