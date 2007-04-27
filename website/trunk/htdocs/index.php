<?php

define( 'OCONE_CACHE', true );
define( 'OCONE_BASE', realpath( dirname( __FILE__ ) . '/..' ) . '/' );

set_include_path( OCONE_BASE . 'classes/' );
require_once 'dispatcher.php';

oCone_Dispatcher::dispatch( $_SERVER['REQUEST_URI'] );

