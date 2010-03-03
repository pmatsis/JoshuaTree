<?php
/**
 * Class to hold all of the user errors
 */
class System_Error_Base{
    /**
     * holds an instance of this class
     *
     * @var object
     * @access private
     * @static
     */
    private static $_instance = false;
    
    /**
     * holds a stack of errors
     *
     * @var array
     * @access private
     */
    private static $_error_stack = array();
    
    /**
     * Class Constructor
     *
     * @access private
     * @return null
     */     
    private function __construct(){}
    
    /**
     * returns an instance of the class
     *
     * @static
     * @return Object
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * adds a key value pair to the error_stack property
     *
     * @param string $key, a unique key that will be added to the stack
     * @param string $value, a value to be added to the stack
     * @param boolean $overwrite, overwrites the key in the error stack if it exists
     * @access public
     * @static
     * @return void
     */
    public static function addEntry($key = false, $value = false, $overwrite = false){
        /**
         * this block checks to see if the key and value are set 
		 * if they are, check to see if the overwrite property is it
		 * if it is, overwrite the key in the error_stack property with the new value
		 * else, do not add the new value
         */
		if($key && $value){
			$proceed = false;
			
			if($overwrite){
				$proceed = true;
			}elseif(!array_key_exists($key, self::$_error_stack)){
				$proceed = true;
			}

			if($proceed) self::$_error_stack[$key] = $value;

        }else{
            throw new System_Error_Exception('a key and a value must be passed to the addEntry method.');
        }
    }
    
    /**
     * resets the state of the class to blank
     *
     * @access public
     * @static
     * @return Object, instance of this class
     */
    public function reset(){
        self::$_error_stack = array();
        
        return $this;
    }
    
    /**
     * returns the error stack 
     *
     * @access public
     * @static
     * @return array|boolean
     */
    public static function get(){
        return self::$_error_stack;
    }
    
    /**
     * returns the status of the stack
     * checks for errors
     *
     * @access public
     * @static
     * @return boolean
     */
    public static function error(){
        return (bool) self::count();
    }
    
    /**
     * returns the number of errors logged
     *
     * @access public
     * @static
     * @return integer
     */
    public static function count(){
        return count(self::get());
    }
}