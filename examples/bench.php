<?php

require __DIR__ . '/../vendor/autoload.php';

$i = 0;
\Amp\repeat(function () use (&$i) {
    echo "\n{$i} iterations";
    $i = 0;
}, 1000);

\Amp\once(function(){
    \Amp\stop();
}, 5000);

\Amp\once(function(){
    \Amp\stop();
}, 10000);

echo "##### SET OP/S";
\Amp\run(function() use (&$i) {
    $memcached = new \Edo\Memcached();
    $memcached->addServer('tcp://127.0.0.1', 11211);
    while (true) {
        $stats = (yield $memcached->set('key', 'value_key'));
        $i++;
    }
});

echo "\n##### GET OP/S";
\Amp\run(function() use (&$i) {
    $memcached = new \Edo\Memcached();
    $memcached->addServer('tcp://127.0.0.1', 11211);
    while (true) {
        $stats = (yield $memcached->get('key'));
        $i++;
    }
});