<?php

$array = [
    0 => 'VALUE key 0 10 6221',
    1 => 'key stored',
    2 => 'VALUE key1 0 11 6222',
    3 => 'key1 stored',
    4 => 'VALUE key2 0 11 6223',
    5 => 'key2 stored',
    6 => 'END'
];

foreach(yielding($array) as $ars) {
    echo $ars . "\n";
}

function yielding($array) {
    foreach($array as $ar) {
        yield $ar;
    }
}