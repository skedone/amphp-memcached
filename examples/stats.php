<?php
declare(ticks=1);
require __DIR__ . '/../vendor/autoload.php';
$i = 0;
// \Amp\reactor(new \Amp\NativeReactor());
echo get_class(\Amp\reactor()) . "\n";

$c = 10000;
$values = array();
for ($i=0;$i<$c;$i++) $values[sprintf('%020s',$i)]=sha1($i);

\Amp\reactor(new \Amp\NativeReactor());
\Amp\run(function() use (&$i, $values) {

    $memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');
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

if(extension_loaded('memcached')) {

    $m = new Memcached();
    $m->addServer('127.0.0.1', 11211);

    $start = microtime(true);
    foreach ($values as $k => $v) $m->set($k, $v, 3600);
    $time = microtime(true)-$start;
    echo "memcached set: $time\n";
    $start = microtime(true);
    foreach ($values as $k => $v) $m->get($k);
    $time = microtime(true)-$start;
    echo "memcached get: $time\n";

}

echo memory_get_usage(true) / 1024 . " Mb \n";
echo memory_get_peak_usage(true) / 1024 . " Mb\n";