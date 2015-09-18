<?php

namespace Edo;


class TextParser {

    /** @var string */
    private $buffer;

    public function __construct(callable $responseCallback) {
        $this->responseCallback = $responseCallback;
    }

    public function append($string)
    {
        $this->buffer .= $string;
        $cb = $this->responseCallback;
        $cb($this->buffer);
    }
}