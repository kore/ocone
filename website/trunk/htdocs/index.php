<?php

// Require basic configuration
require dirname( __FILE__ ) . '/../classes/config.php';

try
{
    // Dispatch request
    wcvDispatcher::dispatch( $_SERVER['REQUEST_URI'] );
}
catch ( Exception $e )
{
    // Bail out with a 501 error
    header( 'HTTP/1.0 501 Internal Server Error' );

    try
    {
        // This is quite critical, maybe we reissue the error here, but let's
        // try to display something nice...
        $displayHandler = new wcvDisplayHtml(
            // Use empty content tree ... something bad happened
            new wcvIndexerTreeSubtree( null ),

            // Create pseudo content from error
            new wcvIndexerTreeFile(
                '501 error',
                new wcvContentError( $e ),
                new wcvMetadataError()
            )
        );

        // Display error page
        echo $displayHandler->display();
    }
    catch ( Exception $e2 )
    {
        // If this did not work do let the new exception bubble up and display
        // some very basic HTML.
        ?>
<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>WCV - Internal Server Error</title>
</head>
<body>
    <h1>WCV - Internal Server Error</h1>
<?php
        if ( WCV_DEBUG ) 
        {
            echo '<pre>', $e, '</pre>';
            echo '<pre>', $e2, '</pre>';
        }
?>
</body>
</html>
<?php
    }
}

