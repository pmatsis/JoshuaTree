<?php
/**
 * Object used as a global hash to store instances of various objects in the system
 * all objects must implement System_Registry_Interface
 * 
 * @package System
 * @subpackage System_Registry
 * @author Mark Henderson
 */
class System_Registry_Storage{
    /**
     * property that holds an instance of the class
     *
     * @var holds instance of class
     * @access private
     */
    private static $_instance = false;
    
    /**
     * holds objects for memoization
     *
     * @var holds instances of objects
     * @access private
     */
    private static $_memo = array();
    
    /**
     * Class Constructor
     *
     * @access private
     * @return null
     */     
    private function __construct(){
    }
    
    /**
     * returns an instance of the class
     *
     * @static
     * @return Object, instance of this class
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * stores an instance of a class
     *
     * @param $class_name, The name of the class to be stored
     * @static
     * @access private
     * @return Object, class
     */
    private static function storeInstance($class_name){
        $path = explode('_', $class_name);
        
        if(class_exists($class_name)){
            self::$_memo[$path[1]][$class_name] = new $class_name;
        }else{
            /**
             * TODO: throw correct error
             */
            echo 'adfdas';
        }
    }
    
    /**
     * returns an instance of a class
     *
     * @param $class_name, The name of the class to be stored
     * @static
     * @access private
     * @return Object, Instance of the passed in class
     */
    public static function get($class_name){
        $path = explode('_', $class_name);
        
        if(!isset(self::$_memo[$path[1]][$class_name])){
            self::storeInstance($class_name);
        }
        
        $class = self::$_memo[$path[1]][$class_name];
        
        /**
         * check to see if the class has a reset method
         * if it does, run it
         */
        if(method_exists($class, 'reset')){
            $class->reset();
        }
        
        return $class;
    }
}