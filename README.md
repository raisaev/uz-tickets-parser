# uz-tickets-parser
https://booking.uz.gov.ua tickets parser

```php
require_once __DIR__ . '/../vendor/autoload.php';

$parser = new \Raisaev\UzTicketsParser\Parser();

$suggestionsFrom = $parser->getStationsSuggestions('Днепр-Главный');
$suggestionsTo   = $parser->getStationsSuggestions('Белая Церковь');

$date = new \DateTime('now', new \DateTimeZone('Europe/Kiev'));

$trains = $parser->getTrains($suggestionsFrom[0], $suggestionsTo[0], $date);
$coaches = $parser->getCoaches(
    $suggestionsFrom[0], $suggestionsTo[0],
    $trains[0]->getNumber(), \Raisaev\UzTicketsParser\Train\Seat::TYPE_BERTH, $date
);