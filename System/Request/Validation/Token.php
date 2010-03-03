<?php
/**
 * class used to validated a token that is sent with a request to one that is stored in the user's session
 * 
 * @package System_Request
 * @subpackage System_Request_Validation
 * @author Mark Henderson
 */
class System_Request_Validation_Token extends System_Request_Validation_Abstract{
	/**
	 * validation method
	 * 
	 * @access public
	 * @return boolean
	 */
	public function validate(){
		$return = false;
		
		if(isset($this->_data['token'])){
			/**
			 * TODO: call the System_Access_Token object to instantiate a token check
			 */
		}
		
		return $return;
	}
}