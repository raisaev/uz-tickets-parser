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
        $coachesTypes = implode(' | ', $this->parser->getSeatCodeByType());

        $this
            ->setDefinition([
                new InputArgument('from', InputArgument::REQUIRED, 'Station code from'),
                new InputArgument('to', InputArgument::REQUIRED, 'Station code to'),
                new InputArgument('date', InputArgument::REQUIRED, 'Departure date. Format YYYY-MM-DD'),
                new InputArgument('train', InputArgument::REQUIRED, 'Train Number'),
                new InputArgument('coach-type', InputArgument::REQUIRED, 'Coach Type'),
                new InputOption('coach-filter', '-сf', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Show only provided coaches.'),
            ])
            ->setDescription('Search coaches')
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

  
  <info>--coach-filter</info>
        show only provided coaches: --coach-filter=10 --coach-filter=6
TEXT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from      = new Station(null, $input->getArgument('from'));
        $to        = new Station(null, $input->getArgument('to'));
        $trainCode = $input->getArgument('train');
        $coachType = $input->getArgument('coach-type');
        $date      = new \DateTime($input->getArgument('date'));

        $coaches = [];
        $filters = [];
        if ($input->getOption('coach-filter')) {
            $filters[] = new Filter\CoachNumber($input->getOption('coach-filter'));
        }

        foreach ($this->parser->getCoaches($from, $to, $trainCode, $coachType, $date, $filters) as $coach) {

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
        if (!empty($filters)) {
            $io->caution('Search filters were applied.');
        }
        $io->table(['Number', 'Class', 'Type', 'Price', 'Free', 'Seats'], $coaches);

        foreach ($this->parser->getErrorMessages() as $message) {
            $io->error($message);
        }
    }

    //########################################
}
