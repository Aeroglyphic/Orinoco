<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2016 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

/**
 * Console parameter container.
 */
class Param
{
    /**
     * @var array parameter storage
     */
    private $param = array();

    /**
     * Setter.
     *
     * @param string $name (key)
     * @param mixed $value (value)
     *
     */
    public function __set($name, $value)
    {
        $this->param[$name] = $value;
    }

    /**
     * Getter.
     *
     * @param string $name (key)
     * @return boolean
     *
     */
    public function __get($name)
    {
        if (isset($this->param[$name])) {
            return $this->param[$name];
        }
        return false;
    }
}
