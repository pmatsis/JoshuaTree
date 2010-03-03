<?php
/**
 * This class handles data validation in the system
 * TODO: refactor this into validate abstract and validation objects for each type
 *
 * @package System
 * @subpackage System_Utility
 * @author Mark Henderson 
 */
class System_Utility_Validate{
    /**
     * holds an instance of the system error class
     *
     * @var Object
     * @access private
     */
    private $_system_error;
    
    /**
     * holds the value to be validated
     *
     * @var string|integer
     * @access private
     */
    private $_value;
    
    /**
     * holds the name of the value to be validated
     *
     * @var string|boolean
     * @access private
     */
    private $_name = false;
    
    /**
     * holds the current set of rules for the validation
     *
     * @var array
     * @access private
     */
    private $_rules = array();
    
    /**
     * holds the structure for the validation rules 
     *
     * @var array
     * @access private
     * @final
     */
    private $_rules_defined = array(
        'min'   	=> false,
        'max'   	=> false
		//'required' 	=> false
    );
    
    /**
     * holds the type of check that will be done on the value
     *
     * @var boolean|string
     * @access private
     */
    private $_check_type = false;
    
    /**
     * holds the status of the current validation process
     *
     * @var boolean
     * @access private
     */
    private $_error = false;
    
    /**
     * holds the error message
     *
     * @var string|boolean
     * @access private
     */
    private $_error_message = false;
    
    /**
     * Class Constructor
     *
     * @access public
     * @return null
     */     
    public function __construct($name = false, $value = false, array $rules = array()){
        if($name && $value && count($rules)){
            $this->setName($name)->setValue($value)->setRules($rules);
        }
        
        $this->_system_error = System_Error_Base::getInstance();
    }
    
    /**
     * returns the _error property
     *
     * @access public
     * @return boolean
     */
    public function error(){
        return $this->_error;
    }
    
    /**
     * resets the valiator state
     *
     * @access public
     * @return object, Instance of this class
     */
    public function reset(){
        $this->_value = '';
        $this->_rules = array();
        $this->_name  = false;
        $this->_error = false;
        
        return $this;
    }
    
    /**
     * sets the name property
     * 
     * @param string $name
     * @access public
     * @return Object, instance of this class
     */
    public function setName($name){
        if($name && trim($name) !== ''){
            $this->_name = $name;
        }

        return $this;
    }
    
    /**
     * sets the value to be validated
     *
     * @access public
     * @return Object, instance of this class
     */
    public function setValue($value = false){
        if($value){
            $this->_value = trim($value);
        }
        
        return $this;
    }
    
    /**
     * sets the rules for the value to be validated
     *
     * @access public
     * @return Object, instance of this class
     */
    public function setRules(array $rules = array()){
        if(count($rules) && $rules['type']){
            $this->_check_type = $rules['type'];
            
            /**
             * loop through the _rules_defined property to define the rules
             */
            foreach($this->_rules_defined as $key => $val){
                if($key !== 'type' && isset($rules[$key])){
                    $this->_rules[$key] = $rules[$key];
                } 
            }
        }else{
            throw new Exception('A data type was not set to validate against');
        }
        
        return $this;
    }
    
    /**
     * process the current validation
     *
     * @access public
     * @return null
     */
    public function validate(){
        if($this->_name){
            if(is_array($this->_rules)){
                //if(method_exists($this, $this->_check_type)){
                  	/**
                     * run the initial type check
                     */
                    $type = $this->_check_type;
                   	$this->$type();

                    /**
                     * loop through the defined rules until it reaches the end or the _error property is set 
                     */
                    while(!$this->_error && list($method, $val) = each($this->_rules)){
                        if ($val) $this->$method();
                    }
      			//}else{
                   	//throw new Utility_Validate_Exception('That validation method does not exist.');
               	//}
           	}else{
               	throw new Utility_Validate_Exception('There must be rules set to process validation.');
           	}
        }else{
           	throw new Utility_Validate_Exception('There must be a name set to process validation.');
        }
        
        /**
         * if there is an error, add it to the error stack
         * get an instance of the registry and then the system_Error object
         */
        if($this->error()){
            $proper = str_replace('_', ' ', $type);
            $this->_system_error->addEntry($this->_name, 'Not a valid '. $proper);
        } 
    }

	/**
	 * checks whether $this->_value is set if required is set in the rules. 
	 * @access private
	 * @return $this
	 */
	private function required(){
		if (is_string($this->_value) && $this->_value === '') {
			$this->_error = true;
		}elseif (!isset($this->_value)) {
			$this->_error = true;
		}
		
		return $this;
	}

	/**
	 * checks to make sure the value is a mysql variable
	 *
	 * @access private
	 * @return Utility_Validate_Base
	 */
	private function mysqlVariable(){
	    if(!preg_match('/^@/', $this->_value)) $this->_error = true;
	    
	    return $this;
	}
    
    /**
     * checks the min length of the _value property
     *
     * @access private
     * @return object, instance of this class
     */
    private function min(){
        $compare = true;

        if(is_string($this->_value)){
            $len        = strlen($this->_value);
            $compare    = $len >= $this->_rules['min'];
        }else{
            $compare    = $this->_value >= $this->_rules['min'];
        }
        
        if(!$compare){ 
            $this->_error = true;
            $this->_system_error->addEntry($this->_name, ' must have at least '. $this->_rules['min'] . ' characters');
        }
        
        return $this;
    }
    
    /**
     * checks the max length of the _value property
     *
     * @access private
     * @return object, instance of this class
     */
    private function max(){
        $compare = true;

        if(is_string($this->_value)){
            $len        = strlen($this->_value);
            $compare    = $len <= $this->_rules['max'];
        }else{
            $compare    = $this->_value <= $this->_rules['max'];
        }
        
        if(!$compare){ 
            $this->_error = true;
            $this->_system_error->addEntry($this->_name, ' can only be '. $this->_rules['max'] . ' characters max');
        }
        
        return $this;
    }
    
    /**
     * Checks if the _value property is a string
     *
     * @access private
     * @return Object, instance of this class
     */
    private function string(){
        if(!is_string($this->_value)) $this->_error = true;
        
        return $this;
    }
    
    /**
     * checks the if the _value is an valid email
     *
     * @access private
     * @return object, instance of this class
     */
    private function email(){
        $reg    = "#^(((([a-z\d][\.\-\+_]?)*)[a-z0-9])+)\@(((([a-z\d][\.\-_]?){0,62})[a-z\d])+)\.([a-z\d]{2,6})$#i";
        $check  = preg_match($reg, $this->_value);

        if(!$check) $this->_error = true;

        return $this;
    }
    
    /**
     * cchecks if _value property is a valid integer
     *
     * @access private
     * @return ojbect, instance of this class
     */
    private function integer(){
        $this->_value = (int) $this->_value;
        
        if(!is_int($this->_value)) $this->_error = true;
        
        return $this;
    }
    
    /**
     * checks if _value property is a valid float
     *
     * @access private
     * @return ojbect, instance of this class
     */
    private function float(){
        $exp = '/^\-?\d+(\.\d{1,})?$/';
        
        if(!preg_match($exp, $this->_value)){
            $this->_error = true;
        } 
        
        return $this;
    }
    
    /**
     * checks if _value property is a valid mysql date
     *
     * @access private
     * @return object, instance of this class
     */
    private function date(){
        $exp = '/^\d{4}\-\d{2}\-\d{2}$/';
        
        if(!preg_match($exp, $this->_value)) $this->_error = true;
        
        return $this;
    }
    
    /**
     * checks if _value property is a valid datetime
     *
     * @access private
     * @return object, instance of this class
     */
    private function dateTime(){
        $exp = '/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}:\d{2}$/';
        
        if(!preg_match($exp, $this->_value)) $this->_error = true;
        
        return $this;
    }
    
    /**
     * checks if _value property is a valid boolean
     *
     * @access private
     * @return object, instance of this class
     */
    private function boolean(){
        if(!is_bool($this->_value)) $this->_error = true;
        
        return $this;
    }
    
    /**
     * checks if _value property is a valid password
     *
     * @access private
     * @return object, instance of this class
     */
    private function password(){
        //write regex based on password rules
        /*
        $exp = '//';
        
        if(!preg_match($exp, $this->_value)) $this->_error = true;
        */
        return $this;
    }
}