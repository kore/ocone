<?php

abstract class oCone_Handler
{
    /**
     * Original URI requested by the user
     * 
     * @var string
     */
    protected $originalUri;

    /**
     * URI the handler should handle
     * 
     * @var string
     */
    protected $uri;

    /**
     * Construct handler
     * 
     * @param string $uri 
     * @return void
     */
    public function __construct( $uri )
    {
        $this->originalUri = $uri;
        $this->uri = $this->getFileForUri( $uri );
    }

    /**
     * Returns a file name for the requested uri for further use by the 
     * handler.
     * 
     * @param string $uri 
     * @return string
     */
    abstract protected function getFileForUri( $uri );

    /**
     * Returns an multidimensional array with the navigation structure
     * 
     * @access protected
     * @return array
     */
    protected function getNavigation()
    {
        $iterator = new RecursiveRegexIterator(
            new RecursiveDirectoryIterator( 
                dirname( __FILE__ ) . '/../' .
                    oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'content' ),
                RecursiveDirectoryIterator::CURRENT_AS_SELF
            ),
            '/\\/[^.][^\\/]*$/',
            RegexIterator::MATCH,
            RegexIterator::USE_KEY
        );

        return $this->navigationIteratorToArray( $iterator );
    }

    /**
     * Converts an recursive iterator to an multidiumensional array
     * 
     * @param RecursiveIterator $navigation 
     * @param string $directory 
     * @return array
     */
    protected function navigationIteratorToArray( RecursiveIterator $navigation, $directory = '/' )
    {
        $array = array();

        foreach ( $navigation as $path => $item )
        {
            $info = pathinfo( $path );

            $name = ucfirst( $info ['filename'] );
            $array[$name]['link'] = $directory . $info['filename'] . '.html';
    
            // Iterate over children
            if ( $item->hasChildren() )
            {
                $array[$name]['childs'] = $this->navigationIteratorToArray( $item->getChildren(), $directory . $info['filename'] . '/' );
            }
        }

        return $array;
    }

    /**
     * Displays the requested content and creates the cache file for static 
     * content.
     * 
     * @param string $content 
     * @return void
     */
    protected function displayContent( $content, $static = true )
    {
        $url = $this->originalUri;
        $site = oCone_Dispatcher::$configuration->getSettings( 'site', 'general', array( 'author', 'title' ) );
        $navigation = $this->getNavigation();

        ob_start();
        include dirname( __FILE__ ) . '/../templates/main.php';
        $output = ob_get_clean();

        // Write cache item
        if ( $static && OCONE_CACHE )
        {
            $cacheFile = dirname( __FILE__ ) . '/../htdocs' . $this->originalUri;

            @mkdir( dirname( $cacheFile ), 0755, true );
            file_put_contents(
                $cacheFile,
                $output
            );
        }

        echo $output;
    }

    /**
     * Handle requests
     * 
     * @return void
     */
    abstract public function handle();
}
