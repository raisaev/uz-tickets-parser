<?php

namespace Raisaev\UzTicketsParser;

use Raisaev\UzTicketsParser\Rest\Client as RestClient;
use Raisaev\UzTicketsParser\Entity\Train\Seat;
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

    private $seatTypesMap = [
        self::RU_LOC => [
            Seat::TYPE_VIP     => 'Л',
            Seat::TYPE_COUPE   => 'К',
            Seat::TYPE_BERTH   => 'П',
            Seat::TYPE_COMMON  => 'О',
            Seat::TYPE_SITTING => 'С',
        ]
    ];

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

        $this->initCookiesAndToken();
    }

    // ---------------------------------------

    private function initCookiesAndToken()
    {
        $cookies = $this->cache->getItem(self::REQUEST_COOKIES_STORAGE_KEY);
        if ($cookies->isHit()) {
            $this->authorizationCookies = $cookies->get();
        }
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
                $suggestions->expiresAfter(60 * 60 * 24);

                $this->cache->save($suggestions);
            }

            return $suggestions->get();

        } catch (\Exception $e) {

            $this->errorMessages[] = $e->getMessage();
            return [];
        }
    }

    protected function searchStationsSuggestions($locationTitle)
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

    // ---------------------------------------

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

            $this->clearErrorMessages();

            $trains = $this->searchTrains(
                $stationFrom->getCode(), $stationTo->getCode(), $date
            );
            foreach ($filters as $filter) {
                $filter->apply($trains);
            }

            return $trains;

        } catch (\Exception $e) {

            $this->errorMessages[] = $e->getMessage();
            return [];
        }
    }

    protected function searchTrains(
        $stationFrom,
        $stationTo,
        \DateTime $date
    ){
        $connector = $this->di->get(RestClient::class);
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'from' => $stationFrom,
            'to'   => $stationTo,
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

    // ---------------------------------------

    /**
     * @param $stationFrom Entity\Station
     * @param $stationTo Entity\Station
     * @param $trainNumber string
     * @param $seatCode string
     * @param $date \DateTime
     * @param $filters Filter\FilterInterface[]
     *
     * @return Entity\Train\Coach[]
     */
    public function getCoaches(
        Entity\Station $stationFrom,
        Entity\Station $stationTo,
        $trainNumber,
        $seatCode,
        \DateTime $date,
        $filters = []
    ){
        try {

            $this->clearErrorMessages();

            $coaches = $this->searchCoaches(
                $stationFrom->getCode(), $stationTo->getCode(), $trainNumber, $seatCode, $date
            );
            foreach ($filters as $filter) {
                $filter->apply($trains);
            }

            return $coaches;

        } catch (\Exception $e) {

            $this->errorMessages[] = $e->getMessage();
            return [];
        }
    }

    protected function searchCoaches(
        $stationFrom,
        $stationTo,
        $trainNumber,
        $seatCode,
        \DateTime $date
    ){
        $connector = $this->di->get(RestClient::class);
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'from'          => $stationFrom,
            'to'            => $stationTo,
            'train'         => $trainNumber,
            'wagon_type_id' => $this->getSeatCodeByType($seatCode),
            'date'          => $date->format('Y-m-d'),
            'get_tpl'       => '0'
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

                $coach = $this->builder->constructCoach($trainNumber, $coachData);
                $coach->setFreeSeatsNumbers($this->searchSeats(
                    $stationFrom,
                    $stationTo,
                    $coach->getTrainNumber(),
                    $coach->getNumber(),
                    $coach->getType(),
                    $coach->getClass(),
                    $date
                ));

                $coaches[] = $coach;
            }
        }

        return $coaches;
    }

    protected function searchSeats(
        $stationFrom,
        $stationTo,
        $trainNumber,
        $coachNumber,
        $coachType,
        $coachClass,
        \DateTime $date
    ){
        $connector = $this->di->get(RestClient::class);
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'from'        => $stationFrom,
            'to'          => $stationTo,
            'train'       => $trainNumber,
            'date'        => $date->format('Y-m-d'),
            'wagon_num'   => $coachNumber,
            'wagon_type'  => $coachType,
            'wagon_class' => $coachClass
        ));

        $connector->sendRequest($this->getBaseUrl() .'train_wagon/');
        $response = (array)json_decode($connector->getResponseBody(), true);

        if (!empty($response['captcha'])) {
            throw new Exception\ParsingException('Unable to parse data. Captcha required.');
        }

        if (!empty($response['error']) && !empty($response['data'])) {
            throw new Exception\ParsingException("Unable to parse data. {$response['data']}");
        }

        return !empty($response['data']['places']) ? reset($response['data']['places']) : [];
    }

    // ---------------------------------------

    public function reserveTicket(
        Entity\Station $stationFrom,
        Entity\Station $stationTo,
        Entity\Passenger $passenger,
        Entity\Train\Coach $coach,
        $seatNumber,
        \DateTime $date
    ){
        try {

            $this->clearErrorMessages();

            return $this->doReserveTicket(
                $stationFrom,
                $stationTo,
                $passenger,
                $coach->getTrainNumber(),
                $coach->getNumber(),
                $coach->getClass(),
                $coach->getType(),
                $seatNumber,
                $date
            );

        } catch (\Exception $e) {

            $this->errorMessages[] = $e->getMessage();
            return [];
        }
    }

    /**
     * @param Entity\Station $stationFrom
     * @param Entity\Station $stationTo
     * @param Entity\Passenger $passenger
     * @param $trainNumber
     * @param $coachNumber
     * @param $coachClass
     * @param $coachType
     * @param $seatNumber
     * @param \DateTime $date
     *
     * @return array|mixed
     * @throws Exception\ParsingException
     */
    protected function doReserveTicket(
        Entity\Station $stationFrom,
        Entity\Station $stationTo,
        Entity\Passenger $passenger,
        $trainNumber,
        $coachNumber,
        $coachClass,
        $coachType,
        $seatNumber,
        \DateTime $date
    ){
        $connector = $this->di->get(RestClient::class);
        $connector->setCookies($this->authorizationCookies);
        $connector->setPost(array(
            'places[0][ord]'         => '0',
            'places[0][from]'        => $stationFrom->getCode(),
            'places[0][to]'          => $stationTo->getCode(),
            'places[0][train]'       => $trainNumber,
            'places[0][date]'        => $date->format('Y-m-d'),
            'places[0][wagon_num]'   => $coachNumber,
            'places[0][place_num]'   => $seatNumber,
            'places[0][wagon_class]' => $coachClass,
            'places[0][wagon_type]'  => $coachType,

            'places[0][firstname]'   => $passenger->getFirstName(),
            'places[0][lastname]'    => $passenger->getLastName(),
            'places[0][bedding]'     => '1',
            'places[0][child]'       => (int)$passenger->getIsChild(),
            'places[0][student]'     => (int)$passenger->getIsStudent(),
            'places[0][reserve]'     => '0',
        ));

        $connector->sendRequest($this->getBaseUrl() .'cart/add/');
        $response = (array)json_decode($connector->getResponseBody(), true);
var_dump(
    $connector
);
die;
        if (!empty($response['captcha'])) {
            throw new Exception\ParsingException('Unable to parse data. Captcha required.');
        }

        if (!empty($response['error']) && !empty($response['data'])) {
            throw new Exception\ParsingException("Unable to parse data. {$response['data']}");
        }

        if (empty($response['cartCount'])) {
            throw new Exception\ParsingException('Unable to reserve ticket.');
        }

        return $response;
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

    public function getSeatCodeByType($type = null)
    {
        if (null === $type) {
            return $this->seatTypesMap[$this->locale];
        }

        if (!isset($this->seatTypesMap[$this->locale][$type])) {
            throw new \LogicException("Seat code provided [{$type}] is not exists");
        }

        return $this->seatTypesMap[$this->locale][$type];
    }

    //###################################
}