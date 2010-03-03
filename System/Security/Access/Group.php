<?php
/**
 * user groups management. Simple hash that holds groups and allow lookups
 * 
 * @package System_Security
 * @subpackage System_Security_Access
 * @author Mark Henderson
 */
class System_Security_Access_Group{
    /**
     * holds an instance of the class
     * 
     * @var System_Access_Levels -- defaults to false
     * @static
     * @access private
     */
    private static $_instance = false;
    
    /**
     * holds all of the roles
     * 
     * @var array
     * @static
     * @access private
     */
    private static $_groups = array();
    
    /**
     * class constructor made private to allow for singleton
     * 
     * @access public
     * @return void
     */
    private function __construct(){}
    
    /**
     * returns an instance of System_Security_Access_Group
     *
     * @access public
     * @static
     * @return System_Security_Access_Group
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        
        return self::_instance;
    }
    
    /**
     * method used to set the {@link _groups} property
     * 
     * @param array $groups -- the groups to be set
     * @access public
     * @static
     * @return void
     */
    public static function set(array $groups = array()){
        self::$_groups = $groups;
    }
    
    /**
     * method used to check if 
     */
    public static function in(){
        
    }
}