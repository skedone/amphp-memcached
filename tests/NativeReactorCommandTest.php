<?php

namespace Edo\Test;

use Amp\NativeReactor;

class NativeReactorProcessTest extends AbstractCommandTest {

    public function setUp(){
        \Amp\reactor(new NativeReactor());
        $this->memcached = new \Edo\Memcached();
        $this->memcached->addServer('tcp://127.0.0.1', 11211);
    }
    public function testReactor() {
        $this->assertInstanceOf('\Amp\NativeReactor', \Amp\reactor());
    }
}