<?php
/**
 * the string utility
 * 
 * @package System
 * @subpackage System_Utility
 * @author Mark Henderson
 */
class System_Utility_String extends System_Utility_Abstract{
    /**
     * Class Constructor
     *
     * @param String $string - The string to be worked on
     * @access public
     * @return null
     */     
    public function __construct($string = false){
        $this->setValue($string);
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
     * Pulls out the attribute specified out of a list of attributes. 
     * 
     * @param string $attribute - Attribute to be pulled from a string
     * @access public
     * @return object, instance of this casls
     */
	public function getAttributeValue($attribute){
        $find = '/'. preg_quote($attribute) . '\s*=\s*"[^"]*"/';

        if(preg_match($find, $this->_value, $matches)){        
            $pattern = '/"([^"]*)"/';

            preg_match($pattern, $matches[0], $sub_matches);

            $this->_processed = $sub_matches[1];        
        }
        
        return $this;
    }
    
    /**
     * Creates a random string of alphanumeric characters to be used for different purposes. 
     * 
     * @param integer $len
     * @access public
     * @return string
     */
    public function randomString($len = 30){
        if ($len > 50){
            $len = 50;
        }
            
        $base   = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz123456789';
        $max    = strlen($base) - 1;
        $code   = '';
        
        mt_srand((double) microtime() * 1000000);
        
        while (strlen($code) < $len + 1){
            $code .= $base{mt_rand(0, $max)};
        }
                    
        $this->_processed = $code;

        return $this;
    }
    
    /**
     * returns the value of one of the string properties
     *
     * @var string, $type tells the method which type of of property to return. Defaults to the processed property. Options [processed, orignal]
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
     * Processes the user password by adding the salt to the end of the string, and encrypting it using a sha1 encryption.
     * 
     * @access public
     * @return $this
     */
    public function encodePassword(){
        $salt = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'salt');
        $salt = $salt ? $salt : '';

        if($this->_value){
            $this->_processed = sha1(md5($this->_value . $salt));
        }
        
        return $this;
    }
    
    /**
     * takes a string with underscores, removes them, and camelcases the next letter
     *
     * @access public
     * @return System_Utility_String
     */
    public function underscoreToCamelcase(){
        $parts = explode('_', $this->_value);
        
        if(count($parts)){
            $this->_processed = implode('', array_map('ucfirst', $parts));
        }
        
        return $this;
    }
    
    /**
     * converts a string that contains key value pairs to an array
     *
     * @param String $delimiter - the element that is used to delimitate key value pairs in the string
     * @param String $separator - the element used to separate pairs in the string
     * @access public
     * @return System_Utility_String
     */
    public function keyValToArray($delimiter = '=', $separator = ','){
        $arr    = array();
        $break  = explode($separator, $this->_value);
        
        if(count($break)){
            foreach($break as $key => $val){
                $temp = array_map('trim', explode($delimiter, $val));
                
                if(isset($temp[0]) && isset($temp[1])){
                    $arr[$temp[0]]  = $temp[1];
                }
            }
        }
        
        $this->_processed = $arr;
        
        return $this;
    }
    
    /**
     * converts a key:value pair from a string to an array that will be accepted by Model_New
     * 
     * @access public
     * @return System_Utility_String
     */
    public function keyValToObject($key, $value){
        $this->keyValToArray(':');                
        $array = System_Registry_Storage::get('Utility_Array_Base')->setValue($this->_processed)->emptyModel($key, $value)->get();
        $this->_processed = new System_Model_New($array); 
        
        return $this;
    }    
    
    /**
     * removes all non word characters
     *
     * @access public
     * @return System_Utility_String
     */
    public function onlyWords(){
        $this->_processed = preg_replace('/[!\W]/', ' ', $this->_value);
        
        return $this;
    }
    
    /**
     * removes all extra spaces from a string
     *
     * @param Boolean $use_original -- Use the original string or not. Defaults to true
     * @access public
     * @return System_Utility_String
     */
    public function clean($use_original = true){
        $string = $use_original ? $this->_value : $this->_processed;
        
        $this->_processed = preg_replace('/\s\s+/', ' ', $string);
        
        return $this;
    }
    
    /**
     * returns a string of 'Yes' or 'No' Depending on the integer given. If anything other than 0, than it's a no, otherwise yes.
     * @access public
     * @param mixed $value - The value you want to test. 
     * @return string
     */
    public static function displayYesNo($value){
        $return = "Yes";
        if ($value === 0 || $value === '0' || $value === '') {
            $return = "No";
        }
        
        return $return;
    }
}