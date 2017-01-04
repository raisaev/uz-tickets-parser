<?php

namespace RAIsaev\UzTicketsParser\Train;

class Seat
{
    const TYPE_VIP    = 'Л';
    const TYPE_COUPE  = 'К';
    const TYPE_BERTH  = 'П';
    const TYPE_COMMON = 'О';

    protected $code;
    protected $title;
    protected $places;

    //###################################

    public function __construct($code, $title, $places = 0)
    {
        $this->code = $code;
        $this->title = $title;
        $this->places = $places;
    }

    //###################################

    public function getCode()
    {
        return $this->code;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getPlaces()
    {
        return $this->places;
    }

    //###################################
}