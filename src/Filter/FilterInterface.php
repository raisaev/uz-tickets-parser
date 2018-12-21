<?php

namespace Raisaev\UzTicketsParser\Filter;

use Raisaev\UzTicketsParser\Train;

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