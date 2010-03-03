<?php
/**
 * abstract class used to give common functionality to request validations
 * 
 * @package System_Request
 * @subpackage System_Request_Validation
 * @author Mark Henderson
 */
abstract class System_Request_Validation_Abstract Implements System_Registry_Interface{
    /**
     * the data that is used in the current request
     * 
     * @var array
     * @access private
     */
	protected $_data = array();
	
	/**
	 * method used to set the data property
	 * implemented from System_Registry_Interface
	 * 
	 * @param array $data -- the data from the current request
	 * @access public
	 * @return System_Request_Validation_Abstract
	 */
	public function setValue($data){
		$this->_data = $data;
		
		return $this;
	}
	
	/**
	 * resets the state of the validation object
	 * 
	 * @access public
	 * @return System_Request_Validation_Abstract
	 */
	public function reset(){
		$this->_data = array();
		
		return $this;
	}
	
	/**
	 * method used to validate the current request against 
	 * should always return a boolean value
	 */
	abstract public function validate();
}