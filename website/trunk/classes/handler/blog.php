<?php

require_once 'handler.php';

require_once 'handler/blog/entry.php';

class oCone_BlogHandler extends oCone_Handler
{
    protected $pages;

    protected $offset = 0;

    protected $limit;

    protected $action;

    public function __construct( $uri )
    {
        $this->limit = (int) oCone_Dispatcher::$configuration->getSetting( 'site', 'blog', 'entries' );

        parent::__construct( $uri );
    }

    /**
     * Returns a file name for the requested uri for further use by the 
     * handler.
     * 
     * @param string $uri 
     * @return string
     */
    protected function getFileForUri( $uri )
    {
        // Remove file extension
        $pathinfo = pathinfo( $uri );
        $uri = $pathinfo['dirname'] . '/' . $pathinfo['filename'];

        if ( is_numeric( $pathinfo['filename'] ) )
        {
            $uri = $pathinfo['dirname'];
            $offset = (int) $pathinfo['filename'];

            $this->offset = floor( $offset / $this->limit ) * $this->limit;

            if ( $this->offset > 0 )
            {
            }
        }

        // Check if blog index page war requested
        $fullPath = OCONE_CONTENT . '/' . $uri;
        if ( is_dir( $fullPath ) )
        {
            $files = glob( $fullPath . '/.*.txt' );
            $this->pages = array_reverse( $files );

            return $fullPath;
        }

        // Check for single blog entries
        $uri = $pathinfo['dirname'] . '/.' . $pathinfo['filename'];
        $fullPath = OCONE_CONTENT . '/' . $uri;

        $extensions = array( 'txt', 'rst' );
        foreach ( $extensions as $extension )
        {
            if ( $path = realpath( $fullPath . '.' . $extension ) )
            {
                return $path;
            }
        }

        // Check for blog entry actions
        $fullPath = OCONE_CONTENT . '/' . dirname( $pathinfo['dirname'] ) . '/.' . basename( $pathinfo['dirname'] );
        $this->action = $pathinfo['filename'];

        $extensions = array( 'txt', 'rst' );
        foreach ( $extensions as $extension )
        {
            if ( $path = realpath( $fullPath . '.' . $extension ) )
            {
                $this->originalUri = dirname( $this->originalUri ) . '.html';
                return $path;
            }
        }

        // Bail out
        if ( $path === false )
        {
            throw new oCone_NotFoundException( $uri );
        }
    }

    /**
     * Show index page forblog with latest 10 enries 
     * 
     * @access protected
     * @return void
     */
    protected function showIndexPage()
    {
        $content = '';
        $entry = 0;

        $baseUrl = str_replace( '.html', '/', $this->originalUri );

        $offset = $this->offset;
        foreach ( $this->pages as $page )
        {
            if ( $offset > 0 )
            {
                $offset--;
                continue;
            }

            $blogEntry = new oCone_BlogEntry( $page, $baseUrl );
            $content .= $blogEntry->getReducedEntry();

            if ( ++$entry >= $this->limit )
            {
                break;
            }
        }

        $offset = $this->offset;
        $limit = $this->limit;

        ob_start();
        include OCONE_BASE . 'templates/blog/list.php';

        $this->displayContent( ob_get_clean() );
    }

    /**
     * Show full view of a single blog entry
     * 
     * @return void
     */
    protected function showBlogEntry()
    {
        $baseUrl = str_replace( '.html', '/', $this->originalUri );

        $blogEntry = new oCone_BlogEntry( $this->uri, $baseUrl );

        if ( $this->action !== null )
        {
            $blogEntry->action( $this->action );
            $this->clearCache( $this->originalUri );
        }
        else
        {
            $this->displayContent( $blogEntry->getFull() );
        }
    }

    /**
     * Handle request 
     * 
     * @return void
     */
    public function handle()
    {
        if ( is_dir( $this->uri ) )
        {
            $this->showIndexPage();
        }
        else
        {
            $this->showBlogEntry();
        }
    }
}

