<?php

class MemcachedTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException \DomainException
     */
    public function testAddConnection()
    {
        $memcached = new \Edo\Memcached();
        $memcached->addServer('file://localhosts', 1);
    }
}