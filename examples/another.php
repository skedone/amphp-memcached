<?php

require __DIR__ . '/../vendor/autoload.php';

$i = 0;
\Amp\repeat(function () use (&$i) {
    echo "{$i} iterations\n";
    $i = 0;
}, 1000);

\Amp\run(function() use (&$i) {
    $memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');
    // $memcached->addServer(, 11211);
    while (true) {
        $stats = (yield $memcached->getStats());
        $i++;
    }
});