<?php

namespace Edo\Protocol;


class AsciiProtocol {

    const VALUE = 'VALUE';
    const EXISTS = 'EXISTS';
    const NOT_FOUND = 'NOT_FOUND';
    const ERROR = 'ERROR';
    const ERROR_SERVER = 'SERVER_ERROR';
    const ERROR_CLIENT = 'CLIENT_ERROR';
    const STORE_OK = 'STORED';
    const STORE_KO = 'NOT_STORED';
    const DELETED = 'DELETED';
    const STAT = 'STAT';


    /** @var string */
    private $buffer;

    public function __construct(callable $responseCallback = null) {
        if($responseCallback !== null) {
            $this->responseCallback = $responseCallback;
        }
    }

    public function parse($string)
    {
        $string = explode(" ", substr($string, 0, strlen($string) - 2 ));
        switch($string[0]) {
            case self::STORE_OK;
            case self::DELETED:
                return true;
                break;
            case self::STORE_KO:
                return false;
                break;
            case self::STAT:
                return $string;
                break;
            case self::VALUE:
                $values = explode("\r\n", $string[3]);
                return $values[1];
                break;
            case self::ERROR;
            case self::ERROR_CLIENT;
            case self::ERROR_SERVER:
                return false;

        }
    }

    public function append($string)
    {
        $this->buffer = $string;
        $cb = $this->responseCallback;
        $cb($this->buffer);
    }
}