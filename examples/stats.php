<?php

require __DIR__ . '/../vendor/autoload.php';

$i = 0;
$memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');

\Amp\repeat(function () use (&$i) {
    echo "{$i} iterations\n";
}, 1000);

\Amp\run(function() use (&$i, $memcached) {
    // $memcached->addServer('tcp://127.0.0.1', 11211);
    while (true) {
        $stats = (yield $memcached->getStats());
        $i++;
    }
});