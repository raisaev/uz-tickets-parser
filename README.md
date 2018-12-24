# uz-tickets-parser
https://booking.uz.gov.ua tickets parser

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/516ad748e7df4d709349c41011e5ac78)](https://www.codacy.com/app/raisaev/uz-tickets-parser?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=raisaev/uz-tickets-parser&amp;utm_campaign=Badge_Grade)

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