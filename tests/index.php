<?php

require_once __DIR__ . '/../vendor/autoload.php';

$parser = new \Raisaev\UzTicketsParser\Parser();
$suggestionsFrom = $parser->getStationsSuggestions('Днепр-Главный');
$suggestionsTo   = $parser->getStationsSuggestions('Белая Церковь');

$date = new \DateTime('16.01.2019', new \DateTimeZone('Europe/Kiev'));
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
    $trains[0]->getNumber(), \Raisaev\UzTicketsParser\Train\Seat::TYPE_BERTH, $date
);

var_dump(
    $coaches
);
die;
