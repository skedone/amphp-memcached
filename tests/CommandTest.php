<?php

namespace Edo\Test;

use Amp\NativeReactor;

class CommandTest extends \PHPUnit_Framework_TestCase {

    public function setUp(){
        \Amp\reactor(new NativeReactor());
        $this->memcached = new \Edo\Memcached();
        $this->memcached->addServer('tcp://127.0.0.1', 11211);
    }

    /** @var \Edo\Memcached */
    public $memcached;

    public function testSetCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_set', 'value_set', 1));
            $this->assertInternalType('bool', $set);
            $this->assertTrue($set);

            $set = (yield $this->memcached->set('key set', 'value_set', 1));
            $this->assertInternalType('bool', $set);
            $this->assertFalse($set);

            \Amp\stop();
        });
    }

    public function testGetCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_set', 'value_set'));
            $get = (yield $this->memcached->get('key_set'));
            $this->assertInternalType('string', $get);
            $this->assertEquals('value_set', $get);

            $get = (yield $this->memcached->get('key_not_exists'));
            $this->assertInternalType('bool', $get);
            $this->assertFalse($get);

            \Amp\stop();
        });
    }

    public function testAddCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->add('key_add', 'value_set', 1));
            $this->assertInternalType('bool', $set);
            $this->assertTrue($set);

            $set = (yield $this->memcached->add('key_add', 'value_set', 1));
            $this->assertInternalType('bool', $set);
            $this->assertFalse($set);

            $set = (yield $this->memcached->add('key add', 'value_set', 1));
            $this->assertInternalType('bool', $set);
            $this->assertFalse($set);

            \Amp\stop();
        });
    }

    public function testReplaceCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_replace', 'value_replace', 1));

            $set = (yield $this->memcached->replace('key_replace', 'value_replace', 1));
            $this->assertInternalType('bool', $set);
            $this->assertTrue($set);

            $set = (yield $this->memcached->replace('key_replace_not_exists', 'value_replace', 1));
            $this->assertInternalType('bool', $set);
            $this->assertFalse($set);

            $set = (yield $this->memcached->replace('key add', 'value_set', 1));
            $this->assertInternalType('bool', $set);
            $this->assertFalse($set);

            \Amp\stop();
        });
    }

    public function testAppendCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_append', 'value_append', 1));

            $set = (yield $this->memcached->append('key_append', '_append', 1));
            $this->assertInternalType('bool', $set);
            $this->assertTrue($set);

            $get = (yield $this->memcached->get('key_append'));
            $this->assertInternalType('string', $get);
            $this->assertEquals('value_append_append', $get);

            \Amp\stop();
        });
    }

    public function testPrependCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_prepend', 'value_prepend', 1));

            $set = (yield $this->memcached->prepend('key_prepend', 'prepend_', 1));
            $this->assertInternalType('bool', $set);
            $this->assertTrue($set);

            $get = (yield $this->memcached->get('key_prepend'));
            $this->assertInternalType('string', $get);
            $this->assertEquals('prepend_value_prepend', $get);

            \Amp\stop();
        });
    }

    public function testGetsCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_gets', 'value_gets', 60));
            $gets = (yield $this->memcached->gets('key_gets'));
            $this->assertInternalType('array', $gets);
            $this->assertArrayHasKey(0, $gets);
            $this->assertArrayHasKey(1, $gets);
            $this->assertEquals('value_gets', $gets[0]);

            \Amp\stop();
        });
    }

    public function testDeleteCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_delete', 'value_delete', 60));
            $delete = (yield $this->memcached->delete('key_delete'));
            $this->assertInternalType('bool', $delete);
            $this->assertTrue($delete);

            $get = (yield $this->memcached->gets('key_delete'));
            $this->assertInternalType('bool', $get);
            $this->assertFalse($get);

            \Amp\stop();
        });
    }

    public function testTouchCommand()
    {
        \Amp\run(function(){
            $set = (yield $this->memcached->set('key_touch', 'value_touch', 0));
            $get = (yield $this->memcached->get('key_touch'));
            $this->assertEquals('value_touch', $get);

            $touch = (yield $this->memcached->touch('key_touch', 1));
            $this->assertInternalType('bool', $touch);
            $this->assertTrue($touch);

            $get = (yield $this->memcached->get('key_touch'));
            $this->assertEquals('value_touch', $get);
            \Amp\once(function(){
                $get = (yield $this->memcached->get('key_touch'));
                $this->assertFalse($get);
                \Amp\stop();
            }, 1000);

        });
    }

}