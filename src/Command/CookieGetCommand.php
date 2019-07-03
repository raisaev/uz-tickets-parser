<?php

namespace Raisaev\UzTicketsParser\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Raisaev\UzTicketsParser\Parser;

class CookieGetCommand extends Command
{
    protected static $defaultName = 'cookie:get';

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
        $this->setDescription('Get stored Authorization Cookie');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cookies = $this->cache->getItem(Parser::REQUEST_COOKIES_STORAGE_KEY);

        $rawValue = [];
        foreach ($cookies->get() as $key => $value) {
            $rawValue[] = "{$key}={$value}";
        }
        $rawValue = implode('; ', $rawValue);

        $output->writeln($rawValue);
    }

    //########################################
}
