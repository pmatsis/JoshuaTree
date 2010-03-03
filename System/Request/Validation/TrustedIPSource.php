<?php
/**
 * class used to validated that the request is coming from a trusted source
 * trusted sources should be stored in the System_Registry_Configuration object under trusted_ip_sources
 * 
 * @package System_Request
 * @subpackage System_Request_Validation
 * @author Mark Henderson
 */
class System_Request_Validation_TrustedIPSource extends System_Request_Validation_Abstract{
	/**
	 * validation method
	 * 
	 * @access public
	 * @return boolean
	 */
	public function validate(){
		$valid   = false;
		$sources = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'trusted_ip_sources');
        
        if(is_array($sources) && count($sources)){
            $valid = in_array($_SERVER['REMOTE_ADDR'], $sources);
        }
		
		return $valid;
	}
}