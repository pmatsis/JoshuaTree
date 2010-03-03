<?php
/**
 * Page description
 *
 * @package 
 * @version {: }
 */

/**
 * Class description
 * 
 * @package 
 * @subpackage 
 * @author Author Name <email>
 */
class System_Model_Reduce_Full extends System_Model_Reduce_Abstract{
    /**
     * holds an instance of the search object to be used in the full text searching
     * 
     * @var Object
     * @access private
     */
    private $_search_object;
    
    /**
     * sets the search object
     *
     * @access public
     * @return System_Model_Reduce_Full
     */
    public function setSearchObject($search_object){
        $this->_search_object = $search_object;
        
        return $this;
    }
    
    /**
     * runs the search against the model object
     *
     * @access protected
     * @return Object System_Model_Search_Full
     */
    protected function _doExecute(){
        return $this->_search_object->setValue(array(
            'haystack'  => $this->_model_value,
            'term'      => $this->_comparison_value
        ))->start()->getScore(); 
    }
}