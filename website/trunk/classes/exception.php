<?php

class oCone_NotFoundException extends Exception
{
    public function __construct( $uri )
    {
        parent::__construct( "Page with uri '$uri' could not be found." );
    }
}

