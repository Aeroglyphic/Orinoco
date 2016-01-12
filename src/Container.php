<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2016 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

/**
 * Dependency injection container.
 */
class Container extends Reflector
{
    /**
     * @var array
     */
    protected $container = array();

    /**
     * Register (map) object or segments.
     *
     * @param mixed $mixed Object or an array
     * @return mixed Object or boolean
     *
     */
    public function register($mixed)
    {
        if (is_object($mixed)) {
            if (!isset($this->container[get_class($mixed)])) {
                // Store object
                $this->container[get_class($mixed)] = $mixed;
                return $mixed;
            }
        } else if (is_array($mixed)) {
            // Iterate and store key/value pair(s)
            foreach ($mixed as $k => $v) {
                $this->container[$k] = $v;
            }
            return true;
        }
        return false;
    }

    /**
     * Get/resolve registered object or segment (by name).
     *
     * @param string $name
     * @return mixed Object or boolean
     *
     */
    public function resolve($name)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }
        return false;
    }
}
