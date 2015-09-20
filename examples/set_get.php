<?php

require __DIR__ . '/../vendor/autoload.php';

$keys = 10000;
$values = [];
for ($i=0;$i<$keys;$i++) $values[sprintf('%020s',$i)]=sha1($i);

\Amp\run(function() use ($values){
    $memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');
    $set = (yield $memcached->set('key1', array_pop($values)));
    $get = (yield $memcached->get('key1'));

    $set1 = (yield $memcached->set('key1', array_pop($values)));
    $get1 = (yield $memcached->get('key1'));

    echo "\n##########################################";
    echo "\nFIRST ITERTION\n";
    echo $set;
    echo $get;

    echo "\n##########################################";
    echo "\nSECOND ITERTION\n";
    echo $set1;
    echo $get1;

});
