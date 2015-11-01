<?php

class ViewTest extends PHPUnit_Framework_TestCase
{
    private $mock = array(
            'USER' => 'www-data',
            'HOME' => '/var/www',
            'REQUEST_URI' => '/foo/bar',
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36'
        );

    public function testSetupView()
    {
        $config = new Orinoco\Configuration();
        $container = new Orinoco\Container();
        $http = new Orinoco\Http($this->mock);   
        $route = new Orinoco\Route($http, $container);
        
        $view = new Orinoco\View($config, $http, $route);
        return $view;
    }

    /**
     * @depends testSetupView
     */
    public function testSetLayout(Orinoco\View $view)
    {
        $view->layout('fancy-template');
        $this->assertEquals('fancy-template', $view->layout());
    }
}
