<?php

// Initialize eZ Components
require_once 'ezc/Base/src/base.php';
function __autoload( $class )
{
    ezcBase::autoload( $class );
}

require_once 'exception.php';

require_once 'handler/rst.php';
require_once 'handler/blog.php';
require_once 'handler/404.php';
require_once 'handler/500.php';

class oCone_Dispatcher
{
    /**
     * Stores configuration manager
     * 
     * @var ezcConfigurationManager
     */
    public static $configuration;

    /**
     * Initializes configuration management
     * 
     * @return void
     */
    protected static function loadConfig()
    {
        self::$configuration = ezcConfigurationManager::getInstance();
        self::$configuration->init( 
            'ezcConfigurationIniReader', 
            dirname(  __FILE__ ) . '/../config'
        );
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
        self::loadConfig();

        // Forward to start page
        if ( $uri === '/' )
        {
            $uri = self::$configuration->getSetting( 'site', 'general', 'start' );
        }

        // Find best fitting handler for URI
        $handlers = self::$configuration->getSetting( 'site', 'handler', 'path' );
        $score = null;
        $handler = null;

        foreach ( $handlers as $path => $class )
        {
            if ( strpos( $uri, $path ) === 0 )
            {
                $slashes = substr_count( $path, '/' ) * strlen( $path );
                if ( ( $score === null ) ||
                     ( $slashes > $score ) )
                {
                    $handler = $class;
                    $score  = $slashes;
                }
            }
        }

        // Content directory to work on
        define( 'OCONE_CONTENT',
            dirname( __FILE__ ) . '/../' .
            oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'content' )
        );

        // Let the handler do everything else...
        try
        {
            $handler = new $handler( $uri );
            $handler->handle();
        }
        catch ( oCone_NotFoundException $e )
        {
            header( 'HTTP/1.0 404 Not Found' );
            $handler = new oCone_404Handler( $uri );
            $handler->handle();
        }
        catch ( Exception $e )
        {
            header( 'HTTP/1.0 500 Internal Server Error' );
            $handler = new oCone_500Handler( $e );
            $handler->handle();
        }
    }
}

