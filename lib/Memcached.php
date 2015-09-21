<?php

namespace Edo;

use Amp\Socket\ConnectException;

class Memcached
{

    /** @var array */
    private $promisors;

    private $connections = [];

    public function __construct($parser = null)
    {
        if($parser === null) {
            $this->parser = new TextParser();
        }
    }

    public function addConnection($host, $port)
    {
        $connection = new Connection();
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
     * @return Connection
     */
    public function getConnection()
    {
        $connection = $this->connections[array_rand($this->connections)];
        // var_dump($connection->socket);
        return $connection;
    }

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
     */
    public function addServer($host, $port, $weight = 0)
    {
        $this->addConnection($host, $port);
    }

    /**
     * @param array $servers
     */
    public function addServers(array $servers)
    {
        foreach ($servers as $server) {
            $this->servers[] = $server;
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

    public function add($key, $value, $expiration = 0)
    {
        return $this->send([['add', $key, 0, $expiration, strlen($value)], [$value]]);
    }

    public function replace($key , $value , $expiration = 0)
    {
        return $this->send([['replace', $key, 0, $expiration, strlen($value)], [$value]]);
    }

    public function append($key , $value)
    {
        return $this->send([['append', $key, 0, 0, strlen($value)], [$value]]);
    }

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