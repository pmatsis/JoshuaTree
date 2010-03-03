<?php
/**
 * this class handles request types for raw data
 * nothing is sent back with the data
 * 
 * @package System_Request_Response
 * @subpackage System_Request_Response_Type
 * @author Mark Henderson
 */
class System_Request_Response_Type_Raw extends System_Request_Response_Abstract{    
    /**
     * Class Constructor
     *
     * @param Array $data - Array of data to be processed
     * @access public
     * @return null
     */     
    public function __construct(array $data = array()){
        $this->setData($data);
    }
    
    /**
     * sets the headers for a raw response type
     *
     * @access public
     * @return Object, System_Request_Response_Type_Array
     */
    public function headers(){
    }
    
    /**
     * 
     *
     * @access public
     * @return Array  
     */
    public function processResponse(){
    }
}