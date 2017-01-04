<?php

namespace RAIsaev\UzTicketsParser\Filter;

use RAIsaev\UzTicketsParser\Train;

class TrainNumber extends AbstractModel
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

    public function filter(array &$trains)
    {
        foreach ($trains as $key => $train) {
            /** @var Train $train */

            if (!in_array($train->getNumber(), $this->trainNumbers)) {
                unset($trains[$key]);
            }
        }
    }

    // ########################################

    public function setTrainNumbers(array $trainNumbers)
    {
        $this->trainNumbers = $trainNumbers;
    }

    public function getTrainNumbers()
    {
        return $this->trainNumbers;
    }

    // ########################################
}