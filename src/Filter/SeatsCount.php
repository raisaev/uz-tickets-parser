<?php

namespace RAIsaev\UzTicketsParser\Filter;

use RAIsaev\UzTicketsParser\Train;

class SeatsCount extends AbstractModel
{
    protected $seatType;
    protected $seatCount;

    // ########################################

    public function __construct($seatType, $seatCount)
    {
        $this->seatType  = $seatType;
        $this->seatCount = (int)$seatCount;
    }

    // ########################################

    public function getLabel()
    {
        return 'Seats';
    }

    public function filter(array &$trains)
    {
        foreach ($trains as $key => $train) {
            /** @var Train $train */

            $isFound = false;
            foreach ($train->getSeats() as $seat) {

                if ($seat->getCode() == $this->seatType && $seat->getPlaces() >= $this->seatCount) {

                    $isFound = true;
                    break;
                }
            }

            if (!$isFound) {
                unset($trains[$key]);
            }
        }
    }

    // ########################################

    public function setSeatType($type)
    {
        $this->seatType = $type;
    }

    public function getSeatType()
    {
        return $this->seatType;
    }

    public function setSeatCount($count)
    {
        $this->seatCount = (int)$count;
    }

    public function getSeatCount()
    {
        return $this->seatCount;
    }

    // ########################################
}