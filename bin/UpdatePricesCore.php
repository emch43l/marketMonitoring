<?php

namespace UpdatePrices;

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Lock\LockFactory;
// use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Lock\Store\FlockStore;

// $redis = new \Redis();
// $redis->connect('192.168.136.129');
$store = new FlockStore();
$factory = new LockFactory($store);

$lock = $factory->createLock('second-lock',30);

if($lock->acquire())
{
    $i = 0;
    while($i < 11)
    {
        echo "working";
        sleep(1);
        $i++;
    }
}

$lock->release();
