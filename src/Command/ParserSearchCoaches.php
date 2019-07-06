<?php

namespace Raisaev\UzTicketsParser\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Parser;
use Raisaev\UzTicketsParser\Entity\Station;

class ParserSearchCoaches extends Command
{
    protected static $defaultName = 'parser:search-coaches';

    /** @var Parser */
    private $parser;

    //########################################

    public function __construct(
        Parser $parser,
        $name = null
    ){
        $this->parser = $parser;
        parent::__construct($name);
    }

    //########################################

    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('from', InputArgument::REQUIRED, 'Station code from'),
                new InputArgument('to', InputArgument::REQUIRED, 'Station code to'),
                new InputArgument('date', InputArgument::REQUIRED, 'Departure date. Format YYYY-MM-DD'),
                new InputArgument('train', InputArgument::REQUIRED, 'Train Number'),
                new InputArgument('coach-type', InputArgument::REQUIRED, 'Coach Type'),
            ])
            ->setDescription('Search trains');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from      = new Station(null, $input->getArgument('from'));
        $to        = new Station(null, $input->getArgument('to'));
        $trainCode = $input->getArgument('train');
        $coachType = $input->getArgument('coach-type');
        $date      = new \DateTime($input->getArgument('date'));

        $coaches = [];
        foreach ($this->parser->getCoaches($from, $to, $trainCode, $coachType, $date) as $coach) {

            $coaches[] = [
                $coach->getNumber(),
                $coach->getClass(),
                $coach->getType(),
                $coach->getPrice(),
                $coach->getFreeSeats(),
                implode(' ', $coach->getFreeSeatsNumbers()),
            ];
        }

        $io = new SymfonyStyle($input, $output);
        $io->table(['Number', 'Class', 'Type', 'Price', 'Free', 'Seats'], $coaches);

        foreach ($this->parser->getErrorMessages() as $message) {
            $io->error($message);
        }
    }

    //########################################
}
