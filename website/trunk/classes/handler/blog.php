<?php

require_once 'handler.php';

class oCone_BlogHandler extends oCone_Handler
{
    protected $pages;

    protected $offset = 0;

    protected $limit;

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
        $extensions = array( 'txt', 'rst' );
        foreach ( $extensions as $extension )
        {
            if ( $path = realpath( $fullPath . '.' . $extension ) )
            {
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
        $html = '';
        $entry = 0;

        foreach ( $this->pages as $page )
        {
            if ( $this->offset > 0 )
            {
                $this->offset--;
                continue;
            }

            $pageHtml = oCone_RstHandler::rst2html( $page ) . "\n<hr />\n";

            $info = pathinfo( $page );
            $url = str_replace( '.html', '/' . $info['filename'] . '.html', $this->originalUri );
            $pageHtml = preg_replace( '(<h2>\s*<a )i', '\\0href="' . $url . '" ', $pageHtml );

            $html .= $pageHtml;

            if ( ++$entry >= $this->limit )
            {
                break;
            }
        }

        // @TODO: Add paging footer to html

        $this->displayContent( $html );
    }

    protected function showBlogEntry()
    {
        $html = oCone_RstHandler::rst2html( $this->uri );

        // @TODO: Add comment form

        $this->displayContent( $html );
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

