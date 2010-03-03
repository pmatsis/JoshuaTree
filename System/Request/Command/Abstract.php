<?php
/**
 * This method is the parent for all commands
 * 
 * @package System_Request
 * @subpackage System_Request_Command
 * @author Peter Matsis <pmatsis@gmail.com>
 */
abstract class System_Request_Command_Abstract {
    /**
     * http 1.1 response status codes
     * http://en.wikipedia.org/wiki/List_of_HTTP_status_codes  
     * 
     * @var array
     * @access private
     */
    private $_status_codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other (since HTTP/1.1)',
        304 => 'Not Modified',
        305 => 'Use Proxy (since HTTP/1.1)',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        449 => 'Retry With',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'
    );
    
    /**
     * holds an instance of the system registry
     * 
     * @var System_Registry_Root
     * @access protected
     */
    protected $_registry;
    
    /**
     * holds an instance of the response state object
     * 
     * @var object - instance of System_Request_Response_State
     * @access private
     */
    private $_response_state_object;
    
    /**
     * holds an instance of System_Event_Controller
     * 
     * @var System_Event_Controller
     * @access protected
     */
    protected $_event_obj = false;
    
    /**
     * holds an instance of System_Request_Event
     * 
     * @var System_Request_Event
     * @access private
     */
    private $_request_event_obj = false;
    
    /**
     * holds an instance of the error object
     * 
     * @var object - instance of System_Error_Base
     * @access protected
     */
    protected $_error_object;
    
    /**
     * Stores the message that will be going back to the user
     * 
     * @var array
     * @access protected
     */
    protected $_message = array();
    
    /**
     * property that hold the data for the given request
     * 
     * @var array
     * @access protected
     */
    private $_data = array();
    
    /**
     * property that holds all of the generated errors
     * 
     * @var array
     * @access protected
     */
    protected $_errors = array();
    
    /**
     * Public method used to process all of the action for a command
     * 
     * @access public
     * @return Object, instance of the request command that is called
     */
    abstract public function doExecute();
    
    /**
     * sets the request event object if it isn't set and also sets the event_obj property
     * this method should be called in all of the system events to ensure that the event object exists
     * 
     * @access private
     * @return System_Request_Command_Abstract
     */
    private function getRequestEvent(){
        if(!$this->_request_event_obj){
            $this->_request_event_obj = System_Request_Command_Event::getInstance();
            $this->_event_obj = $this->_request_event_obj->getEventObj();
        }
        
        return $this;
    }
    
    /**
     * Adds a message to the message going back to the user.
     * 
     * @param String $key - the key for the return message
     * @param String $message - the return message
     * @access protected
     * @return Object, instance of the request helper that is called
     */
    protected function addMessage($key, $message){
        if ($message && $key) { 
            $this->_message[$key]   =  $message;
        }

        return $this;
    }
    
    /**
     * sets the message response status to the success code
     *
     * @access protected
     * @return Object, instance of the request helper that is called
     */
    protected function setSuccess(){
        $this->addMessage('status', $this->_response_state_object->get('success'));
        
        return $this;
    }
    
    /**
     * sets the message response status to the failure code
     *
     * @access protected
     * @return Object, instance of the request helper that is called
     */
    protected function setFailure(){
        $this->addMessage('status', $this->_response_state_object->get('failure'));
        
        return $this;
    }
    
    /**
     * sets the message response status to the error code
     *
     * @access protected
     * @return Object, instance of the request helper that is called
     */
    protected function setError(){
        $this->addMessage('status', $this->_response_state_object->get('error'));
        
        return $this;
    }
    
    /**
     * method used to set the data property
     *
     * @param Array $data 
     * @access public
     * @return Object, instance of the request helper that is called
     */
    public function setData(array $data = array()){
        $this->_data = $data;

        return $this;
    }

    /**
     * If the system run into an error, build the message stack to include the status as an error, and the error stack
     * 
     * @access protected
     * @return Object System_Request_Controller
     */
    protected function processErrors(){
        $this->setError();
        $errors = array();

        foreach ($this->_error_object->get() as $field => $message) {
            $errors[$field] = $message;
        }

        $this->addMessage('error_fields', $errors);

        return $this;
    }
    
    /**
     * returns the data property
     *
     * @access public
     * @return array
     */
    public function getData(){
        return $this->_data;
    }
    
    /**
     * returns the message stack
     *
     * @access public
     * @return array
     */
    public function getMessage(){
        return $this->_message;
    }
    
    /**
     * sets the current response state object
     *
     * @param Object $object - Instance of the response_state object
     * @access public
     * @return Object, instance of the request helper that is called
     */
    public function registerResposeStateObject($object){
        $this->_response_state_object = $object;
        
        return $this;
    }
    
    /**
     * sets the error object property
     *
     * @param Object $object - Instance of System_Error_Base
     * @access public
     * @return RETURN TYPE
     */
    public function registerErrorObject($object){
        $this->_error_object = $object;
        
        return $this;
    }
    
    /**
     * method used to set the registry property
     * 
     * @param System_Registry_Root $registry - Registry object
     * @access public
     * @return System_Request_Command_Abstract
     */
    public function setRegistry($registry){
        $this->_registry = $registry;
        
        return $this;
    }
    
    /**
     * method used to call http status codes as defined here: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     * this method automatically fires the events associated with the response status code
     *
     * @param integer $code -- the response code to sent to the sent to the. Defaults to 200
     * @final
     * @access public
     * @return null
     */
    public final function setResponseStatus($code = 200){
        if(isset($this->_status_codes[$code])){
            $status = $this->_status_codes[$code];
            
            header("HTTP/1.0 $code $status");
        
            /**
             * fire any registered events for the response code
             */
            $this->getRequestEvent();
        
            $this->_event_obj->fire($code);
        }
    }
    
    /**
     * Utility method used to parse an phtml file
     *
     * @param string $path -- the path to the phtml file including the file extension
     * @param array $arguments -- an associative array used to pass variable to the phtml file
     * @access protected
     * @return string
     */
    protected function parseHtml($path, array $arguments = array()){
        return System_Registry_Storage::get('System_View_Load')->parse($path, $arguments);
    }
    
    /**
     * This function will force an SSL connection on the page.
     * @access protected
     * @param string $host - Overwrites the HTTP_HOST for the secure page. 
     * @return mixed
     */
    protected function forceSsl($host = false){
        $www_host = $_SERVER["HTTP_HOST"];
        
        if ($host) {
            $www_host = $host;
        }
        
        if (!isset($_SERVER["HTTPS"]) && System_Registry_Configuration::environment() == 'production') {
            header("Location: https://" . $www_host . $_SERVER["REQUEST_URI"]);
        }
    }
}