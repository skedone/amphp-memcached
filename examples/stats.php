<?php

require __DIR__ . '/../vendor/autoload.php';
$iterator = 0;

echo get_class(\Amp\reactor()) . "\n";

$operations = 10000;
$values = array();
for ($iterator=0;$iterator<$operations;$iterator++) $values[sprintf('%020s',$iterator)]=sha1($iterator);

\Amp\run(function() use (&$i, $values) {

    $memcached = new \Edo\Memcached();
    $memcached->addServer('tcp://127.0.0.1', 11211);

    $start = microtime(true);
    foreach ($values as $k => $v){
        $stats = (yield $memcached->set($k, $v, 3600));
    }
    $time = microtime(true)-$start;
    echo "amp-memcached set: $time\n";

    $start = microtime(true);
    foreach ($values as $k => $v){
        $stats = (yield $memcached->get($k));
    }
    $time = microtime(true)-$start;
    echo "amp-memcached get: $time\n";

   \Amp\stop();

});

if(extension_loaded('memcache')) {
    $memached = new Memcache();
    $memached->addServer('127.0.0.1', 11211);

    $start = microtime(true);
    foreach ($values as $k => $v) $memached->set($k, $v, 3600);
    $time = microtime(true)-$start;
    echo "memcache set: $time\n";
    $start = microtime(true);
    foreach ($values as $k => $v) $memached->get($k);
    $time = microtime(true)-$start;
    echo "memcache get: $time\n";
}

if(extension_loaded('memcached')) {

    $memached = new Memcached();
    $memached->addServer('127.0.0.1', 11211);

    $start = microtime(true);
    foreach ($values as $k => $v) $memached->set($k, $v, 3600);
    $time = microtime(true)-$start;
    echo "memcached set: $time\n";
    $start = microtime(true);
    foreach ($values as $k => $v) $memached->get($k);
    $time = microtime(true)-$start;
    echo "memcached get: $time\n";

}

$values = [];

echo memory_get_usage(true) / 1024 . " Mb \n";
echo memory_get_peak_usage(true) / 1024 . " Mb\n";