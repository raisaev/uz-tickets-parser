<?php

namespace Raisaev\UzTicketsParser\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Parser;
use Raisaev\UzTicketsParser\Entity\Station;

class ParserSearchTrains extends Command
{
    protected static $defaultName = 'parser:search-trains';

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

    //todo filters
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('from', InputArgument::REQUIRED, 'Station code from'),
                new InputArgument('to', InputArgument::REQUIRED, 'Station code to'),
                new InputArgument('date', InputArgument::REQUIRED, 'Departure date. Format YYYY-MM-DD'),
            ])
            ->setDescription('Search trains');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = new Station(null, $input->getArgument('from'));
        $to   = new Station(null, $input->getArgument('to'));
        $date = new \DateTime($input->getArgument('date'));

        $trains = [];
        foreach ($this->parser->getTrains($from, $to, $date) as $train) {

            $seats = [];
            foreach ($train->getSeats() as $seat) {
                $seats[] = "{$seat->getTitle()}: {$seat->getPlaces()}";
            }

            $trains[] = [
                $train->getNumber(),
                "{$train->getStationFormationFrom()->getTitle()} - {$train->getStationFormationTo()->getTitle()}",
                $train->getStationFromDate('Y-m-d H:i'),
                $train->getStationToDate('Y-m-d H:i'),
                implode(PHP_EOL, $seats)
            ];
        }

        $io = new SymfonyStyle($input, $output);
        $io->table(['Number', 'Direction', 'Departure', 'Arrival', 'Seats'], $trains);

        foreach ($this->parser->getErrorMessages() as $message) {
            $io->error($message);
        }
    }

    //########################################
}
