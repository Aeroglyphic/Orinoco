<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2015 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

use Exception;
use ErrorException;

use Orinoco\Console;

/**
 * Framework's custom exception handler for console runner.
 */
class ExceptionHandlerConsole
{
    /**
     * @var Orinoco\Console
     */
    private $console;

    /**
     * Constructor.
     *
     * @param object $console Orinoco\Console
     *
     */
    public function __construct(Console $console)
    {
        $this->console = $console;
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
        $errorMessage = "Error: " . $exception->getMessage() . "\n
                        File: " . $exception->getFile() . "\n
                        Line: " . $exception->getLine() . "\n
                        Backtrace: " . $exception->getTraceAsString() . "\n";

        print($errorMessage);

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
