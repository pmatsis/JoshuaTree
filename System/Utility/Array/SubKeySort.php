<?php
/**
 * sorts a multidimensional array based on one of the keys in the sub array
 */
class System_Utility_Array_SubKeySort implements System_Registry_Interface{
    /**
     * @param _array array array of data to be sorted
     * @access private
     */
    private $_array = array();

    /**
     * @param _sort_key String the key to sort the array by
     * @access private
     */
    private $_sort_key = false;

    /**
     * Class Constructor
     *
     * @access public
     * @return null
     */     
    public function __construct(array $array = array(), $sort_key = null){
        $this->setValue(array(
            'array' => $array,
            'key'   => $sort_key
        ));
    }

    /**
     * sorts the array 
     *
     * @param $direction String Direction that you would like to sort the array by
     * @access public
     * @return RETURN TYPE
     */
    public function sort($direction = 'ascending'){
        switch($direction){
            case 'asc':
            case 'ascending':
                usort($this->_array, array('System_Utility_Array_SubKeySort', 'ascending'));
                break;
            
            case 'desc':
            case 'descending':
                usort($this->_array, array('System_Utility_Array_SubKeySort', 'descending'));
                break;
        }
    
        return $this->_array;
    }
    
    /**
     * sets the sort_key and array properties
     *
     * @param array $array -- an array whose key is the property to be set. keys can be "array" or "key"
     * @access public
     * @return Object Utility_Array_Sort
     */
    public function setValue($array){
        foreach($array as $key => $value){
            switch($key){
                case 'array':
                    $this->_array = $value;
                    break;
                    
                case 'key':
                    $this->_sort_key = $value;
                    break;
            }
        }
        
        return $this;
    }
    
    /**
     * resets the class
     *
     * @access public
     * @return Object Utility_Array_Sort
     */
    public function reset(){
        $this->_array       = array();
        $this->_sort_key    = false;

        return $this;
    }
    
    /**
     * sorts the array in ascending order
     *
     * @access private
     * @return int
     */
    private function ascending($a, $b){        
        return $this->mainSort($a[$this->_sort_key], $b[$this->_sort_key]);
    }

    /**
     * sorts the array in descending order
     *
     * @access private
     * @return int
     */
    private function descending($a, $b){
        return $this->mainSort($a[$this->_sort_key], $b[$this->_sort_key]) * -1;
    }
    
    /**
     * Figures out the type of vars and then sorts accordingly
     * 
     * @access private
     * @param mixed $value1
     * @param mixed $value2
     * @return integer -1, 0 or 1 based on the relationship between the two.
     */
    private function mainSort($value1, $value2){
        if (is_string($value1) && is_string($value2)) {
            $response = strcmp($value1, $value2); 
        } else {
            $response = $value1 > $value2 ? 1 : -1; 
            
            if ($value1 === $value2) {
                $response = 0;
            }
        }
        
        return $response;
    }    
}