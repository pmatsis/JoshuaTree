<?php
/**
 * Abstract class that defines the functionality for various response types
 * 
 * @package System_Request
 * @subpackage System_Request_Response
 * @author Mark Henderson
 */
abstract class System_Request_Response_Abstract{
    /**
     * property used to hold the response data
     * 
     * @var array
     * @access private
     */
    private $_data = array();
    
    /**
     * method used to define the response headers
     * 
     * @access public
     * @return Object - Instance of the System_Request_Response_Type for chainablity 
     */
    abstract public function headers();
    
    /**
     * method used to convert the response array into a different type
     * 
     * @access public
     * @return Mixed - the return data type is based on the response type
     */
    abstract public function processResponse();
    
    /**
     * sets the data property
     *
     * @param Array $data - Array of data to be processed
     * @access public
     * @return Object, System_Request_Response_Type_Array
     */
    public function setData(array $data = array()){
        $this->_data = $data;
        
        return $this;
    }
    
    /**
     * returns the data property
     *
     * @access public
     * @return array
     */
    public function getData(){
        return $this->_data;
    }
}