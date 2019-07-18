<?php

namespace Raisaev\UzTicketsParser\Entity\Train;

class Seat
{
    const TYPE_VIP     = 'vip';
    const TYPE_COUPE   = 'coupe';
    const TYPE_BERTH   = 'berth';
    const TYPE_COMMON  = 'common';
    const TYPE_SITTING = 'sitting';

    protected $code;
    protected $title;
    protected $places;

    //###################################

    public function __construct($code, $title, $places)
    {
        $this->code   = $code;
        $this->title  = $title;
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