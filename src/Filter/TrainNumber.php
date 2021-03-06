<?php

namespace Raisaev\UzTicketsParser\Filter;

use Raisaev\UzTicketsParser\Entity\Train;

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

    public function apply(array &$entities)
    {
        if (empty($this->trainNumbers)) {
            return;
        }

        foreach ($entities as $key => $train) {
            /** @var Train $train */

            if (!in_array($train->getNumber(), $this->trainNumbers, true)) {
                unset($entities[$key]);
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