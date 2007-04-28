<?php

/**
 * Populates svn information to application
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2006 Kore Nordmann
 * @author Kore Nordmann <kore@php.net> 
 * @license GPL {@link http://www.gnu.org/copyleft/gpl.html}
 */
class oCone_svnInfo
{
    /**
     * File to read information for
     * 
     * @var string
     */
    protected $file;

    /**
     * Simplexml document containing svn informationm
     * 
     * @var SimpleXMLDocument
     */
    protected $info;

    /**
     * Simplexml document containing svn log
     * 
     * @var SimpleXMLDocument
     */
    protected $log;

    /**
     * Create svn info object from file
     * 
     * @param string $file 
     * @return oCone_svnInfo
     */
    public function __construct( $file )
    {
        $this->file = realpath( $file );

        $xml = shell_exec( $cmd = 'svn --xml info ' . escapeshellarg( $this->file ) );
        $this->info = @simplexml_load_string( $xml );

        $xml = shell_exec( $cmd = 'svn --xml log ' . escapeshellarg( $this->file ) );
        $this->log = @simplexml_load_string( $xml );
    }

    /**
     * Interceptor to access svn properties
     * 
     * @param string $property 
     * @return mixed
     */
    public function __get( $property )
    {
        switch( $property )
        {
            case 'author':
                return (string) @$this->info->entry->commit->author;
            case 'revision':
                return (int) (string) @$this->info->entry->commit['revision'];
            case 'date':
                return (int) strtotime( (string) @$this->info->entry->commit->date );
            case 'log':
                return $this->log;
        }
    }
}

