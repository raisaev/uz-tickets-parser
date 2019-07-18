# uz-tickets-parser

### Tickets parser [https://booking.uz.gov.ua]

```
С недавних пор "Укрзалiзниця" ввела капчу на страницах поиска поездов.
Поэтому для работы парсера необходимо один раз выполнить поиск через браузер,
ввести капчу вручную и скопировать cookies из браузера.

php bin/console.php cookie:set "cookie from browser here"
```

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/516ad748e7df4d709349c41011e5ac78)](https://www.codacy.com/app/raisaev/uz-tickets-parser?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=raisaev/uz-tickets-parser&amp;utm_campaign=Badge_Grade)

- ##### parser:suggest-station
  parser:suggest-station %station-title%
  ```php
  parser:suggest-station Dnipro
  ```

- ##### parser:search-trains
  parser:search-trains [--train-filter=%train-number-filter%] %departure-from% %arrive-to% %date%
  ```php
  parser:search-trains --train-filter=738П --train-filter=244П 2210700 2200001 2019-07-25
  ```

- ##### parser:search-coaches
  parser:search-coaches [--coach-filter=%coach-number-filter%] %departure-from% %arrive-to% %date% %train-number% %coach-code%
  
  %coach-code%: [vip | coupe | berth | common | sitting]
  ```php
  parser:search-coaches --coach-filter=10 --coach-filter=16 2210700 2200001 2019-07-25 738П К
  ```
