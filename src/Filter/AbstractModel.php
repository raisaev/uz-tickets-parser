<?php

namespace RAIsaev\UzTicketsParser\Filter;

use RAIsaev\UzTicketsParser\Train;

abstract class AbstractModel
{
    // ########################################

    abstract public function getLabel();

    /**
     * @param Train[] $trains
     */
    abstract public function filter(array &$trains);

    // ########################################
}