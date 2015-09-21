<?php

namespace Edo;

class TextParser {

    const OK = 'OK';

    const VALUE = 'VALUE';

    const DELETED = 'DELETED';

    const OK_STORE = 'STORED';
    const KO_STORE = 'NOT STORED';

    const ERROR_CLIENT = 'CLIENT ERROR';
    const ERROR_SERVER = 'SERVER ERROR';
    const ERROR = 'ERROR';


    public function parse($string)
    {
        $strings = explode(' ', substr( $string, 0, strlen( $string ) - 2 ));
        switch($strings[0]) {

            case self::OK;
            case self::DELETED;
            case self::OK_STORE:
                return true;
                break;
            case self::VALUE:
                $values = \explode("\r\n", $strings[3]);
                return $values[1];
                break;
            case self::ERROR_CLIENT:
                return false;
                break;

            default:
                return false;
                break;
        }
    }
}