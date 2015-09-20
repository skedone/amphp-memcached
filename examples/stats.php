<?php

require __DIR__ . '/../vendor/autoload.php';
$i = 0;
$memcached = new \Edo\Memcached('tcp://127.0.0.1:11211');
echo get_class(\Amp\reactor()) . "\n";

\Amp\run(function() use (&$i, $memcached) {

    $c = 10000;
    $values = array();
    for ($i=0;$i<$c;$i++) $values[sprintf('%020s',$i)]=sha1($i);
    $start = microtime(true);
    foreach ($values as $k => $v){
        $stats = (yield $memcached->set($k, $v, 3600));
    }
    $time = microtime(true)-$start;
    echo "memcached set: $time\n";
});