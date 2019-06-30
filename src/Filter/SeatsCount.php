<?php

namespace Raisaev\UzTicketsParser\Filter;

use Raisaev\UzTicketsParser\Entity\Train;

class SeatsCount implements FilterInterface
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

    public function apply(array &$trains)
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

    public function getSeatType()
    {
        return $this->seatType;
    }

    public function getSeatCount()
    {
        return $this->seatCount;
    }

    // ########################################
}