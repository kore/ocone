<?php

require_once 'svn.php';

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
     * Putput content type
     * 
     * @var string
     */
    protected $contentType = 'html';

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
                OCONE_CONTENT
            ),
            '(^/?(?:\\.\\.?/|[^.][^/]*/)*[^.][^/]*$)',
            RegexIterator::MATCH,
            RegexIterator::USE_KEY
        );

        return $this->navigationIteratorToArray( $iterator );
    }

    /**
     * Converts a filename to a menu title
     * 
     * @param string $file 
     * @return string
     */
    protected function getNameFromFile( $file )
    {
        $info = pathinfo( $file );
        return ucfirst( $info['filename'] );
    }

    /**
     * Get absolute URL for the current site with defined extension
     * 
     * @param string $type 
     * @return string
     */
    protected function getUrl( $type = 'html' )
    {
        $info = pathinfo( $this->originalUri );

        if ( $info['dirname'] === '/' )
        {
            return '/' . $info['filename'] . '.' . $type;
        }
        else
        {
            return $info['dirname'] . '/' . $info['filename'] . '.' . $type;
        }
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
            $name = $this->getNameFromFile( $path );

            $info = pathinfo( $path );
            $array[$name]['link'] = $directory . $info['filename'] . '.html';
    
            // Iterate over children
            if ( $navigation->hasChildren() )
            {
                $array[$name]['childs'] = $this->navigationIteratorToArray( $navigation->getChildren(), $directory . $info['filename'] . '/' );
            }
        }

        return $array;
    }

    /**
     * Displays svn log as RSS feed
     * 
     * @return void
     */
    protected function showLog()
    {
        $feed = new ezcFeed( 'rss2' );
        $feed->title = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'title' );
        $feed->link = $blogUrl = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'url' );
        $feed->description = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'description' );
        $feed->author = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'author' );
 
        $svn = new oCone_svnInfo( $this->uri );
        $url = $this->getUrl();

        foreach ( $svn->log as $entry )
        {
            $item = $feed->newItem();

            $item->title = 'Revision ' . $entry['revision'];
            $item->link = $url;
            $item->description = nl2br( trim( (string) $entry->msg ) );
            $item->author = (string) $entry->author . '@ocone.org';
            $item->published = (string) $entry->date;
            $item->updated = (string) $entry->date;
        }

        $this->contentType = 'xml';
        $this->displayContent( $feed->generate() );
    }

    /**
     * Special handler for html content
     * 
     * @return string
     */
    protected function displayHtmlContent( $content )
    {
        $url = $this->originalUri;
        $site = oCone_Dispatcher::$configuration->getSettings( 'site', 'general', array( 'author', 'title' ) );
        $navigation = $this->getNavigation();
        $svn = new oCone_svnInfo( $this->uri );

        $feedUrl = $this->getUrl( 'rss' );

        ob_start();
        include OCONE_BASE . 'templates/main.php';
        return ob_get_clean();
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
        switch ( $this->contentType )
        {
            case 'html':
                $output = $this->displayHtmlContent( $content );
                break;
            case 'xml':
                header( 'Content-Type: text/xml' );
            default:
                $output = $content;
                break;
        }

        // Write cache item
        if ( $static && OCONE_CACHE )
        {
            $cacheFile = OCONE_BASE . 'htdocs' . $this->originalUri;

            if ( !is_dir( dirname( $cacheFile ) ) )
            {
                mkdir( dirname( $cacheFile ), 0777, true );
            }

            file_put_contents(
                $cacheFile,
                $output
            );
        }

        // Finally output
        echo $output;
    }

    /**
     * Remove cache file
     * 
     * @param string $url 
     * @return void
     */
    protected function clearCache( $url )
    {
        $cacheFile = OCONE_BASE . 'htdocs' . $url;
        if ( is_file( $cacheFile ) )
        {
            unlink( $cacheFile );
        }
    }

    /**
     * Handle requests
     * 
     * @return void
     */
    abstract public function handle();
}

