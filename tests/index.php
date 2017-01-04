<?php

require_once __DIR__ . '/../vendor/autoload.php';
use RAIsaev\UzTicketsParser\Parser;

$parser = new Parser();
$suggestionsFrom = $parser->getStationsSuggestions('Днепропетровск');
$suggestionsTo   = $parser->getStationsSuggestions('Одесса');

$from = reset($suggestionsFrom);
$to   = reset($suggestionsTo);
$date = new \DateTime('now', new \DateTimeZone('UTC'));

$trains = $parser->getTrains($from->getCode(), $to->getCode(), $date);
$train = reset($trains);

$coaches = $parser->getCoaches($from->getCode(), $to->getCode(), $train->getNumber(), 'К', $date);

var_dump(
    //$suggestionsFrom,
    //$suggestionsTo,
    //$trains,
    $coaches
);
die;
