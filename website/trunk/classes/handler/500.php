<?php

require_once 'handler.php';

class oCone_500Handler extends oCone_Handler
{
    protected $exception;

    public function __construct( $e )
    {
        $this->exception = $e;
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
        return $uri;
    }

    /**
     * Handle request 
     * 
     * @return void
     */
    public function handle()
    {
        echo '<pre>', $this->exception, '</pre>';
        die( '<h1>Internal server error.</h1>' );
    }
}

