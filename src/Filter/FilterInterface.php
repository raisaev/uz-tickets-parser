<?php

namespace Raisaev\UzTicketsParser\Filter;

use Raisaev\UzTicketsParser\Entity\Train;

interface FilterInterface
{
    // ########################################

    public function getLabel();

    /**
     * @param $entities
     */
    public function apply(array &$entities);

    // ########################################
}