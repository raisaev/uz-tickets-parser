<?php

namespace Raisaev\UzTicketsParser\Entity;

class Passenger
{
    protected $firstName;
    protected $lastName;
    protected $email;

    protected $isStudent = false;
    protected $isChild = false;

    //###################################

    public function __construct($firstName, $lastName, $email, $isStudent = false, $isChild = false)
    {
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->email     = $email;
        $this->isStudent = $isStudent;
        $this->isChild   = $isChild;
    }

    //###################################

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getIsStudent()
    {
        return $this->isStudent;
    }

    public function getIsChild()
    {
        return $this->isChild;
    }

    //###################################
}