<?php
/**
 * interface to be used whenever a class can be called by the system_registry_storage class
 * 
 * @package System
 * @subpackage System_Registry
 * @author Mark Henderson
 */
interface System_Registry_Interface{
    /**
     * method used to reset the object to an empty state
     *
     * @access public
     * @return should return an instance of the containing object
     */
    public function reset();
    
    /**
     * this method is used to set the value for a class
     *
     * @param mixed $value, value to be set
     * @access public
     * @return should return an instance of the containing object
     */
    public function setValue($value); 
}