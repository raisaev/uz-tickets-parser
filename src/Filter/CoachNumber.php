<?php

namespace Raisaev\UzTicketsParser\Filter;

use Raisaev\UzTicketsParser\Entity\Train;

class CoachNumber implements FilterInterface
{
    protected $coachNumbers = [];

    // ########################################

    public function __construct(array $coachNumbers = [])
    {
        $this->coachNumbers = $coachNumbers;
    }

    // ########################################

    public function getLabel()
    {
        return 'Coach Number';
    }

    public function apply(array &$entities)
    {
        if (empty($this->coachNumbers)) {
            return;
        }

        foreach ($entities as $key => $coach) {
            /** @var Train\Coach $coach */
            if (!in_array($coach->getNumber(), $this->coachNumbers, true)) {
                unset($entities[$key]);
            }
        }
    }

    // ########################################

    public function getCoachNumbers()
    {
        return $this->coachNumbers;
    }

    // ########################################
}