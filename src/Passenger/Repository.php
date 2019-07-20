<?php

namespace Raisaev\UzTicketsParser\Passenger;

use Raisaev\UzTicketsParser\Entity\Passenger;

class Repository
{
    const STORAGE_KEY = 'passengers-storage';

    /** @var \Symfony\Component\Cache\Adapter\FilesystemAdapter */
    private $cache;

    //########################################

    public function __construct(
        \Symfony\Component\Cache\Adapter\FilesystemAdapter $cache
    ){
        $this->cache = $cache;
    }

    //########################################

    /**
     * @param null|int $id
     * @return Passenger|Passenger[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($id = null)
    {
        $pass = $this->cache->getItem(self::STORAGE_KEY);
        $data = $pass->isHit() ? $pass->get() : [];

        return $id === null ? $data : (isset($data[$id]) ? $data[$id] : null);
    }

    /**
     * @param Passenger $pass
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function save(Passenger $pass)
    {
        $data = $this->get();

        if (isset($data[$this->getHash($pass)])) {
            return false;
        }

        $data[$this->getHash($pass)] = $pass;

        $storage = $this->cache->getItem(self::STORAGE_KEY);
        $storage->expiresAfter(60 * 60 * 24 * 360);
        $storage->set($data);

        $this->cache->save($storage);
        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function remove($id)
    {
        $data = $this->get();

        if (!isset($data[$id])) {
            return false;
        }

        unset($data[$id]);

        $storage = $this->cache->getItem(self::STORAGE_KEY);
        $storage->expiresAfter(60 * 60 * 24 * 360);
        $storage->set($data);

        $this->cache->save($storage);
        return true;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $this->cache->deleteItem(self::STORAGE_KEY);
        return true;
    }

    //########################################

    protected function getHash(Passenger $pass)
    {
        return $pass->getEmail();
    }

    //########################################
}