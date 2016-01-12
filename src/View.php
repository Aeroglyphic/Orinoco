<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2016 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

use RuntimeException;

use Orinoco\Http;
use Orinoco\Configuration;
use Orinoco\Route;

/**
 * Framework's simple View implementation
 */
class View
{
    /**
     * @var string Layout to use
     */
    private $useLayout;

    /**
     * @var Orinoco\Http
     */
    private $http;

    /**
     * @var Orinoco\Configuration
     */
    private $config;

    /**
     * @var Orinoco\Route
     */
    private $route;

    /**
     * @var string View base directory
     */
    private $viewBase;

    /**
     * @var array Variable storage
     */
    private $variables;

    /**
     * @var string Explicit view page
     */
    private $pageView;

    /**
     * Constructor.
     *
     * @param Orinoco\Application $app
     *
     */
    public function __construct(Configuration $config, Http $http, Route $route)
    {
        $this->config = $config;
        $this->http = $http;
        $this->route = $route;
    }

    /**
     * Getter method.
     *
     * @param mixed Variable's value or boolean
     *
     */
    public function __get($varName)
    {
        if (isset($this->variables[$varName])) {
            return $this->variables[$varName];
        }
        return false;
    }
    
    /**
     * Set HTML layout to use
     *
     * @param string $layoutName
     * @return Orinoco\View
     *
     */
    public function layout($layoutName = null)
    {
        if (!isset($layoutName)) {
            return $this->useLayout;
        }
        $this->useLayout = $layoutName;
        return $this;
    }

    /**
     * Set page/view template to use.
     *     
     * @param mixed $pageView Array or string
     * @return Orinoco\View
     *
     */
    public function page($pageView)
    {
        // Initialize default page view/template
        $page = array(
                'controller' => $this->route->controller(),
                'action' => $this->route->action()
            );
        // Check if passed parameter is an array
        if (is_array($pageView)) {
            if (isset($pageView['controller'])) {
                $page['controller'] = $pageView['controller'];
            }
            if (isset($pageView['action'])) {
                $page['action'] = $pageView['action'];
            }
        // Else, string
        } else if (is_string($pageView)) {
            $exploded = explode('.', $pageView);
            if (count($exploded) > 1) {
                if (isset($exploded[0])) {
                    $page['controller'] = $exploded[0];
                }
                if (isset($exploded[1])) {
                    $page['action'] = $exploded[1];
                }
            } else {
                $page['action'] = $pageView;
            }
        }
        $this->pageView = $page;
        return $this;
    }

    /**
     * Render template/layout
     *     
     * @param array $objVars Variables to be passed to the layout and page template
     *
     */
    public function render($objVars = array())
    {
        // Check if view 'base' (path) configuration is set
        if (!isset($this->config->view['base'])) {
            if (!$this->config->application['production']) {
                throw new RuntimeException('Application view is not configured properly.');
            } else {
                print('Application view is not configured properly.');
            }
        }

        // Get base absolute path
        $this->viewBase = $this->getAbsolutePath($this->config->view['base']);

        // Store variables (to be passed to the layout and page/view template)
        // Accessible via '__get' method
        if (isset($objVars) && is_array($objVars)) {
            $this->variables = $objVars;
        }

        $layoutName = null;
        // Check if layout is defined
        if(isset($this->useLayout)) {
            $layoutFile = $this->viewBase . DIRECTORY_SEPARATOR . trim($this->config->view['template']['layout'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace($this->config->PHP_FILE_EXTENSION, '', $this->useLayout) . $this->config->PHP_FILE_EXTENSION;
            $layoutName = $this->useLayout;
        // Else, use default layout name
        } else {
            $layoutFile = $this->viewBase . DIRECTORY_SEPARATOR . trim($this->config->view['template']['layout'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace($this->config->PHP_FILE_EXTENSION, '', $this->config->view['layout']) . $this->config->PHP_FILE_EXTENSION;
            $layoutName = $this->config->view['layout'];
        }

        if (!file_exists($layoutFile)) {
            $this->http->header($this->http->getValue('SERVER_PROTOCOL') . ' 500 Internal Server Error', true, 500);
            if (!$this->config->application['production']) {
                throw new RuntimeException('Layout "' . $layoutFile . '" not found or does not exists.');
            } else {
                print('Layout "' . $layoutName . '" not found or does not exists.');
            }
        } else {
            include $layoutFile;
        }
    }

    /**
     * Render action (presentation) content.
     *
     * @return boolean Whether or not view/content file exists
     *
     */
    public function renderContent()
    {
        $contentViewName = null;

        // Check if page view is specified or not        
        if (!isset($this->pageView)) {
            $contentView = $this->viewBase . DIRECTORY_SEPARATOR . trim($this->config->view['template']['page'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->route->controller() . DIRECTORY_SEPARATOR . $this->route->action() . $this->config->PHP_FILE_EXTENSION;
            $contentViewName = $this->route->controller() . '.' . $this->route->action();
        } else {
            $contentView = $this->viewBase . DIRECTORY_SEPARATOR . trim($this->config->view['template']['page'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->pageView['controller'] . DIRECTORY_SEPARATOR . $this->pageView['action'] . $this->config->PHP_FILE_EXTENSION;
            $contentViewName = $this->pageView['controller'] . '.' . $this->pageView['action'];
        }

        if(!file_exists($contentView)) {
            if (!$this->config->application['production']) {
                throw new RuntimeException('View "' . $contentView . '" not found or does not exists.');
            } else {
                print('View "' . $contentViewName . '" not found or does not exists.');
            }
        }
        include $contentView;
    }

    /**
     * Render partial (presentation) content.
     *
     * @param string Partial name/path $partialName
     * @return boolean Whether or not partial file exists
     *
     */
    public function renderPartial($partialName)
    {
        $partialViewName = $partialName;
        $partialView = $this->viewBase . DIRECTORY_SEPARATOR . trim($this->config->view['template']['partial'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $partialName . $this->config->PHP_FILE_EXTENSION;
        if(!file_exists($partialView)) {
            if (!$this->config->application['production']) {
                throw new RuntimeException('Partial view "' . $partialView . '" not found or does not exists.');
            } else {
                print('Partial ' . $partialViewName . ' view not found or does not exists.');
            }
        }
        include $partialView;
    }

    /**
     * Construct JSON string (and also set HTTP header as 'application/json').
     *
     * @param array $data Data in array format
     *
     */
    public function renderJSON($data = array())
    {
        $json = json_encode($data);
        $this->http->header(array(
                'Content-Length' => strlen($json),
                'Content-type' => 'application/json;'
            ));
        print($json);
        // Exit application (normally)
        exit(0);
    }

    /**
     * Helper method to convert relative path to absolute path.
     *     
     * @param string File path
     * @return string Absolute path
     *
     */
    private function getAbsolutePath($path)
    {
        $relativePath = $path;
        $absolutePath = realpath($relativePath);
        return $absolutePath;
    }
}
