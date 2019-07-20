<?php

namespace Raisaev\UzTicketsParser\Command\Passenger;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GetCommand extends Command
{
    protected static $defaultName = 'passenger:get';

    /** @var \Raisaev\UzTicketsParser\Passenger\Repository */
    private $repo;

    //########################################

    public function __construct(
        \Raisaev\UzTicketsParser\Passenger\Repository $repo,
        $name = null
    ){
        $this->repo = $repo;
        parent::__construct($name);
    }

    //########################################

    protected function configure()
    {
        $this->setDescription('Get stored Passengers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (empty($this->repo->get())) {

            $io->note('No passengers');
            return;
        }

        $passengers = [];
        foreach ($this->repo->get() as $pass) {
            $passengers[] = [$pass->getEmail(), $pass->getFirstName(), $pass->getLastName()];
        }

        $io->table(['Email', 'First Name', 'Last Name'], $passengers);
    }

    //########################################
}
