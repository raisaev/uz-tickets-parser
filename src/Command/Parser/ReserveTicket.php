<?php

namespace Raisaev\UzTicketsParser\Command\Parser;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Parser;
use Raisaev\UzTicketsParser\Entity\Station;

class ReserveTicket extends Command
{
    protected static $defaultName = 'parser:reserve-ticket';

    /** @var Parser */
    private $parser;

    /** @var \Raisaev\UzTicketsParser\Passenger\Repository */
    private $repo;

    //########################################

    public function __construct(
        Parser $parser,
        \Raisaev\UzTicketsParser\Passenger\Repository $repo,
        $name = null
    ){
        $this->parser = $parser;
        $this->repo   = $repo;
        parent::__construct($name);
    }

    //########################################

    protected function configure()
    {
        $coachesTypes = implode(' | ', array_keys($this->parser->getSeatCodeByType()));

        $this
            ->setDefinition([
                new InputArgument('from', InputArgument::REQUIRED, 'Station code from'),
                new InputArgument('to', InputArgument::REQUIRED, 'Station code to'),
                new InputArgument('date', InputArgument::REQUIRED, 'Departure date. Format YYYY-MM-DD'),
                new InputArgument('train', InputArgument::REQUIRED, 'Train Number'),
                new InputArgument('coach-type', InputArgument::REQUIRED, 'Coach Type'),
                new InputArgument('coach-number', InputArgument::REQUIRED, 'Coach Number'),
                new InputArgument('seat-number', InputArgument::REQUIRED, 'Seat Number'),
                new InputArgument('passenger', InputArgument::REQUIRED, 'Must be specified by email'),
            ])
            ->setHelp(<<<TEXT
            
  <info>[from] - departure station</info>:
        can be discovered by using <info>parser::suggest-station</info> command
  
  <info>[to] - arrival station</info>:
        can be discovered by using <info>parser::suggest-station</info> command
  
  <info>[date] - date of departure</info>:
        in format YYYY-MM-DD
  
  <info>[train] - train number</info>:
        in format 265Ш
  
  <info>[coach-type] - coach type</info>:
        one of the following [{$coachesTypes}]
        
  <info>[coach-number] - coach number</info>:
        can be discovered by using <info>parser::search-coaches</info> command

  <info>[seat-number] - seat number</info>:
        can be discovered by using <info>parser::search-coaches</info> command
  
  <info>[passenger] - passenger email</info>:
        can be created by using <info>parser::passenger-add</info> command
TEXT
            )
            ->setDescription('Reserve ticket');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $from = new Station(null, $input->getArgument('from'));
        $to   = new Station(null, $input->getArgument('to'));
        $date = new \DateTime($input->getArgument('date'));

        $passenger = $this->repo->get($input->getArgument('passenger'));
        if ($passenger === null) {

            $io->error('Passenger in not found.');
            return;
        }

        $coaches = $this->parser->getCoaches(
            $from, $to, $input->getArgument('train'), $input->getArgument('coach-type'), $date
        );

        foreach ($this->parser->getErrorMessages() as $message) {
            $io->error($message);
        }

        $coachNumber = $input->getArgument('coach-number');
        $seatNumber = $input->getArgument('seat-number');

        $coachFound = null;
        foreach ($coaches as $coach) {
            if ($coach->getNumber() == $coachNumber && in_array($seatNumber, $coach->getFreeSeatsNumbers())) {
                $coachFound = $coach;
                break;
            }
        }

        if (null === $coachFound) {

            $io->error('Coach is not found.');
            return;
        }

        $reserve = $this->parser->reserveTicket(
            $from, $to, $passenger, $coachFound, $input->getArgument('seat-number'), $date
        );

        foreach ($this->parser->getErrorMessages() as $message) {
            $io->error($message);
        }
    }

    //########################################
}
