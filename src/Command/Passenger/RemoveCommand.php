<?php

namespace Raisaev\UzTicketsParser\Command\Passenger;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Entity\Passenger;

class RemoveCommand extends Command
{
    protected static $defaultName = 'passenger:remove';

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
        $this->setDescription('Remove a Passenger from storage [by email]')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'Email'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->repo->remove($input->getArgument('email')) ? $io->success('Done') : $io->error('Is not exists.');
    }

    //########################################
}
