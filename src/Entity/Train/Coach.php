<?php

namespace Raisaev\UzTicketsParser\Entity\Train;

class Coach
{
    /** @var string */
    protected $trainNumber;

    protected $number;
    protected $type;
    protected $class;
    protected $price;

    protected $freeSeats;
    protected $freeSeatsNumbers = array();

    //###################################

    public function __construct(
        $trainNumber,
        $number,
        $type,
        $class,
        $price,
        $freeSeats,
        $freeSeatsNumbers = array()
    ){
        $this->trainNumber = $trainNumber;
        $this->number = $number;
        $this->type = $type;
        $this->class = $class;
        $this->price = $price;
        $this->freeSeats = $freeSeats;
        $this->freeSeatsNumbers = $freeSeatsNumbers;
    }

    //###################################

    public function getTrainNumber()
    {
        return $this->trainNumber;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getFreeSeats()
    {
        return $this->freeSeats;
    }

    // ---------------------------------------

    public function setFreeSeatsNumbers($value)
    {
        $this->freeSeatsNumbers = $value;
    }

    public function getFreeSeatsNumbers()
    {
        return $this->freeSeatsNumbers;
    }

    //###################################
}