<?php
/**
 * System date utility
 * 
 * @package System
 * @subpackage System_Utility
 * @author Mark Henderson
 */
class System_Utility_Date extends System_Utility_Abstract{    
    /**
     * property to hold the unix timestamp for the date passed in
     *
     * @var boolean|string
     * @access private
     */
    private $_unix_date = false;
    
    /**
     * Class Constructor
     *
     * @param string $date, a date to be processed
     * @access public
     * @return null
     */     
    public function __construct($date = false){
        $this->setValue($date);
    }
    
    /**
     * abstract method used to define the default settings for this utility
     * 
     * @access protected
     * @return void
     */
    protected function defineSettings(){
        $this->settings(array());
    }
    
    /**
     * function used to set the date for the object
     *
     * @access public
     * @return Object - Utility_Date_Root
     */
    public function setValue($date){
        if(is_string($date) && trim($date) !== ''){
            $this->reset()->_value = $date;
            $this->createUnix();
        } else {
            if (!$date) {
                $this->_unix_date = false;
            } else {
                $this->reset()->_unix_date = $date;
            }
        }
        
        return $this;
    }
    
    /**
     * resets the state of the class
     *
     * @access public
     * @return Object - Utility_Date_Root
     */
    public function reset(){
        $this->_value      = false;
        $this->_unix_date  = false;
        $this->_processed  = false;
        
        return $this;
    }
    
    /**
     * function used to convert the _value property to a unix timestamp  for easy calculations
     *
     * @access private
     * @return Object - Utility_Date_Root
     */
    private function createUnix(){
        $this->_unix_date = strtotime($this->_value);
        
        return $this;
    }
    
    /**
     * returns the value of one of the date properties
     *
     * @var string, $type tells the method which type of of property to return. Defaults to the processed property. Options [processed, unix, orignal]
     * @access public
     * @return boolean|string
     */
    public function get($type = 'processed'){
        $return = false;
        
        switch($type){
            case 'processed':
                $return = $this->_processed;
                break;
                
            case 'unix':
                $return = $this->_unix_date;
                break;
                
            case 'original':
                $return = $this->_value;
                break;
        }
        
        return $return;
    }
    
    /**
     * adds or removes time to the date
     *
     * @param string $time -- a strtotime acceptable addition
     * @access public
     * @return
     */
    public function modify($time){
        $this->_processed = strtotime($time, $this->_unix_date);

        return $this;
    }
    
    /**
     * converts the date to a mysql date format
     *
     * @access public
     * @return Object - Utility_Date_Root
     */
    public function mysqlDate(){
        if ($this->_unix_date) {
            $this->_processed = date('Y-m-d', $this->_unix_date);
        } else {
            $this->_processed = '0000-00-00';
        }
        
        return $this;
    }
    
    /**
     * converts the date to a mysql datetime format
     *
     * @access public
     * @return Object - Utility_Date_Root
     */
    public function mysqlDateTime(){
        if ($this->_unix_date) {
            $this->_processed = date('Y-m-d H:i:s', $this->_unix_date);
        } else {
            $this->_processed = '0000-00-00 00:00:00';
        }
        
        return $this;
    }

    /**
     * Converts the date to a formatted date for the US
     * 
     * @param boolean $use_original -- flag stating to use the original date or the processed date
     * @access public
     * @return Object - Utility_Date_Root
     */
    public function smallDate($use_original = true){
        $date = $use_original ? $this->_unix_date : $this->_processed;
        $this->_processed = date('m/d/Y', $date);
        
        return $this;
    }
    
    /**
     * Converts the date to a formatted date for the US with the time
     * 
     * @param boolean $use_original -- flag stating to use the original date or the processed date
     * @access public
     * @return Object - Utility_Date_Root
     */
    public function smallDateTime($use_original = true){
        $date = $use_original ? $this->_unix_date : $this->_processed;
        $this->_processed = date('m/d/Y h:m a', $date);
        
        return $this;
    }
    
    /**
     * returns the date in a M-D-Y fashion
     * 
     * @param boolean $use_original -- flag stating to use the original date or the processed date
     * @access public
     * @return mixed
     */
    public function date($use_original = true){
        $date = $use_original ? $this->_unix_date : $this->_processed;
        $this->_processed = date('m-d-Y', $date);
        
        return $this;
    }

    /**
     * Converts the date to a Month year display such as 'November 2008'.
     * 
     * @param boolean $use_original -- flag stating to use the original date or the processed date
     * @access public
     * @return Object - Utility_Date_Root
     */
    public function monthYear($use_original = true){
        $date = $use_original ? $this->_unix_date : $this->_processed;
        $this->_processed = date('F Y', $date);       
        
        return $this;
    }

    /**
     * Converts the date to a Month year display such as 'November 2008'.
     * 
     * @param boolean $use_original -- flag stating to use the original date or the processed date
     * @access public
     * @return Object - Utility_Date_Root
     */
    public function fullDate($use_original = true){
        $date = $use_original ? $this->_unix_date : $this->_processed;
        $this->_processed = date('F d, Y', $date);      
        
        return $this;
    }

    /**
     * Converts the date to a formatted month integer
     * 
     * @param boolean $use_original -- flag stating to use the original date or the processed date
     * @access public
     * @return integer Month of the Date
     */
    public function month($use_original = true){
        $date = $use_original ? $this->_unix_date : $this->_processed;
        $this->_processed = date('m', $date);       
        
        return $this;
    }

    /**
     * Converts the date to a formatted year integer
     * 
     * @param boolean $use_original -- flag stating to use the original date or the processed date
     * @access public
     * @return integer Year of the Date
     */
    public function year($use_original = true){
        $date = $use_original ? $this->_unix_date : $this->_processed;
        $this->_processed = date('Y', $date);   
        
        return $this;
    }
}