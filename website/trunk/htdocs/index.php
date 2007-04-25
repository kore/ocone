<?php

define( 'OCONE_CACHE', true );

set_include_path( dirname( __FILE__ ) . '/../classes' );
require_once 'dispatcher.php';

oCone_Dispatcher::dispatch( $_SERVER['REQUEST_URI'] );

