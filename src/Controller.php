<?php 
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2015 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

/**
 * Base controller.
 */
class Controller
{
    /**
     * Redirect using header.
     *
     * @param mixed $mixed String or an array
     * @param boolean $useRefresh Use 'refresh' instead of 'location'
     * @param integer $refreshTime Time to refresh
     *
     */
    public function redirect($mixed, $useRefresh = false, $refreshTime = 3)
    {
        $url = null;
        if (is_string($mixed)) {
            $url = trim($mixed);
        } else if (is_array($mixed)) {
            $controller = $this->route->controller();
            $action = null;
            if (isset($mixed['controller'])) {
                $controller = trim($mixed['controller']);
            }
            $url = '/' . $controller;
            if (isset($mixed['action'])) {
                $action = trim($mixed['action']);
            }
            if (isset($action)) {
                $url .= '/' . $action;
            }
            if (isset($mixed['query'])) {
                $query = '?';
                foreach ($mixed['query'] as $k => $v) {
                    $query .= $k . '=' . urlencode($v) . '&';
                }
                $query[strlen($query) - 1] = '';
                $query = trim($query);
                $url .= $query;
            }
        }
        if (!$useRefresh) {
            $this->http->header('Location: ' . $url);
        } else {
            $this->http->header('refresh:' . $refreshTime . ';url=' . $url);
        }

        // Exit application (normally)
        exit(0);
    }
}
