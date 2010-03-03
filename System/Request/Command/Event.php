<?php
/**
 * Utility singleton class used to attach events to the Request_Command_Abstract
 * 
 * @package System_Request
 * @subpackage System_Request_Command
 * @author Mark Henderson
 */
class System_Request_Command_Event{
    /**
     * holds an instance of the class
     * 
     * @var System_Request_Command_Event
     * @access private
     * @static
     */
    private static $_instance = false;
    
    /**
     * holds an instance of System_Event_Controller
     * 
     * @var System_Event_Controller
     * @access private
     * @static
     */
    private static $_event_obj = false;
    
    /** 
      * Private class constructor
      *
      * @access public
      * @return void
      */
    private function __construct(){}
    
    /**
     * returns an instance of the front controller class
     *
     * @access public
     * @static
     * @return System_Request_Command_Event
     */
    public static function getInstance(){
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * method used to return the current event object
     * will instantiate the event object if it doesn't already exist
     * 
     * @access public
     * @static
     * @return System_Event_Controller
     */
    public static function getEventObj(){
        if(!self::$_event_obj) {
            self::$_event_obj = new System_Event_Controller();
        }
        
        return self::$_event_obj;
    }
    
    /**
     * wrapper method used to register events with the System_Event_Controller
     * 
     * @param string $type -- the type of events that you want to be fired
     * @param string $name -- the name of the function or method to be executed
     * @static
     * @return null
     */
    public static function add($type, $name){
        self::getEventObj();
        
        self::$_event_obj->add($type, $name);
    }
}