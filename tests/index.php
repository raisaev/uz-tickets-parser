<?php

require_once __DIR__ . '/../vendor/autoload.php';
use RAIsaev\UzTicketsParser\Parser;

$parser = new Parser();
//$suggestionsFrom = $parser->getStationsSuggestions('Днепропетровск'); // 2210700
//$suggestionsTo   = $parser->getStationsSuggestions('Львов');          // 2218000
//$suggestionsTo   = $parser->getStationsSuggestions('Тернополь');      // 2218300

//$from = reset($suggestionsFrom);
//$to   = reset($suggestionsTo);
$date = new \DateTime('11.03.2017 00:00', new \DateTimeZone('UTC'));

$trains = $parser->getTrains(
    '2210700', //$from->getCode(),
    '2218300', //$to->getCode(),
    $date
);

if (!empty($parser->getErrorMessages())) {
    echo implode('\r\n', $parser->getErrorMessages());
}

if (empty($trains)) {
    return;
}

$train = reset($trains);
var_dump(
    $train
);

//$coaches = $parser->getCoaches(
//    $train->getStationFrom()->getCode(), $train->getStationTo()->getCode(),
//    $train->getNumber(), \RAIsaev\UzTicketsParser\Train\Seat::TYPE_COUPE, $date
//);

die;
