<?php
/**
 * abstract class that stores the general functionality for utility objects
 * 
 * @package System
 * @subpackage System_Utility
 * @author Mark Henderson
 */
abstract class System_Utility_Abstract implements System_Registry_Interface{
    /**
     * property used to hold the value to be manipulated
     * 
     * @var scalar
     * @access protected
     */
    protected $_value;
    
    /**
     * property used to hold the value that has been manipulated
     * 
     * @var scalar -- Defaults to boolean false
     * @access protected
     */
    protected $_processed = false;
    
    /**
     * property used to hold the settings for an utility object
     * 
     * @var array
     * @access protected
     */
    protected $_settings = array();
    
    /**
     * method used to reset the state of the utility
     * this method can be overwritten in children objects
     * 
     * @access public
     * @return System_Utility_Abstract
     */
    public function reset(){
        $this->_settings = array();
        
        return $this;
    }
    
    /**
     * method used to set the value to be manipulated
     * this method can be overwritten by children objects
     * 
     * @param scalar value -- the value to be manipulated
     * @access public 
     * @return System_Utility_Abstract
     */
    public function setValue($value){
        $this->_value = $value;
        
        return $this;
    }
    
    /**
     * method used to hold settings for utility object
     * 
     * @param array $settings - an array of settings that will overwrite the {@link _settings} property
     * @access public
     * @return System_Utility_Abstract
     */
    public function settings(array $settings = array()){
        $this->_settings = array_merge_recursive($this->_settings, $settings);
        
        return $this;
    }
    
    /**
     * method used to retrieve the value from the object
     * this method can be overwritten by children objects
     *
     * @param string $type -- the type of value to be returned. Options are either 'original' or 'processed' -- Defaults to processed
     * @access public
     * @return mixed
     */
    public function get($type){
        $return = '';
        
        switch($type){
            case 'original':
                $return = $this->_value;
                break;
                
            case 'processed':
            default:
                $return = $this->_processed;
                break;
        }
        
        return $return;
    }
    
    /**
     * method that each child class should use to define default settings
     */
    abstract protected function defineSettings();
}