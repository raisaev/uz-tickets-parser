<?php

namespace RAIsaev\UzTicketsParser;

class Station
{
    protected $title;
    protected $code;

    //###################################

    public function __construct($title, $code)
    {
        $this->title = $title;
        $this->code  = $code;
    }

    //###################################

    public function getTitle()
    {
        return $this->title;
    }

    public function getCode()
    {
        return $this->code;
    }

    //###################################
}