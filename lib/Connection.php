<?php

namespace Edo;


use Amp\Promise;
use Amp\Promisor;
use Amp\Socket\ConnectException;
use Amp\Success;
use Edo\Protocol\AsciiProtocol;

class Connection {

    /**
     * Set the state of the connection.
     */
    const STATE_DISCONNECTED = 0;
    const STATE_CONNECTING = 1;
    const STATE_CONNECTED = 2;

    /** @var string */
    private $uri;

    /** @var array */
    private $handlers;

    /** @var Promisor */
    private $promisor;

    /** @var string */
    private $writer;

    /** @var string */
    private $reader;

    /** @var resource */
    public $socket;

    /**
     * @param $uri string
     */
    public function __construct()
    {
        $this->outputBuffer = '';
        $this->outputBufferLength = 0;
        $this->state = self::STATE_DISCONNECTED;

        /**
         * Declare the handlers.
         */
        $this->handlers = [ "connect" => [], "response" => [], "error" => [], "close" => [] ];

        /**
         * Parser protocol, can be binary or text right now.
         */
        $this->parser = new AsciiProtocol(function ($response) {
            foreach ($this->handlers["response"] as $handler) {
                $handler($response);
            }
        });
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach($this->handlers as $handlers) {
            unset($handlers);
        }

        \Amp\disable($this->writer);
        \Amp\disable($this->reader);

        $this->parser = null;
    }

    /**
     * @param $uri
     * @return void
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Simple event handler.
     *
     * @param $event
     * @param callable $callback
     */
    public function addEventHandler($event, callable $callback) {
        $events = (array) $event;
        foreach ($events as $event) {
            if (!isset($this->handlers[$event])) {
                throw new \DomainException("Unknown event: " . $event);
            }
            $this->handlers[$event][] = $callback;
        }
    }

    /**
     * @param array $strings
     * @return Promise
     */
    public function send(array $strings) {
        return \Amp\pipe($this->connect(), function () use ($strings) {
            $payload = $this->parsePayload($strings);
            $this->outputBuffer .= $payload;
            $this->outputBufferLength += strlen($payload);
            if($this->reader !== null) {
                \Amp\enable($this->reader);
            }
            if ($this->writer !== null) {
                \Amp\enable($this->writer);
            }
        });
    }

    /**
     * @param array $strings
     * @return string
     */
    public function parsePayload(array $strings)
    {
        if(!is_array($strings[0])) {
            return join(' ', $strings) . "\r\n";
        }

        $payload = '';
        foreach($strings as $string) {
            $payload .= join(' ', $string) . "\r\n";
        }

        return $payload;
    }

    /**
     * @return Promise
     */
    private function connect()
    {
        if($this->promisor) {
            return $this->promisor->promise();
        }

        // Already connected
        if(is_resource($this->socket)) {
            return new Success();
        }

        $this->promisor = new \Amp\Deferred();

        /** @var $socketPromise Promise */
        $socketPromise = \Amp\Socket\connect($this->uri, ['timeout' => 1000]);
        $socketPromise->when(function ($error, $socket) {
            $promisor = $this->promisor;
            $this->promisor = null;

            $this->socket = $socket;
            $this->reader = \Amp\onReadable($this->socket, [$this, "onRead"]);
            $this->writer = \Amp\onWritable($this->socket, [$this, "onWrite"]);

            $promisor->succeed();
        });

        return $this->promisor->promise();
    }

    /**
     * @param $watcherId
     */
    public function onRead($watcherId)
    {
        $read = fread($this->socket, 1048576);
        if ($read != "") {
            $this->parser->append($read);
        } elseif (!is_resource($this->socket) || @feof($this->socket)) {
            $this->state = self::STATE_DISCONNECTED;
            throw new ConnectException("Connection went away (read)", $code = 2);
        }
    }

    /**
     * @param $watcherId
     */
    public function onWrite($watcherId)
    {
        if ($this->outputBufferLength === 0) {
            \Amp\disable($watcherId);
            return;
        }
        $bytes = fwrite($this->socket, $this->outputBuffer);
        if ($bytes === 0) {
            $this->state = self::STATE_DISCONNECTED;
            throw new ConnectException("Connection went away (write)", $code = 1);
        } else {
            $this->outputBuffer = (string) substr($this->outputBuffer, $bytes);
            $this->outputBufferLength -= $bytes;
        }
    }


}