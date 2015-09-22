<?php

namespace Edo\Protocol;


class AsciiProtocol {

    const VALUE = 'VALUE';
    const EXISTS = 'EXISTS';
    const NOT_FOUND = 'NOT FOUND';
    const ERROR = 'ERROR';
    const ERROR_SERVER = 'SERVER_ERROR';
    const ERROR_CLIENT = 'CLIENT_ERROR';
    const STORE_OK = 'STORED';
    const STORE_KO = 'NOT_STORED';
    const DELETED = 'DELETED';
    const STAT = 'STAT';
    const TOUCHED = 'TOUCHED';
    const END = 'END';


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
        $strings = explode(" ", substr($string, 0, strlen($string) - 2 ));
        switch($strings[0]) {
            case self::STORE_OK;
            case self::TOUCHED;
            case self::DELETED:
                return true;
                break;
            case self::STORE_KO;
            case self::END;
            case self::NOT_FOUND:
                return false;
                break;
            case self::STAT:
                return $this->stats($string);
                break;
            case self::VALUE:
                return $this->value($string);
                break;
            case self::ERROR;
            case self::ERROR_CLIENT;
            case self::ERROR_SERVER:
                return false;

        }

        return false;
    }

    public function value($string)
    {
        $lines = explode("\r\n", substr($string, 0, strlen($string) - 2 ));
        if(isset($lines[3])) {
            // $checkCas = (count(explode(' ', $lines[0])) == 4 ? true : false);

            $response = [];
            $cas = $key = $value = null;
            foreach($lines as $k => $line) {
                if($line == 'END') continue;
                if($k % 2 === 0) {
                    $vals = \explode(' ', $line);
                    $key = $vals[1];
                    continue;
                }

                $response[$key] = $line;
            }
            return $response;
        }

        $strings = explode(" ", substr($string, 0, strlen($string) - 2 ));
        $cas = empty($strings[4]) ? false : true;
        $values = $cas ? explode("\r\n", $strings[4]) : explode("\r\n", $strings[3]);
        return (isset($values[1]) ? ($cas ? [$values[1], $values[0]] : $values[1]) : false);
    }

    public function stats($string)
    {
        $strings = explode("\r\n", substr($string, 0, strlen($string) - 2 ));
        $stats = [];
        foreach($strings as $line) {
            if($line == self::END) continue;
            $stat = \explode(' ', $line);
            $stats[$stat[1]] =  $stat[2];
        }

        return $stats;
    }

    public function append($string)
    {
        $this->buffer = $string;
        $cb = $this->responseCallback;
        $cb($this->buffer);
    }
}