<?php

namespace Raisaev\UzTicketsParser;

class Train
{
    /** @var Station */
    protected $from;

    /** @var Station */
    protected $to;

    // ---------------------------------------

    /** @var string */
    protected $number;

    /** @var Station */
    protected $stationFrom;

    /** @var  \DateTime */
    protected $stationFromDate;

    /** @var Station */
    protected $stationTo;

    /** @var  \DateTime */
    protected $stationToDate;

    // ---------------------------------------

    /** @var  \DateInterval */
    protected $tripTime;

    /** @var Train\Seat[] */
    protected $seats = [];

    //###################################

    public function __construct(
        Station $from, Station $to,
        $number, Station $stationFrom, Station $stationTo,
        \DateTime $stationFromDate, \DateTime $stationToDate,
        array $seats
    ){
        $this->from = $from;
        $this->to = $to;
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

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
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