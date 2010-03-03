<?php
/**
 * factory object used to facilitate multiple calls to different validation methods
 * 
 * @package System_Request
 * @subpackage System_Request_Validation
 * @author Mark Henderson
 */
class System_Request_Validation_Factory{
	/**
	 * constructor made private by design
	 * 
	 * @access private
	 * @return void
	 */
	private function __construct(){}
	
	/**
	 * method used to return one of the validation methods
	 * 
	 * @param string $obj -- the name of the validation object -- Token, Cookie, etc.
	 * @param Array $data -- the data to be validated
	 * @access public
	 * @static
	 * @throws Exception -- if the validation type does not exist
	 * @return boolean -- the result of the validation
	 */
	public static function get($obj, array $data = array()){
		$validator = 'System_Request_Validation_'. ucfirst(strtolower($obj));
		
		if(class_exists($validator)){
			return System_Registry_Storage::get($validator)->reset()->setValue($data)->validate();
		}else{
			throw new Exception('The validation method that you are attempting to use -- '. $obj .' -- does not exist. Check the router settings.');
		}
	}
}