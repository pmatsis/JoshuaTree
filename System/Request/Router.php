<?php
/**
 * this class handles all of the routing in the system
 * 
 * @package System
 * @subpackage System_Request
 * @author Mark Henderson
 */
class System_Request_Router{
    /**
     * constant for the default command response type
     * 
     * @var string
     */
    const DEFAULT_RESPONSE_TYPE = 'html';
    
    /**
     * constant for the default command object
     * 
     * @var string
     */
    const DEFAULT_COMMAND_OBJECT = 'home';
    
    /**
     * constant for the default data allowance for the command
     * 
     * @var string
     */
    const DEFAULT_ALLOWANCE = 'none';
    
    /**
     * constant for the default rules that are tested before the execution of the command
     * 
     * @var boolean|array
     */
    const DEFAULT_RULES = false;
    
    /**
     * holds the rules for the request routing
     * 
     * @var array
     * @access private
     */
    private $_routes = array();

	/**
	 * holds all of the ignored routes
	 * 
	 * @var array
	 * @static
	 * @access private
	 */
	private static $_ignored_routes = array();
    
    /**
     * holds an instance of the router
     * 
     * @var boolean|System_Request_Router
     * @access private
     */
    private static $_instance = false;
    
    /**
     * private constructor to allow for singleton
     */
    private function __construct(){
    }
    
    /**
     * returns an instance of the front controller class
     *
     * @access public
     * @static
     * @return object, instance of this class
     */
    public static function getInstance(){
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * sets the routes property
     * only needs to be used during the initial setup
     * 
     * @access private
     * @return System_Request_Router
     */
    public function setRoutes(array $routes = array()){
        /**
         * prepare the rules for all of the possible routes
         */
        foreach($routes as $url => $rules){
            $urls = explode('|', $url);
            
            foreach($urls as $url_real){
                $parts    = explode('/', trim($url_real, '/'));
                $uri      = array();
                $rewrites = array();
                $defaults = array();

				/**
				 * add the ignored route to the stack
				 */
				if(isset($rules['ignore']) && $rules['ignore'] === true){
					self::$_ignored_routes[] = $url_real;
				}
				
				/**
				 * loop through the parts to determine the uri, rewrites, and defaults
				 */
                foreach($parts as $key => $val){
                    $val_extra = '';
                    
                    /**
                     * determine the val and the value for the defaults array
                     */
                    if(preg_match('/=/', $val)){
                        $val_parts  = explode('=', $val);
                        $val        = $val_parts[0];
                        $defaults[] = $val_parts[1];
                    }else{
                        $defaults[] = false;
                    }
                    
                    if(preg_match('/^:/', $val)){
                        $rewrites[$key] = substr($val, 1);
                    }else{
                        $uri[] = $val;
                    }
                }
                
                $rules['rewrites'] = $rewrites;
                $rules['defaults'] = $defaults;

                $this->_routes[implode('/', $uri)] = $rules;
            }
        }

        return $this;
    }
	
	/**
	 * method used to return the routes marked ignored in the routing settings
	 * 
	 * @access public
	 * @static
	 * @return array
	 */
	public static function getIgnoredRoutes(){
		return self::$_ignored_routes;
	}
    
    /**
     * returns the default array setup for a request
     *
     * @access public
     * @static
     * @return array
     */
    public static function getDefaultSetup(){
        return array(
            'command'        => false,
            'view'           => false,
            'model'          => false,
            'validate'       => false,
            'response_type'  => self::DEFAULT_RESPONSE_TYPE,
            'request_type'   => false,
            'allow'          => self::DEFAULT_ALLOWANCE,
            'rules'          => self::DEFAULT_RULES,
			'ignored'		 => false
        );
    }
    
    /**
     * returns the default setup options for an internal command request
     *
     * @access public
     * @static
     * @return
     */
    public static function getInternalSetup(){
        return array(
            'command'        => false,
            'view'           => false,
            'model'          => false,
            'validate'       => false,
            'response_type'  => 'json',
            'request_type'   => false,
            'allow'          => 'get|post',
            'rules'          => self::DEFAULT_RULES,
			'ignored'		 => false
        );
    }
    
    /**
     * method used to sort an array by key length
     * TODO: this is a hack and should be moved to its own library
     *
     * @access private
     * @return integer
     */
    private function keyLengthSort($a, $b){
        $len_a = strlen($a);
        $len_b = strlen($b);
        $ret   = 0;

        if($len_a > $len_b){
            $ret = 1;
        }elseif($len_b < $len_a){
            $ret = -1;
        }

        return $ret;
    }
    
    /**
     * the url vars for a request is passed and matched against the keys in the defined_routes property
     * if found, the array containing the command, view, and model objects is returned
     * 
     * @access public
     * @return array
     */
    public function matchUrl(){
        $return         = array();        
        $url_obj        = System_Request_Url::getInstance();
        $url_parts      = $url_obj->getRequestVars();
        $request_string = implode('/', $url_parts);
        $request_string = trim($request_string) == '' ? self::DEFAULT_COMMAND_OBJECT : $request_string;
        $rules          = array();
        
        /**
         * Hack used to reorder the keys by length
         * This should be moved to an array lib for reuse
         * TODO: find another way to match a url against the a defined route
         */
        uksort($this->_routes, array('System_Request_Router', 'keyLengthSort'));
        
        foreach($this->_routes as $url => $route_rules){
           if(preg_match_all('/^'. preg_quote($url, '/') .'/i', $request_string, $matches)){ 
                $return = self::getDefaultSetup();

                foreach($route_rules as $key => $val){
                    $return[$key] = $val;
                }
                
                $rules = $route_rules;
                
                if($url === $request_string) break;
            }
        }
                       
        if(isset($rules['rewrites']) && count($rules['rewrites'])){
            $url_obj->rewriteKeys($rules['rewrites'], $rules['defaults']);
        }
        
        return $return;
    }
}