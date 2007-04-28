<?php

class oCone_BlogEntry
{
    /**
     * Conatins main file of blog entry
     * 
     * @var string
     */
    protected $file;

    /**
     * Blogs base URL 
     * 
     * @var string
     */
    protected $baseUrl;

    /**
     * Path to blog entry comments directory
     * 
     * @var string
     */
    protected $commentsDirectory;

    /**
     * Array of comment files on blog entry
     * 
     * @var array( string )
     */
    protected $comments;

    public function __construct( $path, $baseUrl = '/' )
    {
        $this->file = realpath( $path );
        $this->baseUrl = $baseUrl;

        $this->checkCommentDirectory();
    }

    /**
     * Check if comments directory exist and create otherwise. Also builds 
     * list of user comments. 
     * 
     * @param string $path 
     * @return void
     */
    protected function checkCommentDirectory()
    {
        $info = pathinfo( $this->file );
        $comments = $info['dirname'] . '/' . $info['filename'] . '/';

        if ( !is_dir( $comments ) )
        {
            mkdir( $comments, 0755, true );
        }

        $this->commentsDirectory = $comments;
        $this->comments = glob( $comments . '*.txt', GLOB_NOSORT );
    }

    /**
     * Get the HTML for a reduced view of a blog entry
     * 
     * @access public
     * @return void
     */
    public function getReducedEntry()
    {
        $content = oCone_RstHandler::rst2html( $this->file );

        $info = pathinfo( $this->file );
        $url = $this->baseUrl . substr( $info['filename'], 1 ) . '.html';
        $content['html'] = preg_replace( '(<h2>\s*<a )i', '\\0href="' . $url . '" ', $content['html'] );

        $comments = $this->comments;
        $svn = new oCone_SvnInfo( $this->file );

        return array(
            'content' => $content['html'],
            'comments' => $comments,
            'svn' => $svn,
            'title' => $content['title'],
            'url' => $url,
            'baseUrl' => $this->baseUrl,
        );
    }

    /**
     * Get the HTML for a full view of a blog entry
     * 
     * @return void
     */
    public function getFull()
    {
        $content = oCone_RstHandler::rst2html( $this->file );

        $info = pathinfo( $this->file );
        $url = substr( $this->baseUrl, 0, -1 ) . '.html';
        $content['html'] = preg_replace( '(<h2>\s*<a )i', '\\0href="' . $url . '" ', $content['html'] );

        $comments = array();
        foreach ( $this->comments as $nr => $comment )
        {
            $info = pathinfo( $comment );
            $comments[$nr]['author'] = $info['filename'];
            $comments[$nr]['date'] = filemtime( $comment );
            $comments[$nr]['content'] = file_get_contents( $comment );
        }

        $svn = new oCone_SvnInfo( $this->file );
        $baseUrl = $this->baseUrl;

        return array(
            'content' => $content['html'],
            'comments' => $comments,
            'svn' => $svn,
            'title' => $content['title'],
            'url' => $url,
            'baseUrl' => $this->baseUrl,
        );
    }

    /**
     * Check comment using akismet. Return true if comment is no spam.
     * 
     * @param string $user 
     * @param string $comment 
     * @return boolean
     */
    protected function isNoSpam( $user, $comment )
    {
        $apiKey = oCone_Dispatcher::$configuration->getSetting( 'site', 'blog', 'akismet_key' );

        $RestUrl = $apiKey . '.rest.akismet.com/1.1/comment-check';

        $ch = curl_init( );
        curl_setopt( $ch, CURLOPT_URL, $RestUrl );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        
        // Request variables as defined on:
        // http://akismet.com/development/api/
        curl_setopt( $ch, 
            CURLOPT_POSTFIELDS, 
            $query = sprintf( 'blog=%s&user_ip=%s&user_agent=%s&referrer=%s&comment_type=%s&comment_author=%s&comment_content=%s',
                oCone_Dispatcher::$configuration->getSetting( 'site', 'general', 'url' ),
                urlencode( $_SERVER['REMOTE_ADDR'] ),
                urlencode( $_SERVER['HTTP_USER_AGENT'] ),
                urlencode( $_SERVER['HTTP_REFERER'] ),
                'comment',
                urlencode( $user ),
                urlencode( $comment )
            )
        );
        
        $result = curl_exec ( $ch );
        curl_close ( $ch );

        return ( $result === 'false' );
    }

    /**
     * Execute action on blog entry
     * 
     * @param string $action 
     * @return void
     */
    public function action( $action )
    {
        switch( strtolower( $action ) )
        {
            case 'postcomment':
                if ( $this->isNoSpam( $_POST['name'], $_POST['comment'] ) )
                {
                    $name = preg_replace( '([^a-z0-9_]+)', '_', $_POST['name'] );

                    // Create unique filename
                    $i = 0;
                    do {
                        if ( $i > 0 )
                        {
                            $commentFile = $this->commentsDirectory . $name . '_' . $i;
                        }
                        else
                        {
                            $commentFile = $this->commentsDirectory . $name;
                        }

                        $i++;
                    } while ( is_file( $commentFile . '.txt' ) );

                    // Store comment
                    file_put_contents( $commentFile . '.txt', $_POST['comment'] );
                }

                header( 'Location: http://' . $_SERVER['SERVER_NAME'] . substr( $this->baseUrl, 0, -1 ) . '.html' );
                break;
        }
    }
}

