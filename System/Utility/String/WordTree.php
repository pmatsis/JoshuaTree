<?php
/**
 * class used to take a string and create a word tree from it
 * the word tree creates all of the possible combinations of words in the string
 * TODO: add more methods that returns different types of word trees
 * 
 * @package System_Utility
 * @subpackage System_Utility_String
 * @author Mark Henderson
 */
class System_Utility_String_WordTree extends System_Utility_String{    
    /**
     * holds the current iteration
     * 
     * @var integer
     * @access private
     */
    private $_index = 0;
    
    /**
     * holds the split up string
     * 
     * @var array
     * @access private
     */
    private $_parsed_value = array();
    
    /**
     * holds the current length of the parsed string
     * 
     * @var Integer
     * @access private
     */
    private $_parsed_length = 0;
    
    /**
     * Class Constructor
     *
     * @param String $string - the string to be converted
     * @param Regular Expression - Regex used to split the string
     * @access public
     * @return void
     */     
    public function __construct($string, $split_by = '/ /'){
        $this->setValue($string);
        
        $this->_split_by = $split_by;
        
        $this->setup();
    }
    
    /**
     * sets up the class for processing
     *
     * @access private
     * @return void
     */
    private function setup(){
        $this->_parsed_value  = preg_split($this->_split_by, $this->_value);
        $this->_parsed_length = count($this->_parsed_value);
    }
    
    /**
     * Creates an array containing all of the possible consecutive word combinations
     *
     * @access public
     * @return System_Utility_String_WordTree
     */
    public function consecutive(){
        while($this->_index < $this->_parsed_length){
            $current = $this->_parsed_value;
            
            while(count($current) > 0){
                $this->_processed[] = implode(' ', $current);
                array_pop($current);
            }
            
            array_shift($this->_parsed_value);
            
            $this->_index++;
            
            $this->consecutive();
        }
        
        return $this;
    }
}