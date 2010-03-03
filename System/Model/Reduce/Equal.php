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
class System_Model_Reduce_Equal extends System_Model_Reduce_Abstract{    
    /**
     * runs the search against the model object
     *
     * @access protected
     * @return Boolean
     */
    protected function _doExecute(){
        return $this->_model_value === $this->_comparison_value;
    }
}