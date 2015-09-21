<?php

require __DIR__ . '/../vendor/autoload.php';

$keys = 10000;
$values = [];
for ($i=0;$i<$keys;$i++) $values[sprintf('%020s',$i)]=sha1($i);

\Amp\run(function() use ($values){
    $memcached = new \Edo\Memcached();
    $memcached->addServer('tcp://127.0.0.1', 11211);

    $set = (yield $memcached->set('key_set', 'value_set'));
    echo "##### SET\n";
    var_dump($set);

    $set = (yield $memcached->set('key set', 'value_set'));
    echo "##### SET\n";
    var_dump($set);

    $add = (yield $memcached->add('key_add', 'value_add', 1));
    echo "##### ADD\n";
    var_dump($add);

    $append = (yield $memcached->append('key_set', '_append'));
    echo "##### APPEND\n";
    var_dump($append);

    $prepend = (yield $memcached->prepend('key_set', 'prepend_'));
    echo "##### PREPEND\n";
    var_dump($prepend);

    $get = (yield $memcached->get('key_set'));
    echo "##### GET\n";
    var_dump($get);
});