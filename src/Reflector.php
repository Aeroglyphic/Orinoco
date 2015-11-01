<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2015 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

/**
 * Framework's reflection implementation.
 */
class Reflector
{
    /**
     * @var ReflectionClass object
     */
    private $reflection;

    /**
     * @var string
     */
    private $class;

    /**
     * Create an instance of \ReflectionClass, to get informations about a specific class.
     *
     * @param string $class Class name
     *
     */
    public function reflectionLoad($class)
    {
        $this->class = $class;
        $this->reflection = new \ReflectionClass($class);
    }

    /**
     * Get constructor name.
     *
     * @return string Class's constructor name
     *
     */
    public function reflectionGetConstructor()
    {
        return $this->reflection->getConstructor();
    }

    /**
     * Get the class method's number of parameters.
     *
     * @param string $methodName Class's method name
     * @return integer Number of parameters
     *
     */
    private function reflectionGetParameterCount($methodName)
    {
        return $this->reflection->getMethod($methodName)->getNumberOfParameters();
    }

    /**
     * Create an instance of \ReflectionParameter.
     *
     * @param string $methodName Class's method name
     * @param integer $index Parameter index
     * @return object ReflectionParameter's object
     *
     */
    private function reflectionParameterInfo($methodName, $index)
    {
        return new \ReflectionParameter(array($this->class, $methodName), $index);
    }

    /**
     * Get the class method's parameters (dependencies).
     *
     * @param string $methodName
     * @return array Parameters (dependencies)
     *
     */
    public function reflectionGetMethodDependencies($methodName)
    {
        $count = $this->reflectionGetParameterCount($methodName);
        if ($count === 0) {
            return array();
        } else {
            $count--;
            $dependencies = array();
            for ($c = 0; $c <= $count; $c++) {
                $info = $this->reflectionParameterInfo($methodName, $c);
                // Check if dependency is a class object or part of URL segment
                if (isset($info->getClass()->name)) {
                    $dependency = $info->getClass()->name;
                } else {
                    $dependency = $info->name;
                }

                // Get required dependency from dependency injection container (Orinoco\Container)
                if ($d = $this->resolve($dependency)) {
                    $dependencies[] = $d;
                } else {
                    // Pass a NULL value, if we're not able to resolve the required dependency
                    $dependencies[] = null;
                }
            }
            return $dependencies;
        }
    }

    /**
     * Create an instance w/ or w/o arguments/parameters.
     *
     * @param array $arguments Parameters/arguments (dependencies)
     * @param boolean $withConstructor Create instance w/ or w/o __construct being executed
     * @return object Instance object of the class
     *
     */
    public function reflectionCreateInstance($arguments = array(), $withConstructor = true)
    {
        if ($withConstructor) {
            // Create new instance w/ __construct executed
            return $this->reflection->newInstanceArgs($arguments);
        } else {
            // Create new instance w/o __construct being executed
            return $this->reflection->newInstanceWithoutConstructor($arguments);
        }
    }
}
