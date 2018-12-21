<?php

namespace Raisaev\UzTicketsParser\Train;

class Coach
{
    /** @var string */
    protected $trainNumber;

    protected $number;
    protected $type;
    protected $class;
    protected $price;
    protected $places;

    //###################################

    public function __construct(
        $trainNumber, $number, $type, $class, $price, $places
    ){
        $this->trainNumber = $trainNumber;
        $this->number = $number;
        $this->type = $type;
        $this->class = $class;
        $this->price = $price;
        $this->places = $places;
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

    public function getPlaces()
    {
        return $this->places;
    }

    //###################################
}