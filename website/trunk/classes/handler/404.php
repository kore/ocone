<?php

require_once 'handler.php';

class oCone_404Handler extends oCone_Handler
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
        return $uri;
    }

    /**
     * Handle request 
     * 
     * @return void
     */
    public function handle()
    {
        die( '<h1>Not found.</h1>' );
    }
}

