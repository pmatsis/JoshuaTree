<?php

/**
* Exception file for the Database Class files
*/
class System_Database_Exception extends System_Exception_Root { 
    /**
     * Class Constructor
     *
     * @access public
     * @return null
     */     
    public function __construct($message){
        parent::__construct($message);
    }
}