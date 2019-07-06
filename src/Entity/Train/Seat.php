<?php

namespace Raisaev\UzTicketsParser\Entity\Train;

class Seat
{
    //todo locales
    const TYPE_VIP     = 'Л';
    const TYPE_COUPE   = 'К';
    const TYPE_BERTH   = 'П';
    const TYPE_COMMON  = 'О';
    const TYPE_SITTING = 'С';

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