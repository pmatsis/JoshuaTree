<?php
/**
 * Utility Class used to call a request or command
 * 
 * @package System
 * @subpackage System_Request
 * @author Mark Henderson
 */
class System_Request_Call{
	/**
	 * calls a command from a url
	 * allows a command to be call with complete rules in tact
	 *
	 * @param string|boolean $uri -- the url to be used for the request. Defaults to false
	 * @param array $data -- the data to be passed with the request. This mimics a GET or POST
	 * @param boolean $log -- flag to log the current requested url in user's session. Defaults to false
	 * @access public
	 * @static
	 * @return mixed
	 */
	public static function url($uri = false, $data = array(), $log = false){
		if($uri){
		    $url_obj = System_Request_Url::getInstance()->setRequestUri($uri);
		}

		$objects = System_Request_Router::getInstance()->matchUrl();

		$request = new System_Request_Controller($objects);

		if(count($data)){
		    $request->setCommandData($data);
		}

		/**
		 * log the request in the user's session
		 */
		if($log){
			$uri = implode('/', System_Request_Url::getRequestVars());
			
			if(!in_array($uri, System_Request_Router::getIgnoredRoutes())){
				$_SESSION['requested_uri'] = '/'. $uri;
			} 
		}
		
		return $request->setUp()->process();
    }
   
	/**
	 * call a command directly ignoring the set rules for the route associated with it
	 *
	 * @param array $rules -- the rules that will define the command to be called
	 * @param array $data -- the data to be passed to the command
	 * @access public
	 * @static
	 * @throws System_Request_Exception - if the command is left blank
	 * @return System_Request_Controller
	 */
	public static function command(array $rules = array(), array $data = array()){
	    if(!isset($rules['command']) || trim($rules['command']) == '') throw new System_Request_Exception('You must provide a command');

	    $rules = array_merge(System_Request_Router::getInternalSetup(), $rules);

	    $request = new System_Request_Controller($rules);
	    $request->setCommandData($data);
        
	    return $request->setUp()->process();
	}
}