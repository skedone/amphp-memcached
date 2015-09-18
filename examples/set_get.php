<?php

require __DIR__ . '/../vendor/autoload.php';

\Amp\run(function(){
    $memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');
    $set = (yield $memcached->set('key1', 'valore'));
    $get = (yield $memcached->get('key1'));
    echo $set;
});
