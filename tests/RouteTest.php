<?php

class RouteTest extends PHPUnit_Framework_TestCase
{
    private $mock = array(
            'USER' => 'www-data',
            'HOME' => '/var/www',
            'REQUEST_URI' => '/foo/123',
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36'
        );

    public function testSetupRoute()
    {
        $http = new Orinoco\Http($this->mock);   
        $container = new Orinoco\Container();
        $route = new Orinoco\Route($http, $container);
        $route->set('/foo/:id', 'foo.bar', array(
                'segment' => array(
                    'id' => '(\d+)'
                    )
            ));
        return $route;
    }

    /**
     * @depends testSetupRoute
     */
    public function testRouteTable(Orinoco\Route $route)
    {
        $routeTable = $route->getRouteTable();
        $this->assertEquals(array(
                '/foo/:id' => array(
                                'controller' => 'foo',
                                'action' => 'bar',
                                'segment' => array(
                                        'id' => '(\d+)'
                                    )
                            )
            ), $routeTable);
        return $route;
    }

    /**
     * @depends testSetupRoute
     */
    public function testParseRequest(Orinoco\Route $route)
    {
        $this->assertEquals(true, $route->parseRequest());
        return $route;
    }

    /**
     * @depends testParseRequest
     */
    public function testGetControllerName(Orinoco\Route $route)
    {
        $this->assertEquals('foo', $route->controller());
    }

    /**
     * @depends testParseRequest
     */
    public function testGetActionName(Orinoco\Route $route)
    {
        $this->assertEquals('bar', $route->action());
    }

    /**
     * @depends testParseRequest
     */
    public function testPathNotDefined(Orinoco\Route $route)
    {
        $this->assertEquals(false, $route->isPathDefined());
    }

    /**
     * @depends testParseRequest
     */
    public function testGetSegment(Orinoco\Route $route)
    {
        $this->assertEquals(123, $route->segment('id'));
    }    
}
