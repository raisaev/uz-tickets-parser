<?php

namespace Raisaev\UzTicketsParser\Command\Parser;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Parser;

class SuggestStation extends Command
{
    protected static $defaultName = 'parser:suggest-station';

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
                new InputArgument('title', InputArgument::REQUIRED, 'Station title for search'),
            ])
            ->setDescription('Suggest stations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suggestions = [];
        foreach ($this->parser->getStationsSuggestions($input->getArgument('title')) as $suggestion) {
            $suggestions[] = [$suggestion->getCode(), $suggestion->getTitle()];
        }

        $io = new SymfonyStyle($input, $output);
        $io->table(['Code', 'Title'], $suggestions);

        foreach ($this->parser->getErrorMessages() as $message) {
            $io->error($message);
        }
    }

    //########################################
}
