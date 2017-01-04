<?php

namespace RAIsaev\UzTicketsParser;

use RAIsaev\UzTicketsParser\Rest\Client as RestClient;
use JJDecode\JJDecode;

class Parser
{
    //###################################

    const FRONTEND_URL = 'http://booking.uz.gov.ua/';

    const LOCALE_RU = 'ru';
    const LOCALE_EN = 'en';
    const LOCALE_UA = 'ua';

    protected $params = [];
    protected $errorMessages = [];

    private $authorizationCookies = [];
    private $authorizationToken   = null;

    /** @var EntityBuilder */
    private $builder;

    //###################################

    public function __construct()
    {
        $this->builder = new EntityBuilder();
        $this->initCookiesAndToken();
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
            $connector->sendRequest($this->getBaseUrl() . "purchase/station/{$locationTitle}");
            $response = (array)json_decode($connector->getResponseBody(), true);

            foreach ($response['value'] as $stationData) {
                $suggestions[] = $this->builder->constructStation($stationData);
            }

        } catch (\Exception $e) {
            $this->errorMessages[] = $e->getMessage();
        }

        return $suggestions;
    }

    /**
     * @param $stationFromCode string
     * @param $stationToCode string
     * @param $date \DateTime
     * @param $filters Filter\AbstractModel[]
     * @return Train[]
     */
    public function getTrains($stationFromCode, $stationToCode, \DateTime $date, $filters = [])
    {
        $trains = [];

        try {

            $this->clearErrorMessages();

            $trains = $this->searchTrains($stationFromCode, $stationToCode, $date);
            foreach ($filters as $filter) {
                $filter->filter($trains);
            }

        } catch (\Exception $e) {
            $this->errorMessages[] = $e->getMessage();
        }

        return $trains;
    }

    /**
     * @param $stationFromCode string
     * @param $stationToCode string
     * @param $trainNumber string
     * @param $seatCode string
     * @param $date \DateTime
     * @return Train\Coach[]
     */
    public function getCoaches($stationFromCode, $stationToCode, $trainNumber, $seatCode, \DateTime $date)
    {
        $coaches = [];

        try {

            $this->clearErrorMessages();

            $coaches = $this->searchCoaches($stationFromCode, $stationToCode, $trainNumber, $seatCode, $date);

        } catch (\Exception $e) {
            $this->errorMessages[] = $e->getMessage();
        }

        return $coaches;
    }

    //###################################

    private function initCookiesAndToken()
    {
        if (!empty($this->authorizationCookies) && $this->authorizationToken) {
            return;
        }

        $connector = new RestClient;
        $connector->sendRequest($this->getBaseUrl());
        $this->authorizationCookies = $connector->getResponseCookies();

        $decoder = new JJDecode();
        $decodedResponse = $decoder->Decode($connector->getResponseBody());

        preg_match('/"gv-token"(.)*?"((.)*?)"/', $decodedResponse, $matches);
        if (empty($matches[2])) {
            throw new \Exception('Unable to parse GV-Token');
        }

        $this->authorizationToken = $matches[2];
    }

    //###################################
    /**
     * @param $stationFromCode string
     * @param $stationToCode string
     * @param $date \DateTime
     * @return Train[]
     * @throws \Exception
     */
    protected function searchTrains($stationFromCode, $stationToCode, \DateTime $date)
    {
        $connector = new RestClient;
        $connector->setHeaders(array(
            'GV-Ajax'    => '1',
            'GV-Referer' => $this->getBaseUrl(),
            'GV-Token'   => $this->authorizationToken,
        ));
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'station_id_from' => $stationFromCode,
            'station_id_till' => $stationToCode,
            'date_dep'        => $date->format('d.m.Y'),
            'time_dep'        => '00:00',
            'time_dep_till'   => '',
            'another_ec'      => '0',
            'search'          => ''
        ));

        $connector->sendRequest($this->getBaseUrl() . 'purchase/search/');
        $response = (array)json_decode($connector->getResponseBody(), true);

        if (isset($response['error']) && $response['error']) {
            throw new \Exception($response['value']);
        }

        $trains = [];
        foreach ($response['value'] as $trainData) {
            $trains[] = $this->builder->constructTrain($trainData);
        }

        return $trains;
    }

    //###################################

    /**
     * @param $stationFromCode string
     * @param $stationToCode string
     * @param string $trainNumber
     * @param string $seatCode
     * @param $date \DateTime
     * @return Train\Coach[]
     * @throws \Exception
     */
    protected function searchCoaches($stationFromCode, $stationToCode, $trainNumber, $seatCode, \DateTime $date)
    {
        $connector = new RestClient;
        $connector->setHeaders(array(
            'GV-Ajax'    => '1',
            'GV-Referer' => $this->getBaseUrl(),
            'GV-Token'   => $this->authorizationToken,
        ));
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'station_id_from' => $stationFromCode,
            'station_id_till' => $stationToCode,
            'train'           => $trainNumber,
            'coach_type'      => $seatCode,
            'date_dep'        => $date->getTimestamp(),
        ));

        $connector->sendRequest($this->getBaseUrl().'purchase/coaches/');
        $response = (array)json_decode($connector->getResponseBody(), true);

        if (isset($response['error']) && $response['error']) {
            throw new \Exception($response['value']);
        }

        $coaches = [];
        foreach ($response['coaches'] as $coachData) {
            $coaches[] = $this->builder->constructCoach($trainNumber, $coachData);
        }

        return $coaches;
    }

    //###################################

    private function getLocale()
    {
        $locale = isset($this->params['locale']) ? $this->params['locale'] : self::LOCALE_RU;

        if (!in_array($locale, [self::LOCALE_EN, self::LOCALE_RU, self::LOCALE_UA])) {
            throw new \Exception("Invalid locale provided [{$locale}]");
        }
        return $locale;
    }

    private function getBaseUrl()
    {
        return self::FRONTEND_URL.$this->getLocale().'/';
    }

    //###################################

    public function setParams(array $params = array())
    {
        return $this->params = $params;
    }

    //-------------------------------------

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