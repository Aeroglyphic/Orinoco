<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2015 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace Orinoco;

use Orinoco\Configuration;

/**
 * Framework's internal autoload mechanism.
 */
class AutoLoad
{
    /**
     * Register an anonymous (autoload) function.
     *
     * @param string $dirName Directory/folder name
     *
     */
    public static function register($dirName)
    {
        // Prepare directory/folder name
        $dirName = rtrim($dirName, '/') . '/';
        
        // Register an anonymous (autoload) function
        spl_autoload_register(function ($className) use ($dirName)
            {
                $className = ltrim($className, '\\');
                $fileName  = '';
                $namespace = '';
                if ($lastNamespacePos = strrpos($className, '\\')) {
                    $namespace = substr($className, 0, $lastNamespacePos);
                    $className = substr($className, $lastNamespacePos + 1);
                    $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
                }
                $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . Configuration::PHP_FILE_EXTENSION;
                $filePath = $dirName . $fileName;
                if (file_exists($filePath)) {
                    require_once $filePath;
                }
            }
        );
    }
}
