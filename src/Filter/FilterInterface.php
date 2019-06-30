<?php

namespace Raisaev\UzTicketsParser\Filter;

use Raisaev\UzTicketsParser\Entity\Train;

interface FilterInterface
{
    // ########################################

    public function getLabel();

    /**
     * @param Train[] $trains
     */
    public function apply(array &$trains);

    // ########################################
}