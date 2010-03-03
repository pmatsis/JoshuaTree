<?php
/**
 * Singleton class used to parse the current url in a given request
 *
 * @package System
 * @subpackage System_Request
 * @author Mark Henderson
 */
class System_Request_Url{
    /**
     * holds an instance of the class
     * 
     * @param System_Request_Url -- defaults to false
     * @static
     * @access private
     */
    private static $_instance = false;
    
    /**
     * The currently requested URL. 
     *
     * @var string The currently requested URL.
     * @access private
     */
    private $_request_uri = "";

    /**
     * Values from the currently requested URL. 
     * 
     * @var array Values from the currently requested URL.
     * @static
     * @access private
     */
    private static $_request_vars = array();
    
    /**
     * class constructor made private to allow for singleton
     * this automatically calls the setRequestUri method defining the current request
     * 
     * @access public
     * @return void
     */
    private function __construct(){
        $this->setRequestUri();
    }
    
    /**
     * resets the status of the object
     * 
     * @access public
     * @return null
     */
    public function reset(){
        $this->_request_uri = '';
        self::$_request_vars = array();
    }
    
    /**
     * returns an instance of System_Request_Url
     *
     * @access public
     * @static
     * @return System_Request_Url
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Sets the currently requested URL to the value provided, and updates the values in the _request_vars array. 
     * 
     * @param string $uri -- The string to set as the currently requested URL. Defaults to boolean false. If no value is passed in, the current REQUEST_URI in the $_SEVER superglobal is used
     * @access public
     * @return System_Request_Url
     */
    public function setRequestUri($uri = false){
        $this->reset();
        
        /**
         * remove the query string from the uri
         */
        $request_uri = $_SERVER['REQUEST_URI'];
        
        if(strpos($request_uri, '?') !== false){
            $parts = explode('?', $request_uri);

            if(count($parts)){
                array_pop($parts);
            }
            
            $request_uri = implode('?', $parts);
        }        
        
        $this->_request_uri = pick($uri, $request_uri);
        
        $vars = explode("/", trim($this->_request_uri, "/"));
        
        foreach($vars as $var) {
            self::$_request_vars[] = $var;
        }
        
        return $this;
    }
    
    /**
     * Builds a full url of the page  
     *
     * @access public
     * @return string The current base url.
     */
    public function getFullUrl(){
        return 'http://'. $_SERVER['HTTP_HOST'] .'/'. implode('/', self::$_request_vars);
    }
    
    /**
     * method used to get part of the url until a certain point
     * will return the full url if the key is not found
     * 
     * @param mixed $until_key - the key to stop the url at
     * @access public
     * @return string
     */
    public function getUrlUntil($until_key = false){
        $url = 'http://'. $_SERVER['HTTP_HOST'] .'/';
        
        foreach(self::$_request_vars as $key => $var){
            if($key === $until_key){
                break;
            }else{
                $url .= '/'. $var;
            }
        }
        
        return $url;
    }
    
    /**
     * Rewrites the numeric index keys for the request_vars array
     *
     * @param array $new_keys -- an array holding the keys to be rewritten in the order in which they should be rewritten
     * @param array $new_values -- an array holding the values associated with the keys to be written. 
     * @access public
     * @return System_Request_Url
     */
    public function rewriteKeys(array $new_keys = array(), array $new_vals = array()){
                
        if(count($new_keys) > 0){
            $new = array();
            $old = self::$_request_vars;
            
            foreach($new_keys as $it_key => $key){
                $new[$key] = isset($old[$it_key]) ? $old[$it_key] : $new_vals[$it_key];
            }
            
            self::$_request_vars = $new;
        }

        return $this;
    }
    
    /**
     * Gets an array of vars from the requested URL. This is useful in the individual module controllers. 
     * 
     * @access public
     * @static
     * @return array An array of vars from the requested URL.
     */
    public static function getRequestVars() {
        return self::$_request_vars;
    }
    
    /**
     * returns all of the request vars as a string
     * 
     * @access public
     * @static
     * @return string
     */
    public static function getRequestString(){
        return implode('/', self::getRequestVars());
    }
    
    /**
     * returns a single request var
     * will return false if the key doesn't exist in the request vars property
     * 
     * @param scalar $key - the key to be returned
     * @access public
     * @static
     * @return boolean|integer|string
     */
    public static function getRequestVar($key){
        $return = false;
        
        if(isset(self::$_request_vars[$key])){
            $return = self::$_request_vars[$key];
        }
        
        return $return;
    }
}