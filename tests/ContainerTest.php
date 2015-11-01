<?php

class ContainerTest extends PHPUnit_Framework_TestCase
{
    private $mock = array(
            'USER' => 'www-data',
            'HOME' => '/var/www',
            'REQUEST_URI' => '/foo/bar',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36'
        );

    public function testSetupContainer()
    {
        $container = new Orinoco\Container();
        return $container;
    }

    /**
     * @depends testSetupContainer
     */
    public function testRegisterClass(Orinoco\Container $container)
    {
        $http = new Orinoco\Http($this->mock);
        $this->assertInstanceOf('Orinoco\Http', $container->register($http));
        return $container;
    }

    /**
     * @depends testRegisterClass
     */
    public function testResolveClass(Orinoco\Container $container)
    {
        $httpObj = $container->resolve('Orinoco\Http');
        $this->assertInstanceOf('Orinoco\Http', $httpObj);
        return $httpObj;
    }

    /**
     * @depends testResolveClass
     */
    public function testResolveHttpGetRequestURI(Orinoco\Http $http)
    {
        $this->assertEquals('/foo/bar', $http->getRequestURI());
    }

    /**
     * @depends testResolveClass
     */
    public function testResolveHttpGetValue(Orinoco\Http $http)
    {
        $this->assertEquals('www-data', $http->getValue('USER'));
        $this->assertEquals('/var/www', $http->getValue('HOME'));
        $this->assertEquals('127.0.0.1', $http->getValue('REMOTE_ADDR'));
    }
}
