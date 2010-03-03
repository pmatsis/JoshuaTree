<?php
/**
 * Class description
 *  
 * @package System
 * @subpackage System_Request
 * @author Peter Matsis
 */
class System_Request_Controller {
    /**
     * constant to hold the default request for
     *
     * @var string
     */
    const DEFAULT_COMMAND_OBJECT = 'Command_Home_Index';
    
    /**
     * constant to hold the default response type
     *
     * @var string
     */
    const DEFAULT_RESPONSE_OBJECT = 'System_Request_Response_Type_Html';
    
    /**
     * constant to hold the default response state type
     *
     * @var string
     */
    const DEFAULT_RESPONSE_STATUS_TYPE = 'default';
    
    /**
     * holds an instance of System_Request_Event
     * 
     * @var System_Request_Event
     * @static
     * @access private
     */
    private static $_event_object = false;
     
    /**
     * tells the request to check against the session cookie or not
     * 
     * @var boolean
     * @access private
     */
    private $_cookie_check = true;
    
    /**
     * tells the request to check against the form token or not
     * 
     * @var boolean
     * @access private
     */
    private $_token_check = true;
    
    /**
     * tells the request to check for an api key or not
     * 
     * @var boolean
     * @access private
     */
    private $_api_key_check = true;
    
    /**
     * tells the response object to output headers or not
     * 
     * @var boolean
     * @access private
     */
    private $_output_headers = true;
    
    /**
     * holds the current request object
     * 
     * @var boolean|Object 
     * @access private
     */
    private $_request_object = false;
    
    /**
     * holds the current response object
     * 
     * @var boolean|Object 
     * @access private
     */
    private $_response_object = false;
    
    /**
     * holds the current response object
     * 
     * @var boolean|Object 
     * @access private
     */
    private $_response_state_object = false;
    
    /**
     * Stores the data in a property of the class
     * 
     * @var array
     * @access private
     */
    private $_data = array();
    
    /**
     * Stores an instance of the error class
     * 
     * @var Object - Instance of System_Error_Base
     * @access private
     */
    private $_error_object;
    
    /**
     * tells the response_object to return headers
     *
     * @var boolean
     * @access private
     */
    private $_set_headers = true;
    
    /**
     * holds an instance of the registry
     *
     * @var boolean
     * @access private
     */
    private $_registry;
    
    /**
     * holds the data for the request
     * 
     * @var array
     * @access private
     */
    private $_command_data = array();
    
    /**
     * Class Constructor
     *
     * @access public
     * @return System_Request_Controller
     */     
    public function __construct(array $data = array()){
        $this->setData($data);
        $this->_registry     = System_Registry_Root::getInstance();
        $this->_error_object = System_Error_Base::getInstance();
        $this->_event_object = System_Request_Event::getInstance();
        
        return $this;
    }
    
    /**
     * determines which command to return
     * TODO: come up with a way to all for all response types to be represented in the System_Request_Command_Generic call
     * 
     * @access private
     * @return string that represents a System_Request_Command_Abstract
     */
    private function getCommand(){
        $use_404 = false;
        $use_401 = false;
        
        if($this->validateRequest()){
			if(!isset($this->_data['command']) || !class_exists($this->_data['command'])){
		        $use_404 = true;
			}
		}else{    
		    $use_401 = true;
		}
		
		if($use_401 || $use_404){
            $event                        = $use_404 ? 404 : 401;
		    $request_event                = System_Request_Command_Event::getInstance();
		    $this->_command_data['event'] = $event;
			$command                      = 'System_Request_Command_Generic';
		}else{
			$command = $this->_data['command'];
		}
		
		return $command;
    }
    
    /**
     * Returns a response type
     *
     * @access private
     * @return String that names the System_Request_Response_Abstract
     */
    private function getResponse(){
        if(isset($this->_data['response_type'])){
            $response = 'System_Request_Response_Type_' . ucfirst($this->_data['response_type']);
        }else{
            $response = 'System_Request_Response_Type_Html';
        }
        
        return $response;
    }
    
    /**
     * method used to set the template in with the System_Registry_Root if it is defined in the route
     *
     * @access private
     * @return System_Request_Controller
     */
    private function setTemplate(){
        if(isset($this->_data['template'])){
            $this->_registry->setTemplate($this->_data['template']);
        }
        
        return $this;
    }
    
    /**
     * this method sets up the request
     * and fires applicable events
     * 
     * @access public
     * @return System_Request_Controller
     */
    public function setUp(){
        $this->_event_object->getEventObj()->fire('pre_command');
        
      	$command  = $this->getCommand();		
      	$response = $this->getResponse();
      	
      	$this->setTemplate();
       
        /**
         * get the objects
         */
        $command_object         = new $command($this->_data);   
        $response_object        = new $response;
        $response_state_object  = new System_Request_Response_Status('default');

        /**
         * sets the registry and data for the command object
         */
        $command_object->setRegistry($this->_registry)->setData($this->getCommandData());

        /**
         * set the request, response, and response_state objects
         * then register the response state object and error object with the request object
         */
        $this->setCommandObject($command_object)->setResponseObject($response_object)->setResponseStateObject($response_state_object)->registerRequestObjects();  
        
        $this->_event_object->getEventObj()->fire('post_command');
            
        return $this;
    }
    
    /**
     * this method will merge the command data with the allowed data -- get or post based on the routing rules
     * 
     * @access private
     * @return array - the data that will be set to the command object
     */
    private function getCommandData(){        
        if(isset($this->_data['allow']) && $this->_data['allow'] != 'none'){
            if(preg_match('/post/i', $this->_data['allow']) && count($_POST)){
                $this->_command_data = array_merge($this->_command_data, $_POST);
            }
            
            if(preg_match('/get/i', $this->_data['allow']) && count($_GET)){
                $this->_command_data = array_merge($this->_command_data, $_GET);
            }
            
            if(preg_match('/files/i', $this->_data['allow']) && count($_FILES)){
                $this->_command_data['files'] = $_FILES;
            }
        }
        
        return $this->_command_data;
    }
    
    /**
     * this method is used to define the command data that is passed to the command object
     *
     * @param array $data -- the array of data that you want passed.
     * @access public
     * @return System_Request_Controller
     */
    public function setCommandData($data = array()){
        $this->_command_data = $data;
        
        return $this;
    }
    
    /**
     * sets the request_object property
     *
     * @param System_Request_Command_Abstract $object
     * @access public
     * @return System_Request_Controller
     */
    public function setCommandObject(System_Request_Command_Abstract $object){
        $this->_command_object = $object;

        return $this;
    }
    
    /**
     * sets the response_object property
     *
     * @param Object $object 
     * @access public
     * @return System_Request_Controller
     */
    public function setResponseObject($object){
        $this->_response_object = $object;

        return $this;
    }
    
    /**
     * sets the response_state_object property
     *
     * @param Object $object
     * @access public
     * @return System_Request_Controller
     */
    public function setResponseStateObject($object){
        $this->_response_state_object = $object;

        return $this;
    }
    
    /**
     * registers the response_state_object and error_object with the request_object
     *
     * @access public
     * @return Object - System_Request_Controller
     */
    public function registerRequestObjects(){
        $this->_command_object->registerResposeStateObject($this->_response_state_object)->registerErrorObject($this->_error_object);
        
        return $this;
    }
    
    /**
     * verifies the current request against the various request objects
     * if any of the verification methods fail, the 404 error is thrown ending the rest of the script execution
     *
     * @access private
     * @return boolean
     */
    private function validateRequest(){
		$passed = true;
        
		if(isset($this->_data['validate']) && trim($this->_data['validate']) !== ''){
			$validate = $this->_data['validate'];
			$methods  = explode(',', $validate);
			$data     = $this->_data;
			
			/**
			 * loop through all of the defined validation methods
			 * check to see if it is numeric, if so, run the access validation
			 * end the loop on failure and call the 401 error command
			 */
			foreach($methods as $method){
				if(is_numeric($method)){
					$data['access_level'] = $method;
					$method = 'Access';
				}
				
				if(!System_Request_Validation_Factory::get($method, $data)){
					$passed = false;
					break;
				}
			}
		}
		
		return $passed;
    }

    /**
     * Sets the data property with the data needed
     * 
     * @access public
     * @return mixed
     */
    public function setData($data){
        $this->_data = $data;
        
        return $this;
    }
    
    /**
     * method that processes the request
     *
     * @access public
     * @return Mixed - the results of the request
     */
    public function process(){
        $this->_command_object->doExecute();

        $message = $this->_command_object->getMessage();
        
        if($this->_output_headers){
			$this->_response_object->headers();
		}

        return $this->_response_object->setData($message)->processResponse();
    }
}