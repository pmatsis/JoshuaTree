<?php

/**
 * 
 */
class System_Security_Exception extends System_Exception_Root{
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