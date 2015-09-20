<?php

require __DIR__ . '/../vendor/autoload.php';

$i = 0;
$header = "\n#### GET";

echo get_class(\Amp\reactor());

\Amp\repeat(function () use (&$i, &$header) {
    echo "{$header} {$i} iterations";
    $i = 0;
}, 1000);

\Amp\repeat(function () use (&$i) {
    \Amp\stop();
}, 5000);

\Amp\run(function() use (&$i, &$header) {
    $memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');
    $header = "\n#### GET";
    while (true) {
        $stats = (yield $memcached->get('key'));
        $i++;
    }
});


\Amp\run(function() use (&$i, &$header) {
    $memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');
    $header = "\n#### SET";
    while (true) {
        $stats = (yield $memcached->set('key', 'value'));
        $i++;
    }
});


echo memory_get_usage(true) / 1024 / 1024 . " Mb";
echo memory_get_peak_usage(true) / 1024 / 1024 . " Mb";