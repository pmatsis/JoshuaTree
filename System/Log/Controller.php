<?php
/**
 * Handles all of the logging for the system
 *
 * @package System
 * @version {: }
 */

/**
 * Logs errors the correct files
 * 
 * @package System
 * @subpackage System_Log
 * @author Mark Henderson <emehrkay@gmail.com>
 */
class System_Log_Controller implements System_Registry_Interface{
    /**
     * holds the value for which file will be logged
     * 
     * @var string|boolean
     * @access private
     */
    private $_log_file = false;
    
    /**
     * defines the type of log that will be written
     *
     * @var string
     * @access private
     */
    private $_log_type = 'csv';
    
    /**
     * Class Constructor
     *
     * @access public
     * @return null
     */     
    public function __construct($log_file = false){
        $this->setValue($log_file);
    }
    
    /**
     * resets the class
     *
     * @access public
     * @return object, instance of this class
     */
    public function reset(){
        $this->_log_file = false;
        
        return $this;
    }
    
    /**
     * sets which log file should be used
     *
     * @access public
     * @return object, instance of this class
     */
    public function setValue($file){
        if($file && trim($file) !== ''){
            $this->_log_file = $file;
        }
        
        return $this;
    }
    
    /**
     * writes to the log file
     *
     * @access public
     * @return object, instance of this class
     */
    public function write($message = ''){
        if($message && trim($message) !== ''){
            
            $email      = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : '';
            $user_id    = isset($_SESSION['user']['user_id']) ? $_SESSION['user']['user_id'] : 0;
            
            /**
             * log values
             */
            $values = array(
                time(),
                $email,
                $user_id,
                $message
            );
            
            /**
             * open the file, then write the contents
             */
            $handle = fopen(FILE_LOG_PATH .'/'. $this->_log_file, 'a+');
            fputcsv($handle, $values);
            fclose($handle);
        }
        
        return $this;
    }
}