<?php

namespace Raisaev\UzTicketsParser;

use Raisaev\UzTicketsParser\Rest\Client as RestClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Parser
{
    //###################################

    const FRONTEND_URL = 'https://booking.uz.gov.ua/';

    const RU_LOC = 'ru';
    const EN_LOC = 'en';
    const UA_LOC = 'ua';

    /** @var string */
    private $locale;

    /** @var EntityBuilder */
    private $builder;

    /** @var ContainerInterface */
    private $di;

    private $authorizationCookies = [];
    private $errorMessages = [];

    //###################################

    public function __construct(
        EntityBuilder $builder,
        ContainerInterface $di,
        $locale = self::RU_LOC
    ){
        $this->locale  = $locale;
        $this->builder = $builder;
        $this->di      = $di;

        $this->initCookiesAndToken();
    }

    // ---------------------------------------

    private function initCookiesAndToken()
    {
        $connector = $this->di->get(RestClient::class);
        $connector->sendRequest($this->getBaseUrl());
        $this->authorizationCookies = $connector->getResponseCookies();
    }

    //###################################
    /**
     * @param $locationTitle
     * @return Entity\Station[]
     */
    public function getStationsSuggestions($locationTitle)
    {
        $suggestions = [];

        try {

            $this->clearErrorMessages();

            $connector = $this->di->get(RestClient::class);
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
     * @param $stationFrom Entity\Station
     * @param $stationTo Entity\Station
     * @param $date \DateTime
     * @param $filters Filter\FilterInterface[]
     *
     * @return Entity\Train[]
     */
    public function getTrains(
        Entity\Station $stationFrom,
        Entity\Station $stationTo,
        \DateTime $date,
        $filters = []
    ){
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
     * @param $stationFrom Entity\Station
     * @param $stationTo Entity\Station
     * @param $trainNumber string
     * @param $seatCode string
     * @param $date \DateTime
     *
     * @return Entity\Train\Coach[]
     */
    public function getCoaches(
        Entity\Station $stationFrom,
        Entity\Station $stationTo,
        $trainNumber, $seatCode,
        \DateTime $date
    ){
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
     * @param $stationFrom Entity\Station
     * @param $stationTo Entity\Station
     * @param $date \DateTime
     *
     * @return Entity\Train[]
     */
    protected function searchTrains(
        Entity\Station $stationFrom,
        Entity\Station $stationTo,
        \DateTime $date
    ){
        $connector = $this->di->get(RestClient::class);
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'from' => $stationFrom->getCode(),
            'to'   => $stationTo->getCode(),
            'date' => $date->format('Y-m-d'),
            'time' => '00:00',
        ));

        $connector->sendRequest($this->getBaseUrl() . 'train_search/');
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
     * @param $stationFrom Entity\Station
     * @param $stationTo Entity\Station
     * @param string $trainNumber
     * @param string $seatCode
     * @param $date \DateTime
     *
     * @return Entity\Train\Coach[]
     */
    protected function searchCoaches(
        Entity\Station $stationFrom,
        Entity\Station $stationTo,
        $trainNumber,
        $seatCode,
        \DateTime $date
    ){
        $connector = $this->di->get(RestClient::class);
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'from'          => $stationFrom->getCode(),
            'to'            => $stationTo->getCode(),
            'train'         => $trainNumber,
            'wagon_type_id' => $seatCode,
            'date'          => $date->format('Y-m-d'),
        ));

        $connector->sendRequest($this->getBaseUrl() .'train_wagons/');
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