<?php
/**
 * class used to validate if a user is logged in before the command is executed
 * it is a simple wrapper for System_Session_Controller::isLoggedIn();
 * 
 * @package System_Request
 * @subpackage System_Request_Validation
 * @author Mark Henderson
 */
class System_Request_Validation_Loggedin extends System_Request_Validation_Abstract{
	/**
	 * validation method
	 * 
	 * @access public
	 * @return boolean
	 */
	public function validate(){
		return System_Session_Controller::isLoggedIn();
	}
}