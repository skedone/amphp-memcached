<?php

namespace Edo;

use Amp\Socket\ConnectException;
use Edo\Protocol\AsciiProtocol;

/**
 * Class Memcached
 * @package Edo
 */
class Memcached
{

    /** @var array */
    private $promisors;

    /** @var array */
    private $connections = [];

    public function __construct($parser = null)
    {
        if($parser === null) {
            $this->parser = new AsciiProtocol();
        }
    }

    public function addConnection($host, $port)
    {
        $connection = new Connection();
        if (strpos($host, "tcp://") !== 0 && strpos($host, "unix://") !== 0) {
            throw new \DomainException("Host must start with tcp:// or unix://");
        }
        $connection->setUri("$host:$port");
        $connection->addEventHandler("response", function ($response) {
            $promisor = array_shift($this->promisors);
            if ($response instanceof \Exception) {
                $promisor->fail($response);
            } else {
                $promisor->succeed($response);
            }
        });

        $this->connections[] = $connection;
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach($this->connections as $connection) {
            unset($connection);
        }
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connections[array_rand($this->connections)];
    }

    /**
     * @param array $args
     * @param callable $transform
     * @return \Amp\Promise
     */
    public function send(array $args, callable $transform = null)
    {
        $promisor = new \Amp\Deferred();
        $this->promisors[] = $promisor;
        $this->getConnection()->send($args);
        return \Amp\pipe($promisor->promise(), function($response) {
            return $this->parser->parse($response);
        });
    }


    /**
     * @param $host
     * @param $port
     * @param int $weight
     * @return void
     */
    public function addServer($host, $port, $weight = 0)
    {
        $this->addConnection($host, $port);
    }

    /**
     * @param array $servers
     * @return void
     */
    public function addServers(array $servers)
    {
        foreach ($servers as $server) {
            $this->addServer($server['host'], $server['port'], ($server['weight'] ?: 0) );
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @return \Amp\Promise
     */
    public function set($key, $value, $expire = 0)
    {
        return $this->send([['set', $key, 0, $expire, strlen($value)],[$value]]);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expiration
     * @return \Amp\Promise
     */
    public function add($key, $value, $expiration = 0)
    {
        return $this->send([['add', $key, 0, $expiration, strlen($value)], [$value]]);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expiration
     * @return \Amp\Promise
     */
    public function replace($key , $value , $expiration = 0)
    {
        return $this->send([['replace', $key, 0, $expiration, strlen($value)], [$value]]);
    }

    /**
     * @param $key
     * @param $value
     * @return \Amp\Promise
     */
    public function append($key , $value)
    {
        return $this->send([['append', $key, 0, 0, strlen($value)], [$value]]);
    }

    /**
     * @param $key
     * @param $value
     * @return \Amp\Promise
     */
    public function prepend($key , $value)
    {
        return $this->send([['prepend', $key, 0, 0, strlen($value)], [$value]]);
    }

    public function get($key)
    {
        return $this->send(['get', $key]);
    }

    public function getStats()
    {
        return $this->send(['stats']);
    }

}