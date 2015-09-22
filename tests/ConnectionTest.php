<?php

class ConnectionTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        \Amp\reactor(new \Amp\NativeReactor());
    }

    /**
     * @expectedException \DomainException
     */
    public function testConnectionSetup()
    {
        $connection = new \Edo\Connection();
        $connection->addEventHandler('not_exists', function(){});
    }

}
