<?php
/**
 * This class takes a model and reduces the result set based on the rules applied to it.
 * 
 * @package System
 * @subpackage System_Model
 * @author Mark Henderson <emehrkay@gmail.com>
 */
class System_Model_Reduce implements System_Registry_Interface{
    /**
     * an array that holds all of the fields ordered by search score
     * 
     * @var array
     * @access private
     */
    private $_sorted_results = array();
    
    private $_model;
    
    private $_rules = array();
    
    /**
     * class constructor
     *
     * @access public
     * @return null
     */
    public function __construct($model = false, array $rules = array()){
        $this->setValue(array(
           'model' => $model,
           'rules' => $rules 
        ));
    }
    
    /**
     * sets the model and rules properties
     *
     * @param array $value - an associative array used to set class properties
     * @access public
     * @return Object System_Model_Reduce
     */
    public function setValue($value){
        foreach($value as $key => $val){
            switch($key){
                case 'model':
                    $this->_model = $val;
                    break;
                
                case 'rules':
                    $this->_rules = $val;
                    break;
            }
        }
        
        return $this;
    }
    
    public function reset(){
        
        
        return $this;
    }
    
    /**
     * reduces a model based on the params passed in
     *
     * @param System_Database_Base $model -- Instance of System_Database_Base
     * @param Array $rules -- The rules in which the model must be matched against
     * @access public
     * @return Object System_Database_Base -- A clone of the original object passed in
     */     
    public function process(){
        $clone          = clone $this->_model;
        $search_obj     = System_Registry_Storage::get('System_Search_Controller');
        $sort_results   = false;
        
        $clone->setValidateInput(false);

        /**
         * loop through the object and then loop through the rules array for each iteration of the object's value
         * get an instance of the reduction utility, set the model's value and comparison value
         * if it fails the comparison, remove the row from the object
         */
        while($clone->current()){
            $i = $clone->key();
            
            foreach($this->_rules as $field => $structure){
                $value = $clone->getValue($field);
                $model_value    = $structure['type'] == 'full' ? $clone : $value ? $value : false;
                $keep           = true;
                
                if($model_value){
                    $reduction_utility = $this->getReduction($structure['type']);
                    
                    /**
                     * if it is a full search, set the search object
                     */
                    if($structure['type'] == 'full'){
                        $sort_results = true;
                        $reduction_utility->setSearchObject($search_obj);
                        $model_value = $clone->getIndexFieldValues();
                    }

                    $keep = $reduction_utility->setValue(array(
                        'model'     => $model_value,
                        'compare'   => $structure['value']
                    ))->run();

                    if(!isset($this->_sorted_results[$i]) || !(bool) $this->_sorted_results[$i]['score']){
                        $this->_sorted_results[$i] = array('score' => (int) $keep, $clone->getRow($i));
                    }
                }else{
                    $keep = false;
                }
                
                /**
                 * if keep is set to false
                 * remove the row from the object and from the _sorted_results property
                 */
                if(!(bool) $keep){
                    if(isset($this->_sorted_results[$i])){
                        unset($this->_sorted_results[$i]);
                    }
                    
                    break;
                }
            }
            
            $clone->next();
        }
        
        $results = $sort_results ? $this->orderResults()->getResults() : $this->getResults();

        return $clone->resetDataSet($results)->rewind();
    }
    
    /**
     * returns a reduction utility class
     *
     * @param String $type - The type of reduction object to be returned
     * @access private
     * @return Object_Reduce_Abstract
     */
    private function getReduction($type){
        $type   = ucfirst(strtolower($type));
        $name   = 'System_Model_Reduce_'. $type;
        
        return System_Registry_Storage::get($name);
    }
    
    /**
     * orders the results based on the score
     *
     * @access private
     * @return Object System_Model_Reduce
     */
    private function orderResults(){
        $this->_sorted_results = System_Registry_Storage::get('Utility_Array_SubKeySort')->setValue(array(
            'array' => $this->_sorted_results,
            'key'   => 'score'
        ))->sort('desc');
        
        return $this;
    }
    
    /**
     * method used to extract the data_set from the _sorted_results property
     *
     * @access private
     * @return array
     */
    private function getResults(){
        $return = array();
        
        foreach($this->_sorted_results as $key => $val){
            $return[] = $val[0];
        }
        
        return $return;
    }
}