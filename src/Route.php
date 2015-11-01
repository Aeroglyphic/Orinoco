<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2015 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

use Closure;

use Orinoco\Configuration;
use Orinoco\Container;

/**
 * Framework's route mechanism
 */
class Route
{
    /**
     * @var array (Route map)
     */
    public $routeTable = array();

    /**
     * @var string (Method used for request, e.g. GET, POST, etc)
     */
    public $requestMethod;

    /**
     * @var string (Raw URI request, e.g. /foo/bar?id=123)
     */
    public $requestUri;

    /**
     * @var array (Parsed URL components)
     */
    public $components = array();

    /**
     * @var array (The actual controller and action)
     */
    public $requestMap = array();

    /**
     * @var string
     */
    public $controller;

    /**
     * @var string
     */
    public $action;

    /**
     * @var string (Controller's class path)
     */
    public $path;

    /**
     * @var array (URI segments storage, e.g. /foo/:name/:id)
     */
    public $segments = array();

    /**
     * @var object (Orinoco\Container object)
     */
    private $container;

    /**
     * @var boolean (If current matched route rule is a Closure)
     */
    private $isActionClosure = false;

    /**
     * @var Closure container
     */
    private $closure;

    /**
     * Constructor.
     *
     * @param object $http Orinoco\Http
     * @param object $container Orinoco\Container
     *
     */
    public function __construct(Http $http, Container $container)
    {
        $this->requestMethod = $http->getRequestMethod();
        $this->requestUri = $http->getRequestURI();
        $this->container = $container;
    }

    /**
     * Set/add properties to route table (map).
     *
     * @param string $uri String or Regular expression
     * @param mixed $handler Controler.Action handler (array or string)
     * @param array $routeParams Route parameters/properties
     * @return boolean
     *
     */
    public function set($uri = null, $handler = null, $routeParams = array())
    {
        if (!isset($uri) || !isset($handler)) {
            return false;
        }

        // Check if passed handler is a Closure
        if ($handler instanceof Closure) {
            $this->routeTable[trim($uri)] = $handler;
            return true;
        }

        // Set default handle
        $handle = array(
            'controller' => Configuration::SELF_CONTROLLER,
            'action' => Configuration::DEFAULT_ACTION
        );

        // Check if passed parameter is an array
        if (is_array($handler)) {
            if (isset($handler['controller'])) {
                $handle['controller'] = $handler['controller'];
            }
            if (isset($handler['action'])) {
                $handle['action'] = $handler['action'];
            }
        // Else, string
        } else if (is_string($handler)) {
            $exploded = explode('.', $handler);
            if (count($exploded) > 1) {
                if (isset($exploded[0])) {
                    $handle['controller'] = $exploded[0];
                }
                if (isset($exploded[1])) {
                    $handle['action'] = $exploded[1];
                }
            } else {
                $handle['controller'] = $handler;
            }
        }

        $routeProperties = array_merge($handle, $routeParams);
        
        $this->routeTable[trim($uri)] = $routeProperties;
    }

    /**
     * Get the route table (map).
     *
     * @return array Route table (map)
     *
     */
    public function getRouteTable()
    {
        return $this->routeTable;
    }

    /**
     * Closure checker, if action is Closure.
     *
     * @return boolean
     *
     */
    public function isActionClosure()
    {
        return $this->isActionClosure;
    }

    /**
     * Get Closure function.
     *
     * @return Closure
     *
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Parse request URI.
     *
     * @return mixed Bind closure or boolean (whether or not we got a matching route)
     *
     */
    public function parseRequest()
    {
        $this->components = parse_url($this->requestUri);
        $this->requestMap = preg_split("/\//", $this->components['path'], 0, PREG_SPLIT_NO_EMPTY);
        if ($match = $this->matchRouteRule($this->components['path'])) {
            if ($match instanceof Closure) {
                $this->isActionClosure = true;
                $this->closure = $match;
                return true;
            }
            if (isset($match["controller"])) {
                $this->controller = ($match["controller"] === Configuration::SELF_CONTROLLER) ? $this->requestMap[0] : $match["controller"];
            } else {
                $this->controller = $this->requestMap[0];
            }

            if (isset($match["action"])) {
                $this->action = ($match["action"] === Configuration::SELF_ACTION) ? $this->requestMap[1] : $match["action"];
            } else {
                $this->action = $this->requestMap[1];
            }

            if (isset($match["path"])) {
                $this->path = $match["path"];
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the controller name.
     *
     * @return mixed Boolean or string
     *
     */
    public function controller()
    {
        return isset($this->controller) ? $this->controller : false;
    }

    /**
     * Get the action name.
     *
     * @return mixed Boolean or string
     *
     */
    public function action()
    {
        return isset($this->action) ? $this->action : false;
    }

    /**
     * Check if controller class path is defined/set.
     *
     * @return mixed Boolean or string
     *
     */
    public function isPathDefined()
    {
        if (isset($this->path)) {
            // Make sure there's no forward slashes in the front (beginning) of the path string
            return ltrim($this->path, "/");
        }
        return false;
    }

    /**
     * Match request (based on the route table/rule).
     *
     * @return mixed Boolean or array
     *
     */
    private function matchRouteRule($subject)
    {
        foreach($this->routeTable as $k => $v) {

            // Check if 'method' is defined in the route rule
            if (!($v instanceof Closure) && isset($v['method'])) {
                $checkMethodValue = preg_grep('/^' . $this->requestMethod . '$/i', $v['method']);
                $checkMethodKey = array_intersect_key($v['method'], array_flip(preg_grep('/^' . $this->requestMethod . '$/i', array_keys($v['method']), 0)));
                if (empty($checkMethodValue) && empty($checkMethodKey)) {
                    continue;
                } else {
                    if (isset($checkMethodKey)) {
                        // Create a secondary mapping with uppercased key name
                        $checkMethodKeyUppercased = array();
                        foreach ($checkMethodKey as $key => $val) {
                            $checkMethodKeyUppercased[strtoupper($key)] = $val;
                        }
                        // Check if we need to override 'action' (response)
                        if (array_key_exists($this->requestMethod, $checkMethodKeyUppercased)) {
                            $v['action'] = $checkMethodKeyUppercased[$this->requestMethod];
                        }
                    }
                }
            }

            $segments = array();
            if (!($v instanceof Closure) && isset($v['segment'])) {
                $segments = $v['segment'];
            }
            $callback = function($matches) use ($segments) {
                if (isset($matches[1]) && isset($segments[$matches[1]])) {
                    return $segments[$matches[1]];
                }
                return '(\w+)';
            };

            $pattern = "@^" . preg_replace_callback("/:(\w+)/", $callback, $k) . "$@i";
            $matches = array();
            if (preg_match($pattern, $subject, $matches)) {
                if(strpos($k, ':') !== false) {
                    if (preg_match_all("/:(\w+)/", $k, $segmentKeys)) {
                        array_shift($matches);
                        array_shift($segmentKeys);
                        foreach ($segmentKeys[0] as $key => $name) {
                            $this->segments[$name] = $matches[$key];
                        }
                        // Register segments on Container (for dependency injection)
                        if (!empty($this->segments)) {
                            $this->container->register($this->segments);
                        }
                    }
                }
                return $v;
            }

        }
        return false;
    }

    /**
     * Get specific segment's value.
     *
     * @param string $name
     * @return mixed Boolean or segment value
     *
     */
    public function segment($name)
    {
        if (isset($this->segments[$name])) {
            return $this->segments[$name];
        }
        return false;
    }    
}
