<?php

namespace Raisaev\UzTicketsParser;

use Raisaev\UzTicketsParser\Rest\Client as RestClient;

class Parser
{
    //###################################

    const FRONTEND_URL = 'https://booking.uz.gov.ua/';

    const RU_LOC = 'ru';
    const EN_LOC = 'en';
    const UA_LOC = 'ua';

    private $locale;

    /** @var EntityBuilder */
    private $builder;

    private $authorizationCookies = [];
    private $errorMessages = [];

    //###################################

    public function __construct($locale = self::RU_LOC)
    {
        $this->locale  = $locale;
        $this->builder = new EntityBuilder();

        $this->initCookiesAndToken();
    }

    // ---------------------------------------

    private function initCookiesAndToken()
    {
        $connector = new RestClient;
        $connector->sendRequest($this->getBaseUrl());
        $this->authorizationCookies = $connector->getResponseCookies();
    }

    //###################################
    /**
     * @param $locationTitle
     * @return Station[]
     */
    public function getStationsSuggestions($locationTitle)
    {
        $suggestions = [];

        try {

            $this->clearErrorMessages();

            $connector = new RestClient;
            $connector->setCookies($this->authorizationCookies);
            $connector->setGet(array(
                'term' => $locationTitle
            ));
            $connector->sendRequest($this->getBaseUrl() . 'train_search/station/');

            $response = (array)json_decode($connector->getResponseBody(), true);
            foreach ($response as $stationData) {
                $suggestions[] = $this->builder->constructStation($stationData);
            }

        } catch (\Exception $e) {
            $this->errorMessages[] = $e->getMessage();
        }

        return $suggestions;
    }

    /**
     * @param $stationFrom Station
     * @param $stationTo Station
     * @param $date \DateTime
     * @param $filters Filter\FilterInterface[]
     *
     * @return Train[]
     */
    public function getTrains(Station $stationFrom, Station $stationTo, \DateTime $date, $filters = [])
    {
        $trains = [];

        try {

            $this->clearErrorMessages();

            $trains = $this->searchTrains($stationFrom, $stationTo, $date);
            foreach ($filters as $filter) {
                $filter->apply($trains);
            }

        } catch (\Exception $e) {
            $this->errorMessages[] = $e->getMessage();
        }

        return $trains;
    }

    /**
     * @param $stationFrom Station
     * @param $stationTo Station
     * @param $trainNumber string
     * @param $seatCode string
     * @param $date \DateTime
     *
     * @return Train\Coach[]
     */
    public function getCoaches(Station $stationFrom, Station $stationTo, $trainNumber, $seatCode, \DateTime $date)
    {
        $coaches = [];

        try {

            $this->clearErrorMessages();

            $coaches = $this->searchCoaches($stationFrom, $stationTo, $trainNumber, $seatCode, $date);

        } catch (\Exception $e) {
            $this->errorMessages[] = $e->getMessage();
        }

        return $coaches;
    }

    //###################################

    /**
     * @param $stationFrom Station
     * @param $stationTo Station
     * @param $date \DateTime
     *
     * @return Train[]
     */
    protected function searchTrains(Station $stationFrom, Station $stationTo, \DateTime $date)
    {
        $connector = new RestClient;
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'from' => $stationFrom->getCode(),
            'to'   => $stationTo->getCode(),
            'date' => $date->format('Y-m-d'),
            'time' => '00:00',
        ));

        $connector->sendRequest($this->getBaseUrl() . '/train_search/');
        $response = (array)json_decode($connector->getResponseBody(), true);

        $trains = [];
        if (!empty($response['data']['list'])) {
            foreach ($response['data']['list'] as $trainData) {
                $trains[] = $this->builder->constructTrain($trainData);
            }
        }

        return $trains;
    }

    /**
     * @param $stationFrom Station
     * @param $stationTo Station
     * @param string $trainNumber
     * @param string $seatCode
     * @param $date \DateTime
     *
     * @return Train\Coach[]
     */
    protected function searchCoaches(Station $stationFrom, Station $stationTo, $trainNumber, $seatCode, \DateTime $date)
    {
        $connector = new RestClient;
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'from'          => $stationFrom->getCode(),
            'to'            => $stationTo->getCode(),
            'train'         => $trainNumber,
            'wagon_type_id' => $seatCode,
            'date'          => $date->format('Y-m-d'),
        ));

        $connector->sendRequest($this->getBaseUrl() .'train_wagons');
        $response = (array)json_decode($connector->getResponseBody(), true);

        $coaches = [];

        if (!empty($response['data']['wagons'])) {
            foreach ($response['data']['wagons'] as $coachData) {
                $coachData['train_number'] = $trainNumber;
                $coaches[] = $this->builder->constructCoach($coachData);
            }
        }

        return $coaches;
    }

    //###################################

    private function getBaseUrl()
    {
        return self::FRONTEND_URL . $this->locale . '/';
    }

    //###################################

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function clearErrorMessages()
    {
        $this->errorMessages = array();
    }

    public function getCombinedErrorMessage()
    {
        return implode('. ', $this->errorMessages);
    }

    //###################################
}