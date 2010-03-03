<?php
/**
 * Central location for execptions
 *
 * @package System
 * @version {: }
 */

/**
 * 
 * 
 * @package System
 * @subpackage System_Exception
 * @author Mark Henderson <emehrkay@gmail.com>
 */
class System_Exception_Root extends Exception{
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
