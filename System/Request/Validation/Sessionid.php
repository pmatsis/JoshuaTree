<?php
/**
 * class used to validate a PHP SESSION cookie value with what is sent with the request
 * this should be used with AJAX-based reqeusts
 * 
 * @package System_Request
 * @subpackage System_Request_Validation
 * @author Mark Henderson
 */
class System_Request_Validation_Sessionid extends System_Request_Validation_Abstract{
	/**
	 * validation method
	 * 
	 * @access public
	 * @return boolean
	 */
	public function validate(){
		$return = false;
		
		if(isset($this->_data['phpsessionid'])){
			$return = ($this->_data['phpsessionid'] === session_id());
		}
		
		return $return;
	}
}