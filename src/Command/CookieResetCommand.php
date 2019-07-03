<?php

namespace Raisaev\UzTicketsParser\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Parser;

class CookieResetCommand extends Command
{
    protected static $defaultName = 'cookie:reset';

    /** @var \Symfony\Component\Cache\Adapter\FilesystemAdapter */
    private $cache;

    //########################################

    public function __construct(
        \Symfony\Component\Cache\Adapter\FilesystemAdapter $cache,
        $name = null
    ){
        $this->cache = $cache;
        parent::__construct($name);
    }

    //########################################

    protected function configure()
    {
        $this->setDescription('Reset Authorization Cookie');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cache->deleteItem(Parser::REQUEST_COOKIES_STORAGE_KEY);

        $io = new SymfonyStyle($input, $output);
        $io->success('Done');
    }

    //########################################
}
