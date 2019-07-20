<?php

namespace Raisaev\UzTicketsParser\Command\Passenger;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Entity\Passenger;

class AddCommand extends Command
{
    protected static $defaultName = 'passenger:add';

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
        $this->setDescription('Add a Passenger to storage')
            ->setDefinition([
                new InputArgument('first-name', InputArgument::REQUIRED, 'First Name'),
                new InputArgument('last-name', InputArgument::REQUIRED, 'Last Name'),
                new InputArgument('email', InputArgument::REQUIRED, 'Email'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $pass = new Passenger(
            $input->getArgument('first-name'), $input->getArgument('last-name'), $input->getArgument('email')
        );
        $this->repo->save($pass) ? $io->success('Done') : $io->error('Is already exists.');
    }

    //########################################
}
