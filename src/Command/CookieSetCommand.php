<?php

namespace Raisaev\UzTicketsParser\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Raisaev\UzTicketsParser\Parser;

class CookieSetCommand extends Command
{
    protected static $defaultName = 'cookie:set';

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
        $this
            ->setDefinition([
                new InputArgument('cookie', InputArgument::REQUIRED, 'The cookie value. Example: _gv_lang=ru; HTTPSERVERID=server4;'),
                new InputArgument('lifetime', InputArgument::OPTIONAL, 'The cookie lifetime'),
            ])
            ->setDescription('Set Authorization Cookie');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cookieValue = [];
        $rawValue = explode('; ', $input->getArgument('cookie'));
        foreach ($rawValue as $rawValuePart) {
            list($key, $value)= explode('=', $rawValuePart);
            $cookieValue[$key] = $value;
        }

        $cookies = $this->cache->getItem(Parser::REQUEST_COOKIES_STORAGE_KEY);
        $cookies->set($cookieValue);
        $cookies->expiresAfter($input->getArgument('lifetime') ? (int)$input->getArgument('lifetime') : (60 * 60 * 24));

        $this->cache->save($cookies);

        $io = new SymfonyStyle($input, $output);
        $io->success('Done');
    }

    //########################################
}
