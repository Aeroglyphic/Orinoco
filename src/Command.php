<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2016 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

use Orinoco\Interfaces\Request;

/**
 * Console command request.
 */
class Command implements Request
{
    /**
     * @var array (request information storage)
     */
    private $request = array();

    /**
     * Set request/command to execute.
     *
     * @param string $request String
     *
     */
    public function setRequest($request)
    {
        $this->request['command'] = $request;
    }

    /**
     * Return a NULL request method as console command don't need $_SERVER['REQUEST_METHOD'].
     *
     * @return null
     *
     */
    public function getRequestMethod() {
        return null;
    }

    /**
     * Return request command (as URI format).
     *
     * @return string 'command' value
     *
     */
    public function getRequestURI() {
        return $this->request['command'];
    }
}
