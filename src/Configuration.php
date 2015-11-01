<?php 
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2015 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

/**
 * Configuration container.
 */
class Configuration
{
    /**
     * Constants.
     */
    const PHP_FILE_EXTENSION = '.php';
    const SELF_CONTROLLER = '__SELF__';
    const SELF_ACTION = '__SELF__';
    const CONTROLLER_NAME_SUFFIX = 'Controller';
    const DEFAULT_CONTROLLER = 'index';
    const DEFAULT_ACTION = 'index';
    const DEFAULT_CONFIG = 'Default/Config.php';

    /**
     * @var array (Default route rule)
     */
    private $default_routes = array(
                '(^\/+[a-zA-Z]+\/+[a-zA-Z]([^/]+)/?$)' => array(
                        'controller' => self::SELF_CONTROLLER,
                        'action' => self::SELF_ACTION
                    ),
                '(^\/+[a-zA-Z]([^/]+)/?$)' => array(
                        'controller' => self::SELF_CONTROLLER,
                        'action' => self::DEFAULT_ACTION
                    ),
                '(^\/$)' => array(
                        'controller' => self::DEFAULT_CONTROLLER,
                        'action' => self::DEFAULT_ACTION
                    )
            );

    /**
     * @var array
     */
    private $config = array();

    /**
     * Constructor.
     *
     * @param array $definedConfig User defined configurations
     *
     */
    public function __construct($definedConfig = array())
    {
        $defaultConfigFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::DEFAULT_CONFIG;
        if (file_exists($defaultConfigFile)) {
            $this->config = require $defaultConfigFile;
        }

        if (isset($definedConfig)) {
            $this->config = array_replace_recursive($this->config, $definedConfig);
        }

        // Append default route rules
        foreach ($this->default_routes as $route => $attr) {
            $this->config['route'][$route] = $attr;
        }
    }

    /**
     * Getter.
     *
     * @param string $name Configuration name
     * @return mixed Boolean or config value
     *
     */
    public function __get($name)
    {
        // Try configuration store
        if (isset($this->config[$name])) {
            return $this->config[$name];
        // Else, try configuration constants
        } else {
            $reflection = new \ReflectionClass($this);
            if ($reflection->getConstant($name)) {
                return $reflection->getConstant($name);
            }
        }
        return false;
    }
}
