<?php
/**
 * this class creates a simple hash storing access levels and methods to check against them
 * 
 * @package System_Security
 * @subpackage System_Security_Access
 * @author Mark Henderson
 */
class System_Security_Access_Level{
    /**
     * holds an instance of the class
     * 
     * @var System_Access_Levels -- defaults to false
     * @static
     * @access private
     */
    private static $_instance = false;
    
    /**
     * an array that stores all of the user access levels 
     * 
     * @var array
     * @static
     * @access private
     */
    private static $_levels = array();
    
    /**
     * an array that stores all of the public access_level names
     * 
     * @var array
     * @static
     * @access private
     */
    private static $_public = array();
    
    /**
     * class constructor made private to allow for singleton
     * 
     * @access public
     * @return void
     */
    private function __construct(){}
    
    /**
     * returns an instance of System_Access_Level
     *
     * @access public
     * @static
     * @return System_Access_Level
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        
        return self::_instance;
    }
    
    /**
     * sets the {@link $_levels} property
     *      should be defined as (all values should be unique):
     *          array(
     *              'root' => array('value' => 1)
     *              'admin' => array('value' => 2)
     *              'guest' => array('value' => 4)
     *          )
     *
     * @param array $access_levels 
     * @access public
     * @static
     * @return void
     */
    public static function set(array $access_levels = array()){
        self::$_levels = $access_levels;
    }
    
    /**
     * this method defines the public, non-logged in, roles
     * it is just an array containing the keys from the {@link $_levels} property and must be called after the set method
     *
     * @access public
     * @static
     * @return void
     */
    public static function setPublic(array $public = array()){
        foreach($public as $access_level){
            if(isset(self::$_levels[$access_level])){
                self::$_public[] = $access_level;
            }
        }
    }
    
    /**
     * method used to check against an access level by name of access level
     *
     * @param string|integer $check -- the access level to be checked. Can either be the name of the access level or the id associated with it
     * @param integer|boolean $level -- the access level to be checked against the $name in the hash. Defaults to false. If false, the session value is used.
     * @access public
     * @static
     * @return boolean
     */
    public static function is($check, $level = false){
        $passed = false;
        $level  = $level ? $level : $_SESSION['user']['access_level'];
        
        if(is_numeric($check)){
            $passed = (int) $level & $check;
        }elseif(isset(self::$_levels[$check])){
            $passed = (int) $level & (int) self::$_levels[$check]['value'];
        }
        
        return (bool) $passed;   
    }
    
    /**
     * returns the name(s) of an access level by value
     *
     * @param integer $value -- the value of the access level
     * @access public
     * @static
     * @return bool|array
     */
    public static function define($value = 0){
        $return = array();
        
        foreach(self::$_levels as $name => $definition){
            if($definition['value'] & $value){
                $return[] = $name;
            }
        }
        
        return count($return) ? $return : false;
    }
    
    /**
     * returns all defined access levels based on what is passed
     *
     * @param string $type -- the type to be returned. either all, public, or private. Defaults to all
     * @access public
     * @static
     * @return array
     */
    public static function get($type){
        switch($type){
            case 'public':
                $return = self::$_levels;
                $public = self::$_public;
                
                foreach($return as $key => $definitions){
                    if(!in_array($key, $public)){
                        unset($return[$key]);
                    }
                }
                break;
                
            case 'private':
                $return = self::$_levels;
                $public = self::$_public;
                
                foreach($return as $key => $definitions){
                    if(in_array($key, $public)){
                        unset($return[$key]);
                    }
                } 
                break;
                
            case 'all':
            default:
                $return = self::$_levels;
                break;
        }
        
        return $return;
    }
    
    /**
     * returns the value for an access level by key
     *
     * @param string $name -- the name of the access_level to be returned
     * @access public
     * @static
     * @return bool|integer
     */
    public static function getValue($name){
        $value = false;
        
        if(isset(self::$_levels[$name])){
            $value = (int) self::$_levels[$name]['value'];
        }
        
        return $value;
    }
}