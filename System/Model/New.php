<?php
/**
 * This is an empty model that will make an object out of an array.
 *
 * @package DefaultFramework
 * @version V2
 */

/**
 * This class will make an new model out of an array of data.
 * 
 * @package Object
 * @subpackage Object
 * @author Peter Matsis <pmatsis@DefaultFramework.com>
 */
class System_Model_New extends System_Database_Base{
    
    /**
     * Holds the new set of values for the data frame.
     * @var array 
     * @access private
     */
    private $_data_frame_values = array();
    
    /**
     * Instantiates the Parent class
     * Sets the working table as user
     * Sets the Data Frame
     * Sets the Primary Key Field
     * 
     * @param array $array The array of values to be added to the New Model
     * @access public
     * @return null
     */
    function __construct(array $data = array()){
        parent::__construct();
        $this->_data_frame_values = $data;
        $this->setDataFrame();
        $this->setFields();
    }
    
    /**
     * Sets the primary key field
     * @access protected
     * @return object
     */
    public function setPrimaryKey($pk){
        $this->_primary_key_field = $pk;
        return $this;
    }
    
    /**
     * sets the key, value pairs for the object
     *
     * @var array
     * @access protected
     * @return null
     */
    protected function setDataFrame(){
        
        if (isset($this->_data_frame_values[0])) {
            $data_frame = array();

            foreach ($this->_data_frame_values[0] as $key => $value) {
                $data_frame[$key] = array('type' => '');
            }

            $this->_data_frame = $data_frame;
        }
        
        return $this;
    }
    
    /**
     * sets the values for the _fields property
     * @access protected
     * @return Model_New
     */
    protected function setFields(){
        $this->resetDataSet($this->_data_frame_values);
        if (isset($this->_data_frame_values[0])) {
            $this->setValue($this->_data_frame_values[0], false, false);
        }
        
        return $this;
    }
}