<?php
/**
 * this is a utility class that allows event registration
 * 
 * @package System
 * @subpackage System_Event
 * @author Mark Henderson
 */
class System_Event_Controller{
    /**
     * holds the registered events
     * 
     * @var array
     * @access private
     */
    private $_event_stack = array();
    
    /** 
      * class constructor
      *
      * @access public
      * @return void
      */
    public function __construct(){}
    
    /**
     * method used to add events to the stack
     * 
     * @param string $type -- the type of event to be added
     * @param string $name -- the name of the function or method to be executed
     * @throws Exception -- if either the type or name is passed to the method
     * @return System_Event_Controller
     */
    public function add($type, $name){
        if(trim($type) === '' || trim($name) === ''){
            throw new Exception('You must pass in both a type and name of function to be registered');
        }else{
            $this->_event_stack[$type][] = $name;
        }
        
        return $this;
    }
    
    /**
     * this method fires all of the events registered with a certain type
     * 
     * @param string $type -- the type of events that you want to be fired
     * @param array $params -- the params that will be passed to the events being fired. Defaults to an empty array
     * @access public
     * @return null
     */
    public function fire($type, array $params = array()){
        if(isset($this->_event_stack[$type])){
            foreach($this->_event_stack[$type] as $event){
                call_user_func_array($event, $params);
            }
        }
    }
    
    /**
     * removes a registered event. All events for a type will be removed if the name of the event is not passed in
     * 
     * @param string $type -- the type of events that you want to be fired
     * @param string $name -- the name of the function or method to be executed
     * @throws Exception -- if a type of event is not passed to the method
     * @return null
     */
    public function remove($type, $name){
        $type = trim($type);
        $name = trim($name);
        
        if($name !== ''){
            unset($this->_event_stack[$type][$name]);
        }elseif($type !== ''){
            unset($this->_event_stack[$type]);
        }else{
            throw new Exception('You must define a type of event to be removed.');
        }
    }
}