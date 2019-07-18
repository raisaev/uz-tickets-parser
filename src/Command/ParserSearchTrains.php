<?php

namespace Raisaev\UzTicketsParser\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Parser;
use Raisaev\UzTicketsParser\Filter;
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

    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('from', InputArgument::REQUIRED, 'Station code from'),
                new InputArgument('to', InputArgument::REQUIRED, 'Station code to'),
                new InputArgument('date', InputArgument::REQUIRED, 'Departure date. Format YYYY-MM-DD'),
                new InputOption('train-filter', '-tf', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Show only provided trains.'),
                new InputOption('full', '-f', InputOption::VALUE_OPTIONAL, 'Display trains with no free seats [y/n]', 'n'),
            ])
            ->setDescription('Search trains')
            ->setHelp(<<<'EOF'
            
   <info>[from] - departure station</info>:
        can be discovered by using <info>parser::suggest-station</info> command
  
   <info>[to] - arrival station</info>:
        can be discovered by using <info>parser::suggest-station</info> command
  
   <info>[date] - date of departure</info>:
        in format YYYY-MM-DD


   <info>--train-filter</info>
        show only provided trains: --train-filter=065П --train-filter=265Ш
   
   <info>--full</info>
        display trains with no free seats: [y/n]. Default: n
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = new Station(null, $input->getArgument('from'));
        $to   = new Station(null, $input->getArgument('to'));
        $date = new \DateTime($input->getArgument('date'));

        $trains = [];
        $filters = [];
        if ($input->getOption('train-filter')) {
            $filters[] = new Filter\TrainNumber($input->getOption('train-filter'));
        }

        foreach ($this->parser->getTrains($from, $to, $date, $filters) as $train) {

            $seats = [];
            foreach ($train->getSeats() as $seat) {
                $seats[] = "[{$seat->getCode()}] {$seat->getTitle()}: {$seat->getPlaces()}";
            }

            if (empty($seats) && $input->getOption('full') === strtolower('n')) {
                continue;
            }

            $trains[] = [
                $train->getNumber(),
                "{$train->getStationFormationFrom()->getTitle()} - {$train->getStationFormationTo()->getTitle()}",
                $train->getStationFromDate('Y-m-d H:i'),
                $train->getStationToDate('Y-m-d H:i'),
                $train->getTripTime('%H:%I'),
                implode(PHP_EOL, $seats)
            ];
        }

        $io = new SymfonyStyle($input, $output);
        if (!empty($filters)) {
            $io->caution('Search filters were applied.');
        }
        $io->table(['Train', 'Route', 'Departure', 'Arrival', 'Trip Time', 'Seats'], $trains);

        foreach ($this->parser->getErrorMessages() as $message) {
            $io->error($message);
        }
    }

    //########################################
}
