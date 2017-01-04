<?php

namespace RAIsaev\UzTicketsParser;

class Train
{
    /** @var string */
    protected $number;

    /** @var Station */
    protected $stationFrom;

    /** @var Station */
    protected $stationTo;

    /** @var  \DateTime */
    protected $stationFromDate;

    /** @var  \DateTime */
    protected $stationToDate;

    /** @var  \DateInterval */
    protected $tripTime;

    /** @var Train\Seat[] */
    protected $seats = [];

    //###################################

    public function __construct(
        $number,
        Station $stationFrom,
        Station $stationTo,
        \DateTime $stationFromDate,
        \DateTime $stationToDate,
        array $seats
    ){
        $this->number = $number;
        $this->stationFrom = $stationFrom;
        $this->stationTo = $stationTo;
        $this->stationFromDate = $stationFromDate;
        $this->stationToDate = $stationToDate;
        $this->seats = $seats;

        $this->tripTime = $this->stationToDate->diff($this->stationFromDate);
    }

    //###################################

    public function getNumber()
    {
        return $this->number;
    }

    public function getTripTime()
    {
        return $this->tripTime;
    }

    public function getStationFrom()
    {
        return $this->stationFrom;
    }

    public function getStationTo()
    {
        return $this->stationTo;
    }

    public function getSeats()
    {
        return $this->seats;
    }

    public function getStationFromDate($format = null)
    {
        if (!is_null($format)) {
            return $this->stationFromDate->format($format);
        }
        return $this->stationFromDate;
    }

    public function getStationToDate($format = null)
    {
        if (!is_null($format)) {
            return $this->stationToDate->format($format);
        }
        return $this->stationToDate;
    }

    //###################################
}