<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2015 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

/**
 * Framework HTTP class.
 */
class Http
{
    /**
     * @var array ($_SERVER information storage)
     */
    private $server;

    /**
     * Constructor.
     *
     * @param array $server $_SERVER variables
     *
     */
    public function __construct($server = null)
    {
        $this->server = $server;
    }

    /**
     * Get 'REQUEST_METHOD' value.
     *
     * @return string 'REQUEST_METHOD' value
     *
     */
    public function getRequestMethod() {
        return $this->server['REQUEST_METHOD'];
    }

    /**
     * Get 'REQUEST_URI' value.
     *
     * @return string 'REQUEST_URI' value
     *
     */
    public function getRequestURI() {
        return $this->server['REQUEST_URI'];
    }

    /**
     * Get server information ($_SERVER variables).
     *
     * @return array $_SERVER variables
     *
     */
    public function getServerInfo() {
        return $this->server;
    }

    /**
     * Get value from $_SERVER array.
     *
     * @param string $name Key/variable name
     * @return mixed Value or boolean
     *
     */
    public function getValue($name) {
        $name = strtoupper($name);
        if (isset($this->server[$name])) {
            return $this->server[$name];
        }
        return false;
    }

    /**
     * Set HTTP header (response).
     *
     * @param mixed $header Array or string
     * @param boolean $replace Whether to replace the value if a specific header attribute
     * @param integer $httpResponseCode HTTP response code
     *
     */
    public function header($header, $replace = true, $httpResponseCode = null)
    {
        /**
         * @todo If $header is null, return current header?
         */
        
        // Check if $header value is an array
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                header($k . ": " . $v, $replace, $httpResponseCode);
            }
        // Else, assume $header value is a string
        } else {
            header($header, $replace, $httpResponseCode);
        }
    }
}
