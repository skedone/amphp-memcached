<?php

namespace Edo;

use Amp\Socket\ConnectException;

class Memcached
{

    /** @var array */
    private $promisors;

    public function __construct($uri)
    {
        $this->connection = new Connection($uri);
        $this->connection->addEventHandler("response", function ($response) {
            $promisor = array_shift($this->promisors);
            if ($response instanceof \Exception) {
                $promisor->fail($response);
            } else {
                $promisor->succeed($response);
            }
        });
    }

    public function send(array $args, callable $transform = null)
    {
        $promisor = new \Amp\Deferred();
        $this->promisors[] = $promisor;
        $this->connection->send($args);
        return $transform
            ? \Amp\pipe($promisor->promise(), $transform)
            : $promisor->promise();
    }


    /**
     * @param $host
     * @param $port
     * @param int $weight
     */
    public function addServer($host, $port, $weight = 0)
    {
        $this->servers = "$host:$port";
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

    public function getStats()
    {
        return $this->send(['stats']);
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

    public function get($key)
    {
        return $this->send(['get', $key]);
    }
}