<?php

namespace Raisaev\UzTicketsParser\Entity;

class Train
{
    /** @var string */
    protected $number;

    /** @var Station */
    protected $stationFormationFrom;

    /** @var Station */
    protected $stationFrom;

    /** @var \DateTime */
    protected $stationFromDate;

    /** @var Station */
    protected $stationFormationTo;

    /** @var Station */
    protected $stationTo;

    /** @var \DateTime */
    protected $stationToDate;

    /** @var Train\Seat[] */
    protected $seats = [];

    /** @var \DateInterval */
    protected $tripTime;

    //###################################

    public function __construct(
        $number,
        Station $stationFormationFrom,
        Station $stationFrom,
        \DateTime $stationFromDate,
        Station $stationFormationTo,
        Station $stationTo,
        \DateTime $stationToDate,
        array $seats
    ){
        $this->number = $number;
        $this->stationFormationFrom = $stationFormationFrom;
        $this->stationFrom = $stationFrom;
        $this->stationFromDate = $stationFromDate;
        $this->stationFormationTo = $stationFormationTo;
        $this->stationTo = $stationTo;
        $this->stationToDate = $stationToDate;
        $this->seats = $seats;

        $this->tripTime = $this->stationToDate->diff($this->stationFromDate);
    }

    //###################################

    public function getNumber()
    {
        return $this->number;
    }

    public function getStationFormationFrom()
    {
        return $this->stationFormationFrom;
    }

    public function getStationFrom()
    {
        return $this->stationFrom;
    }

    public function getStationFormationTo()
    {
        return $this->stationFormationTo;
    }

    public function getStationFromDate($format = null)
    {
        if (!is_null($format)) {
            return $this->stationFromDate->format($format);
        }
        return $this->stationFromDate;
    }

    public function getStationTo()
    {
        return $this->stationTo;
    }

    public function getStationToDate($format = null)
    {
        if (!is_null($format)) {
            return $this->stationToDate->format($format);
        }
        return $this->stationToDate;
    }

    public function getSeats()
    {
        return $this->seats;
    }

    public function getTripTime($format = null)
    {
        if (!is_null($format)) {
            return $this->tripTime->format($format);
        }
        return $this->tripTime;
    }

    //###################################
}