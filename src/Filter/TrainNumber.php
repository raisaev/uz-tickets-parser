<?php

namespace Raisaev\UzTicketsParser\Filter;

use Raisaev\UzTicketsParser\Train;

class TrainNumber implements FilterInterface
{
    protected $trainNumbers = [];

    // ########################################

    public function __construct(array $trainNumbers = [])
    {
        $this->trainNumbers = $trainNumbers;
    }

    // ########################################

    public function getLabel()
    {
        return 'Train Number';
    }

    public function apply(array &$trains)
    {
        foreach ($trains as $key => $train) {
            /** @var Train $train */

            if (!in_array($train->getNumber(), $this->trainNumbers)) {
                unset($trains[$key]);
            }
        }
    }

    // ########################################

    public function getTrainNumbers()
    {
        return $this->trainNumbers;
    }

    // ########################################
}