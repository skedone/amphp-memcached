<?php

namespace Edo\Test;

abstract class AbstractCommandTest extends \PHPUnit_Framework_TestCase {

    abstract function testReactor();

    /** @var \Edo\Memcached */
    public $memcached;

    public function testSetFailureCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key set', 'value_set'));
            $this->assertInternalType('bool', $set);
            $this->assertFalse($set);
            \Amp\stop();
        });
    }


    /**
     *
     */
    public function testSetsCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_set', 'value_set'));
            $this->assertInternalType('bool', $set);
            $this->assertTrue($set);
            \Amp\stop();
        });
    }

    /**
     * @depends testSetsCommand
     */
    public function testGetCommand()
    {
        \Amp\run(function(){
            $value = 'value_set';
            $set = (yield $this->memcached->set('key_set', $value));
            $get = (yield $this->memcached->get('key_set'));
            $this->assertInternalType('string', $get);
            $this->assertEquals($value, $get);
            \Amp\stop();
        });
    }

    public function testGetFailureCommand()
    {

    }
}