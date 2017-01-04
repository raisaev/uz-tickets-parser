<?php

namespace RAIsaev\UzTicketsParser\Train;

class Coach
{
    /** @var string */
    protected $trainNumber;

    protected $number;
    protected $type;
    protected $class;
    protected $price;
    protected $places;
    protected $includesBedding = false;

    //###################################

    public function __construct(
        $trainNumber,
        $number,
        $type,
        $class,
        $price,
        $places,
        $includesBedding
    ){
        $this->trainNumber = $trainNumber;
        $this->number = $number;
        $this->type = $type;
        $this->class = $class;
        $this->price = $price;
        $this->places = $places;
        $this->includesBedding = $includesBedding;
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

    public function isIncludesBedding()
    {
        return $this->includesBedding;
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

    public function getTitle()
    {
        switch ($this->type) {
            case Seat::TYPE_BERTH:
                return 'Berth';
                break;

            case Seat::TYPE_COUPE:
                return 'Coupe';
                break;

            case Seat::TYPE_VIP:
                return 'Vip';
                break;

            case Seat::TYPE_COMMON:
                return 'Common';
                break;

            default:
                return 'Unknown';
                break;
        }
    }

    //###################################
}