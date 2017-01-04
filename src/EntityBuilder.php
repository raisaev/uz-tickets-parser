<?php

namespace RAIsaev\UzTicketsParser;

use RAIsaev\UzTicketsParser\Station;
use RAIsaev\UzTicketsParser\Train;

class EntityBuilder
{
    //###################################

    /**
    Array
    (
        [title] => Днепропетровск Главный
        [station_id] => 2210700
    )
     * @param array $parsedData
     * @return Station
     */
    public function constructStation(array $parsedData)
    {
        $station = new Station(
            $parsedData['title'],
            $parsedData['station_id']
        );
        return $station;
    }

    /**
    Array
    (
        [num] => 063П
        [model] => 0
        [category] => 0
        [travel_time] => 11:18
        [from] => Array
        (
            [station_id] => 2210700
            [station] => Днепропетровск Главный
            [date] => 1483549800
            [src_date] => 2017-01-04 19:10:00
        )

        [till] => Array
        (
            [station_id] => 2208001
            [station] => Одесса-Главная
            [date] => 1483590480
            [src_date] => 2017-01-05 06:28:00
        )

        [types] => Array
        (
            [0] => Array
            (
                [title] => Купе
                [letter] => К
                [places] => 14
            )
            [1] => Array
            (
                [title] => Плацкарт
                [letter] => П
                [places] => 210
            )
        )
    )
     * @param array $parsedData
     * @return Train
     */
    public function constructTrain(array $parsedData)
    {
        $parsedData['from']['title'] = $parsedData['from']['station'];
        $stationFrom = $this->constructStation($parsedData['from']);

        $parsedData['till']['title'] = $parsedData['till']['station'];
        $stationTo = $this->constructStation($parsedData['till']);

        $seats = [];
        foreach ($parsedData['types'] as $seatData) {
            $seats[] = $this->constructSeat($seatData);
        }

        $train = new Train(
            $parsedData['num'],
            $stationFrom,
            $stationTo,
            new \DateTime($parsedData['from']['src_date']),
            new \DateTime($parsedData['till']['src_date']),
            $seats
        );
        return $train;
    }

    /**
    Array
    (
        [title] => Купе
        [letter] => К
        [places] => 14
    )
     * @param array $parsedData
     * @return Train\Seat
     */
    public function constructSeat(array $parsedData)
    {
        $seat = new Train\Seat(
            $parsedData['letter'],
            $parsedData['title'],
            $parsedData['places']
        );
        return $seat;
    }

    /**
    Array
    (
        [num] => 5
        [type] => К
        [allow_bonus] =>
        [places_cnt] => 1
        [has_bedding] => 1
        [reserve_price] => 1700
        [services] => Array
        (
            [0] => Ч
            [1] => Ш
        )
        [prices] => Array
        (
            [Б] => 16494
        )
        [coach_type_id] => 3
        [scheme_id] => 3
        [coach_class] => Д
    )
     * @param string $trainNumber
     * @param array $parsedData
     * @return Train\Coach
     */
    public function constructCoach($trainNumber, array $parsedData)
    {
        $price = array_shift($parsedData['prices']);
        $price = round($price / 100, 2);

        $coach = new Train\Coach(
            $trainNumber,
            $parsedData['num'],
            $parsedData['coach_type_id'],
            $parsedData['coach_class'],
            $price,
            $parsedData['places_cnt'],
            $parsedData['has_bedding']
        );
        return $coach;
    }

    //###################################
}