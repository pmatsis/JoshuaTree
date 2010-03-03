<?php
/**
 * the array utility
 * 
 * @package System
 * @subpackage System_Utility
 * @author Mark Henderson
 */
class System_Utility_Array extends System_Utility_Abstract{
    /**
     * Class Constructor
     *
     * @param Array $value - Array to be manipulated. Defaults to an empty array
     * @access public
     * @return null
     */     
    public function __construct(array $value = array()){
        $this->setValue($value);
    }
    
    /**
     * abstract method used to define the default settings for this utility
     * 
     * @access protected
     * @return void
     */
    protected function defineSettings(){
        $this->settings(array());
    }
    
    /**
     * returns the value of one of the array properties
     *
     * @var string, $type
     * @access public
     * @return boolean|string
     */
    public function get($type = 'processed'){
        $return = false;
        
        switch($type){
            case 'processed':
                $return = $this->_processed;
                break;
                
            case 'original':
                $return = $this->_value;
                break;
        }
        
        return $return;
    }
    
    /**
     * Removes all empty strings that are set for values
     *
     * @access public
     * @return System_Utility_Array
     */
    public function removeEmptyStringVals(){
        $this->_processed = $this->_value;
        
        foreach($this->_processed as $key => $value){
            if(trim($value) === ''){
                unset($this->_processed[$key]);
            }
        }

        return $this;
    }
    
    /**
     * converts an array to a delimited string
     * 
     * @param array @array - the array to be flattened, used for recursion
     * @param string $delimiter - the delimiter to separate keys and values. Defaulted to "="
     * @param string $separator - a string used to separate key value pairs. Defaulted to ","
     * @access public
     * @return System_Utility_Array
     */
    public function flatten($array = false, $delimiter = '=', $separator = ','){
        $array      = pick($array, $this->_processed, $this->_value);
        $flattened  = array();
        
        foreach($array as $key => $val){
            if(is_array($val)){
                $flattened[] = $this->flatten($val, $delimiter, $separator);
            }else{
                $flattened[] = $key . $delimiter . $val; 
            }
        }
        
        $this->_processed = implode($separator, $flattened);
        
        return $this;
    }
    
    /**
     * returns an array to use for an object from a key:value,key:value pattern
     * 
     * @param string $key_name The Key database field name
     * @param string $value_name The Value database field name
     * @access public
     * @return mixed
     */
    public function emptyModel($key_name, $value_name){
        $new_array = array();

        foreach ($this->_value as $key => $value) {
            $new_array[] = array($key_name => $key, $value_name => $value);
        }
        
        $this->_processed = $new_array;
        
        return $this;
    }
}