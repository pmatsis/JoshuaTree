<?php
/**
 * Class description
 * 
 * @package 
 * @subpackage 
 * @author Author Name <email>
 */
abstract class System_Model_Reduce_Abstract implements System_Registry_Interface{
    /**
     * holds the value from the model to be compared against 
     * 
     * @var Scalar
     * @access protected
     */
    protected $_model_value = false;
    
    /**
     * holds the value to be compared
     * 
     * @var Scalar
     * @access protected
     */
    protected $_comparison_value = false;
    
    /**
     * sets the values to be used in comparison utility 
     * 
     * @param array $values - an array of values to be set. 
     * @access public
     * @return Object System_Model_Reduce_Abstract
     */
    public function setValue($values){
        foreach($values as $type => $value){
            switch($type){
                case 'model':
                    $this->_model_value = $value;
                    break;
                
                case 'compare':
                    $this->_comparison_value = $value;
                    break;
            }
        }
        
        return $this;
    }
    
    /**
     * resets the object
     *
     * @access public
     * @return Object System_Model_Reduce_Abstract
     */
    public function reset(){
        $this->_model_value         = false;
        $this->_comparison_value    = false;
        
        return $this;
    }
    
    /**
     * runs the comparison
     *
     * @access public
     * @return Boolean|Object -- this is dependent on the _doExecute method in the the utility
     */
    public function run(){
        return $this->_doExecute();
    }
}