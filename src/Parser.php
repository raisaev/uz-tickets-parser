<?php

namespace Raisaev\UzTicketsParser;

use Raisaev\UzTicketsParser\Rest\Client as RestClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Parser
{
    //###################################

    const FRONTEND_URL = 'https://booking.uz.gov.ua/';

    const REQUEST_COOKIES_STORAGE_KEY = 'request-cookies';

    const RU_LOC = 'ru';
    const EN_LOC = 'en';
    const UA_LOC = 'ua';

    /** @var string */
    private $locale;

    /** @var EntityBuilder */
    private $builder;

    /** @var ContainerInterface */
    private $di;

    /** @var \Symfony\Component\Cache\Adapter\FilesystemAdapter */
    private $cache;

    private $authorizationCookies = [];
    private $errorMessages = [];

    //###################################

    public function __construct(
        EntityBuilder $builder,
        ContainerInterface $di,
        \Symfony\Component\Cache\Adapter\FilesystemAdapter $cache,
        $locale = self::RU_LOC
    ){
        $this->locale  = $locale;
        $this->builder = $builder;
        $this->di      = $di;
        $this->cache   = $cache;
    }

    // ---------------------------------------

    private function initCookiesAndToken()
    {
        $cookies = $this->cache->getItem(self::REQUEST_COOKIES_STORAGE_KEY);
        if (!$cookies->isHit()) {

            $connector = $this->di->get(RestClient::class);
            $connector->sendRequest($this->getBaseUrl() . 'train_search/');

            $cookies->set($connector->getResponseCookies());
            $cookies->expiresAfter(60 * 60 * 24);

            $this->cache->save($cookies);
        }

        $this->authorizationCookies = $cookies->get();
    }

    private function resetCookiesAndToken()
    {
        $this->cache->deleteItem(self::REQUEST_COOKIES_STORAGE_KEY);
    }

    //###################################

    /**
     * @param $locationTitle
     * @return Entity\Station[]
     */
    public function getStationsSuggestions($locationTitle)
    {
        try {

            $this->clearErrorMessages();

            $suggestions = $this->cache->getItem("stations-suggestions-{$locationTitle}");
            if (!$suggestions->isHit()) {

                $suggestions->set($this->searchStationsSuggestions($locationTitle));
                $suggestions->expiresAfter(60 * 60);

                $this->cache->save($suggestions);
            }

            return $suggestions->get();

        } catch (\Exception $e) {

            $this->errorMessages[] = $e->getMessage();
            return [];
        }
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
        try {

            $this->initCookiesAndToken();
            $this->clearErrorMessages();

            $trains = $this->searchTrains($stationFrom, $stationTo, $date);
            foreach ($filters as $filter) {
                $filter->apply($trains);
            }

            return $trains;

        } catch (\Exception $e) {

            $this->resetCookiesAndToken();
            $this->errorMessages[] = $e->getMessage();

            return [];
        }
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
        try {

            $this->initCookiesAndToken();
            $this->clearErrorMessages();

            $coaches = $this->searchCoaches($stationFrom, $stationTo, $trainNumber, $seatCode, $date);
            return $coaches;

        } catch (\Exception $e) {

            $this->resetCookiesAndToken();
            $this->errorMessages[] = $e->getMessage();

            return [];
        }
    }

    //###################################

    /**
     * @param $locationTitle
     * @return Entity\Station[]
     */
    public function searchStationsSuggestions($locationTitle)
    {
        $connector = $this->di->get(RestClient::class);
        $connector->setCookies($this->authorizationCookies);
        $connector->setGet(array(
            'term' => $locationTitle
        ));

        $connector->sendRequest($this->getBaseUrl() . 'train_search/station/');
        $response = (array)json_decode($connector->getResponseBody(), true);

        $suggestions = [];

        foreach ($response as $stationData) {
            $suggestions[] = $this->builder->constructStation($stationData);
        }

        return $suggestions;
    }

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

        if (!empty($response['captcha'])) {
            throw new Exception\ParsingException('Unable to parse data. Captcha required.');
        }

        if (!empty($response['error']) && !empty($response['data'])) {
            throw new Exception\ParsingException("Unable to parse data. {$response['data']}");
        }

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

        if (!empty($response['captcha'])) {
            throw new Exception\ParsingException('Unable to parse data. Captcha required.');
        }

        if (!empty($response['error']) && !empty($response['data'])) {
            throw new Exception\ParsingException("Unable to parse data. {$response['data']}");
        }

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