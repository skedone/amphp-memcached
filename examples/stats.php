<?php

require __DIR__ . '/../vendor/autoload.php';
$i = 0;
$memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');
echo get_class(\Amp\reactor()) . "\n";
\Amp\repeat(function () use (&$i) {
    echo "{$i} iterations\n";
    if($i > 10000) {
        \Amp\stop();
    }
}, 1000);

\Amp\run(function() use (&$i, $memcached) {
    while (true) {
        $set = (yield $memcached->set('key1', 'valore'));
        $i++;
    }
});