<?php
/**
 * Class description
 * 
 * @package 
 * @subpackage 
 * @author Author Name <email>
 */
abstract class Model_Search_Abstract{
    /**
     * holds an instance of the model to be searched against
     * 
     * @var System_Database_Base|Boolean
     * @access private
     */
    private $_model = false;
    
    /**
     * holds the rules for the search
     * 
     * @param array
     * @access private
     */
    private $_rules = array();
    
    /**
     * method used to set the model to be searched against
     *
     * @param System_Database_Base $model - a model that extends System_Database_Base
     * @access public
     * @return Object Model_Search_Abstract
     */
    public function setModel(System_Database_Base $model){
        $this->_model = $model;
        
        return $this;
    }
    
    /**
     * returns the model
     *
     * @access public
     * @return System_Database_Base|Boolean
     */
    public function getModel(){
        return $this->_model;
    }
    
    /**
     * sets the rules for the search
     *
     * @access public
     * @return Object Model_Search_Abstract
     */
    public function setRules(array $rules = array()){
        $this->_rules = $rules;
        
        return $this;
    }
    
    /**
     * returns the rules
     *
     * @access public
     * @return array
     */
    public function getRules(){
        return $this->_rules;
    }
}