<?php

namespace Edo;

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

    public function __construct()
    {
        $this->parser = new AsciiProtocol();
    }

    public function addConnection($host, $port)
    {
        $connection = new Connection();
        if (strpos($host, "tcp://") !== 0 && strpos($host, "unix://") !== 0) {
            throw new \DomainException("Uri must start with tcp:// or unix://");
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
     * @param string $host
     * @param int $port
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
     * Store value against key
     *
     * @param string $key
     * @param $value
     * @param $expiration
     * @return \Amp\Promise
     * @yield array
     */
    public function set($key, $value, $expiration = 0)
    {
        return $this->send([['set', $key, 0, $expiration, strlen($value)],[$value]]);
    }

    /**
     * Store this value against key if the key does not already exist
     *
     * @param string $key
     * @param $value
     * @param int $expiration
     * @return \Amp\Promise
     * @yield array
     */
    public function add($key, $value, $expiration = 0)
    {
        return $this->send([['add', $key, 0, $expiration, strlen($value)], [$value]]);
    }

    /**
     * Store this value against key if the key already exists
     *
     * @param string $key
     * @param $value
     * @param int $expiration
     * @return \Amp\Promise
     * @yield array
     */
    public function replace($key , $value , $expiration = 0)
    {
        return $this->send([['replace', $key, 0, $expiration, strlen($value)], [$value]]);
    }

    /**
     * Append the supplied value to the end of the value for the specified key.
     * The flags and expiration time arguments should not be used.
     *
     * @param string $key
     * @param $value
     * @return \Amp\Promise
     * @yield array
     */
    public function append($key , $value)
    {
        return $this->send([['append', $key, 0, 0, strlen($value)], [$value]]);
    }

    /**
     * @param string $key
     * @param $value
     * @return \Amp\Promise
     * @yield array
     */
    public function prepend($key , $value)
    {
        return $this->send([['prepend', $key, 0, 0, strlen($value)], [$value]]);
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return \Amp\Promise
     * @yield string|falase
     */
    public function get($key, callable $callback = null)
    {
        return $this->send(['get', $key]);
    }

    /**
     * @param array $keys
     * @param callable $callback
     * @return \Amp\Promise
     */
    public function getMulti(array $keys, callable $callback = null)
    {
        return $this->send(['get', \join(' ', $keys)]);
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return \Amp\Promise
     * @yield array
     */
    public function gets($key, callable $callback = null)
    {
        return $this->send(['gets', $key]);
    }

    /**
     * @param array $keys
     * @param callable $callback
     * @return \Amp\Promise
     * @yield array
     */
    public function getsMulti(array $keys, callable $callback = null)
    {
            return $this->send(['gets', \join(' ', $keys)]);
    }

    /**
     * Delete the key
     *
     * @param string $key
     * @param int $time
     * @return \Amp\Promise
     */
    public function delete($key, $time = 0)
    {
        return $this->send(['delete', $key, $time]);
    }

    /**
     * @param array $keys
     * @return \Amp\Promise
     */
    public function deleteMulti(array $keys)
    {
        $promises = [];
        foreach($keys as $key) {
            $promises[] = $this->send(['delete', $key]);
        }
        return \Amp\all($promises);
    }

    /**
     * The "touch" command is used to update the expiration time of an existing item without fetching it.
     *
     * @param $key
     * @param $expiration
     * @return \Amp\Promise
     */
    public function touch($key, $expiration)
    {
        return $this->send(['touch', $key, $expiration]);
    }

    /**
     * @return \Amp\Promise
     * @yield array
     */
    public function getStats()
    {
        return $this->send(['stats']);
    }

}