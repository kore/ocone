<?php

require_once 'handler.php';

require_once 'handler/blog/entry.php';

class oCone_BlogHandler extends oCone_Handler
{
    protected $pages;

    protected $offset = 0;

    protected $limit;

    protected $action;

    protected $output;

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

        // Check output type
        switch ( $ext = strtolower( $pathinfo['extension'] ) )
        {
            case 'rss':
            case 'html':
                $this->output = $ext;
                break;
            default:
                throw new oCone_NotFoundException( $uri );
        }

        // Check for blog index page offset
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
     * Build and return current base url 
     * 
     * @access protected
     * @return string
     */
    protected function getBaseUrl()
    {
        return str_replace(
            array(
                '.html',
                '.rss',
            ),
            '/',
            $this->originalUri
        );
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

        $baseUrl = $this->getBaseUrl();

        $offset = $this->offset;
        foreach ( $this->pages as $page )
        {
            if ( $offset > 0 )
            {
                $offset--;
                continue;
            }

            $blogEntry = new oCone_BlogEntry( $page, $baseUrl );
            $entry = $blogEntry->getReducedEntry();

            extract( $entry );
            include OCONE_BASE . 'templates/blog/list_entry.php';
            $html .= ob_get_clean();

            if ( ++$entry >= $this->limit )
            {
                break;
            }
        }

        $offset = $this->offset;
        $limit = $this->limit;
        $content = $html;
        
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
        $baseUrl = $this->getBaseUrl();

        $blogEntry = new oCone_BlogEntry( $this->uri, $baseUrl );

        if ( $this->action !== null )
        {
            $blogEntry->action( $this->action );
            $this->clearCache( $this->originalUri );
        }
        else
        {
            $entry = $blogEntry->getFull();
            extract( $entry );

            ob_start();
            include OCONE_BASE . 'templates/blog/entry.php';

            $this->displayContent( ob_get_clean() );
        }
    }

    /**
     * Show feed for blog category
     * 
     * @return void
     */
    protected function showCategoryFeed()
    {
        $feed = new ezcFeed( 'rss2' );
        $feed->title = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'title' );
        $feed->link = $blogUrl = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'url' );
        $feed->description = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'description' );
        $feed->author = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'author' );
        
        $baseUrl = $this->getBaseUrl();

        foreach ( $this->pages as $page )
        {
            $blogEntry = new oCone_BlogEntry( $page, $baseUrl );
            $entry = $blogEntry->getReducedEntry();

            $item = $feed->newItem();

            $item->title = $entry['title'];
            $item->link = $blogUrl . $entry['url'];
            $item->description = $entry['content'];
            $item->author = $entry['svn']->author . '@ocone.org';
            $item->published = $entry['svn']->date;
            $item->updated = $entry['svn']->date;

            if ( ++$entry >= $this->limit )
            {
                break;
            }
        }

        $this->contentType = 'xml';
        $this->displayContent( $feed->generate() );
    }

    /**
     * Show feed for a single blog entry
     * 
     * @access protected
     * @return void
     */
    protected function showBlogEntryFeed()
    {
        $feed = new ezcFeed( 'rss2' );
        $feed->title = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'title' );
        $feed->link = $blogUrl = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'url' );
        $feed->description = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'description' );
        $feed->author = oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'author' );
        
        $baseUrl = $this->getBaseUrl();
        $blogEntry = new oCone_BlogEntry( $this->uri, $baseUrl );
        $entry = $blogEntry->getFull();

        foreach ( $entry['comments'] as $comment )
        {
            $item = $feed->newItem();

            $item->title = substr( $comment['content'], 0, 20 ) . '...';
            $item->link = $blogUrl . $entry['url'];
            $item->description = $comment['content'];
            $item->author = $comment['author'];
            $item->published = $comment['date'];
            $item->updated = $comment['date'];
        }

        $this->contentType = 'xml';
        $this->displayContent( $feed->generate() );
    }

    /**
     * Handle request 
     * 
     * @return void
     */
    public function handle()
    {
        switch ( true )
        {
            case is_dir( $this->uri ) && ( $this->output === 'html' ):
                $this->showIndexPage();
                break;
            case is_file( $this->uri ) && ( $this->output === 'html' ):
                $this->showBlogEntry();
                break;

            case is_dir( $this->uri ) && ( $this->output === 'rss' ):
                $this->showCategoryFeed();
                break;
            case is_file( $this->uri ) && ( $this->output === 'rss' ):
                $this->showBlogEntryFeed();
                break;
        }
    }
}

