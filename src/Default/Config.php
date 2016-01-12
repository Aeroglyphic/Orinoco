<?php
/**
 * Orinoco Framework - A lightweight PHP framework.
 *  
 * Copyright (c) 2008-2016 Ryan Yonzon, http://www.ryanyonzon.com/
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

return array(
        'application' => array(
                'production' => false,
                'session' => true,
                'base' => __DIR__,
            ),
        'view' => array(
                'base' => __DIR__ . DIRECTORY_SEPARATOR . 'view',
                'template' => array(
                        'layout' => '/layout',
                        'page' => '/page',
                        'partial' => '/partial',
                        'error' => '/error'
                    ),
                'layout' => 'main',
            ),
    );
