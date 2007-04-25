<?php

require_once 'handler.php';

class oCone_BlogHandler extends oCone_Handler
{
    /**
     * Returns a file name for the requested uri for further use by the 
     * handler.
     * 
     * @param string $uri 
     * @return string
     */
    protected function getFileForUri( $uri )
    {
        $extensions = array( 'txt', 'rst' );

        foreach ( $extensions as $extension )
        {
            if ( $path = realpath( OCONE_CONTENT . '/' . $uri . '.' . $extension ) )
            {
                break;
            }
        }

        if ( $path === false )
        {
            throw new oCone_NotFoundException( $uri );
        }
    }

    /**
     * Handle request 
     * 
     * @return void
     */
    public function handle()
    {
        // @TODO: Implement
    }
}

