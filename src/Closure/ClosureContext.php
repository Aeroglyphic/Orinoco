<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2016 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

use Orinoco\Application;

/**
 * A class that Closure (anonymous function) will bind to.
 */
class ClosureContext
{
    /**
     * @var Closure function store
     */
    private $closure;

    /**
     * Bind to this object and execute closure function.
     *
     * @param Orinoco\Application $app
     * @return object Bounded Closure
     *
     */
    public function bind(Application $app)
    {
        $closure = $this->closure;
        $boundClosure = $closure->bindTo($this);
        return $boundClosure($app);
    }

    /**
     * Store closure function.
     *
     * @param Closure
     *
     */
    public function set($closure)
    {
        $this->closure = $closure;
    }
}
