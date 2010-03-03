<?php
/**
 * class used to validate user access levels before a request
 * 
 * @package System_Request
 * @subpackage System_Request_Validation
 * @author Mark Henderson
 */
class System_Request_Validation_Access extends System_Request_Validation_Abstract{
	/**
	 * validation method
	 * 
	 * @access public
	 * @return boolean
	 */
	public function validate(){
		$return = false;
		
		if(isset($this->_data['access_level'])){
			$access_level = $this->_data['access_level'];
			
			/**
			 * make sure that the access level passes a boolean check against 1
			 */
			if(!($access_level & 1)){
				$access_level += 1;
			}
			
			$return = (bool) ($access_level & $_SESSION['user']['access_level']);
		}
		
		return $return;
	}
}