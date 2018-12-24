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

    /** @var \Raisaev\UzTicketsParser\Train\Seat[] */
    protected $seats = [];

    //###################################

    public function __construct(
        $trainNumber,
        $number,
        $type,
        $class,
        $price,
        array $seats
    ){
        $this->trainNumber = $trainNumber;
        $this->number = $number;
        $this->type = $type;
        $this->class = $class;
        $this->price = $price;

        $this->seats = $seats;
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

    public function getSeats()
    {
        return $this->seats;
    }

    //###################################
}