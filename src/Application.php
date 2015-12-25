<?php 
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2015 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

use Exception;
use RuntimeException;

use Closure;

use Orinoco\Controller;
use Orinoco\Configuration;
use Orinoco\Autoload;
use Orinoco\Container;

use Orinoco\Http;
use Orinoco\Route;
use Orinoco\View;
use Orinoco\ExceptionHandler;

/**
 * The framework.
 */
class Application extends Controller
{
    /**
     * @var Orinoco\Configuration
     */
    protected $config;
    
    /**
     * @var Orinoco\Container
     */
    protected $container;

    /**
     * @var Orinoco\Http
     */
    protected $http;

    /**
     * @var Orinoco\Route
     */
    protected $route;

    /**
     * @var Orinoco\ExceptionHandler
     */
    protected $exception;

    /**
     * @var Orinoco\View
     */
    protected $view;

    /**
     * Constructor.
     *
     * @param Orinoco\Configuration $config
     *
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->container = new Container();
        $this->autoload = new Autoload();

        // Register framework's classes (for dependency injection)
        $this->http = $this->container->register(new Http($_SERVER));
        $this->route = $this->container->register(new Route($this->http, $this->container));
        $this->view = $this->container->register(new View($this->config, $this->http, $this->route));
        $this->exception = $this->container->register(new ExceptionHandler($this));
    }

    /**
     * Runner.
     */
    public function run()
    {
        // Session
        if ($this->config->application['session'] && (session_id() === "")) {
            session_start();
        }

        // Internal Autoload mechanism
        if (isset($this->config->application['autoload'])) {
            foreach ($this->config->application['autoload'] as $path) {
                $relativePath = $this->config->application['base'] . DIRECTORY_SEPARATOR . ltrim(rtrim($path, '/'), '/');
                $absolutePath = realpath($relativePath);
                // Register only if path exists
                if (file_exists($absolutePath)) {
                    $this->autoload->register($absolutePath);
                }
            }
        }

        // Needed for 'ClosureContext' visibility
        $this->autoload->register(dirname(__FILE__) . '/Closure/');

        // Register Application instance
        // So it can be injected within the user's controller
        $this->container->register($this);

        // Prepare routes
        foreach ($this->config->route as $expression => $route) {
            $this->route->set($expression, $route);
        }

        // Parse request, the actual route (URI) parsing process
        if ($this->route->parseRequest()) {
            $this->dispatch();
        } else {
            if (!$this->config->application['production']) {
                $this->http->header($this->http->getValue('SERVER_PROTOCOL') . ' 404 Not Found', true, 404);
                throw new Exception('Route Not Found');
            } else {
                $this->view->layout('error')->page('error.404')->render();
            }
        }

    }

    /**
     * Interface for Orinoco\Configuration.
     */
    public function Config()
    {
        return $this->config;
    }

    /**
     * Interface for Orinoco\Autoload.
     */
    public function Autoload()
    {
        return $this->autoload;
    }

    /**
     * Interface for Orinoco\Container (DI).
     */
    public function Container()
    {
        return $this->container;
    }

    /**
     * Interface for Orinoco\Http.
     */
    public function Http()
    {
        return $this->http;
    }

    /**
     * Interface for Orinoco\Route.
     */
    public function Route()
    {
        return $this->route;
    }

    /**
     * Interface for Orinoco\View.
     */
    public function View()
    {
        return $this->view;
    }

    /**
     * Request dispatcher.
     *
     * @return mixed Controller/action response
     *
     */
    private function dispatch()
    {

        if ($this->route->isActionClosure()) {
            $controller = 'ClosureContext';
            $action = 'bind';
        } else {
            $controller = $this->route->controller();
            $controller = $controller . Configuration::CONTROLLER_NAME_SUFFIX;
            $action = $this->route->action();
        }

        if (class_exists($controller, true)) {

            // Load reflection of controller/class
            $this->container->reflectionLoad($controller);

            $withConstructor = false;
            $dependencies = array();

            $constructor = $this->container->reflectionGetConstructor();

            // Check if class has a contructor method (either "__construct" or method that is the same name as its class)
            if (isset($constructor->name)) {
                $dependencies = $this->container->reflectionGetMethodDependencies($constructor->name);
                if ($constructor->name == '__construct') {
                    // $skipReflection = true;
                    $withConstructor = true;
                }
            }

            // Create class instance (object)
            $obj = $this->container->reflectionCreateInstance($dependencies, $withConstructor);

            // Prepare/set Closure function
            if ($this->route->isActionClosure()) {
                $obj->set($this->route->getClosure());
            }

            // Check if object method exists
            if (method_exists($obj, $action)) {
                // Check if action method needs dependency
                $dependencies = $this->container->reflectionGetMethodDependencies($action);
                // Call the controller's action method
                call_user_func_array(array($obj, $action), $dependencies);
            } else {
                // No action method found!
                if (!$this->config->application['production']) {
                    $this->http->header($this->http->getValue('SERVER_PROTOCOL') . ' 404 Not Found', true, 404);
                    throw new Exception('Cannot find method "' . $action . '" in controller class "' . $controller . '"');
                } else {
                    $this->view->layout('error')->page('error.404')->render();
                }
            }
        } else {
            // No controller class found!
            if (!$this->config->application['production']) {
                $this->http->header($this->http->getValue('SERVER_PROTOCOL') . ' 404 Not Found', true, 404);
                throw new Exception('Cannot find controller class "' . $controller . '"');
            } else {
                $this->view->layout('error')->page('error.404')->render();
            }
        }
    }

    /**
     * End application.
     *
     * @param integer $returnCode Return code
     *
     */
    public function end($returnCode = 0)
    {
        exit($returnCode);
    }
}
