<?php

require_once 'handler.php';

class oCone_RstHandler extends oCone_Handler
{
    protected $output;

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

        // Find file trying various extensions
        $extensions = array( 'txt', 'rst' );

        foreach ( $extensions as $extension )
        {
            if ( $path = realpath( OCONE_CONTENT . '/' . $uri . '.' . $extension ) )
            {
                return $path;
            }
        }

        // Bail out, if no file could be found.
        if ( $path === false )
        {
            throw new oCone_NotFoundException( $uri );
        }
    }

    /**
     * Converts a rst document to html and returns the html markup.
     * 
     * @param string $rstFile 
     * @return string
     */
    public static function rst2Html( $rstFile )
    {
        // Call rst to html converter from docutils
        $html = shell_exec( 'rst2html.py -q --compact-field-lists --link-stylesheet --initial-header-level=2 --no-doc-title ' . escapeshellarg( $rstFile ) );

        // We only need the stuff between the body tags
        $html = preg_replace( '(^.*<body[^>]*>(.*)</body>.*$)ims', '\\1', $html );
        // Remove all those useless divs
        $html = preg_replace( '(</?div[^>]*>\s*)', '', $html );
        // Give TOC <ul> a more meaningful name and remove useless toc header
        // Hint: Only the \s matches line breaks
        $html = preg_replace( '(.*topic-title.*\s*<ul class="simple">)i', '<ul class="toc">', $html );

        return $html;
    }

    /**
     * Handle request 
     * 
     * @return void
     */
    public function handle()
    {
        switch ( $this->output )
        {
            case 'rss':
                $this->showLog();
                break;
            default:
                $this->displayContent( self::rst2html( $this->uri ) );
        }
    }
}

