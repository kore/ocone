<?php

// Initialize eZ Components
require_once 'ezc/Base/src/base.php';

class oCone_Dispatcher
{
    protected static $configuration;

    protected static function loadConfig()
    {
        self::$configuration = 
    }

    /**
     * Dispatches a user requested URL to the modules defined in the 
     * configuration
     * 
     * @param string $uri 
     * @return void
     */
    public static function dispatch( $uri )
    {
        
    }
}
