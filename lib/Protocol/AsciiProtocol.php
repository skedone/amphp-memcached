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
    const TOUCHED = 'TOUCHED';


    /** @var string */
    private $buffer;

    /**
     * @param callable $responseCallback
     */
    public function __construct(callable $responseCallback = null) {
        if($responseCallback !== null) {
            $this->responseCallback = $responseCallback;
        }
    }

    /**
     * @param $string
     * @return string|array|bool
     */
    public function parse($string)
    {
        $string = explode(" ", substr($string, 0, strlen($string) - 2 ));
        // print_r($string);
        switch($string[0]) {
            case self::STORE_OK;
            case self::TOUCHED;
            case self::DELETED:
                return true;
                break;
            case self::STORE_KO;
            case self::NOT_FOUND:
                return false;
                break;
            case self::STAT:
                return $string;
                break;
            case self::VALUE:
                $cas = empty($string[4]) ? false : true;
                $values = $cas ? explode("\r\n", $string[4]) : explode("\r\n", $string[3]);
                return (isset($values[1]) ? ($cas ? [$values[1], $values[0]] : $values[1]) : false);
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