<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2016 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco\Exceptions;

use Exception;
use ErrorException;

use Orinoco\Application;

/**
 * Framework's custom exception handler.
 */
class ExceptionHandler
{
    /**
     * @var Orinoco\Application
     */
    private $app;

    /**
     * Constructor.
     *
     * @param object $app Orinoco\Application
     *
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->register();
    }

    /**
     * Register exception and error (custom) handlers.
     */
    private function register()
    {
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
        // We might need to register a shutdown handler in the future
        // register_shutdown_function(array($this, 'shutdownHandler'));
    }

    /**
     * Custom error handler.
     *
     * @param integer $errorNumber
     * @param string $errorString
     * @param string $errorFile
     * @param integer $errorLine
     * @param array $errorContext
     *
     */
    public function errorHandler($errorNumber, $errorString, $errorFile = '', $errorLine = 0, $errorContext = array())
    {
        if (!(error_reporting() & $errorNumber)) {
            return;
        }
        throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Custom exception handler.
     *
     * @param Exception $exception
     *
     */
    public function exceptionHandler($exception)
    {
        $errorMessage = 'Error: ' . $exception->getMessage() . '<br />
                        File: ' . $exception->getFile() . '<br />
                        Line: ' . $exception->getLine() . '<br />
                        Backtrace: <pre><code>' . $exception->getTraceAsString() . '</code></pre><br />';

        if (!$this->app->Config()->application['production']) {
            // For client side
            print($errorMessage);
        }

        // For backend (error log)
        throw new Exception($exception->getMessage(), 1);
    }

    /**
     * Custom shutdown handler.
     */
    public function shutdownHandler()
    {
        // Last chance to get errors
        $error = error_get_last();
    }    
}
