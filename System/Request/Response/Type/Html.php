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
class System_Request_Response_Type_Html extends System_Request_Response_Abstract{    
    /**
     * Class Constructor
     *
     * @param Array $data - Array of data to be processed
     * @access public
     * @return null
     */     
    public function __construct(array $data = array()){
        $this->setData($data);
    }
    
    /**
     * sets the headers for a json response type
     *
     * @access public
     * @return Object, System_Request_Response_Type_Array
     */
    public function headers(){
        
    }
    
    /**
     * method used to convert the data to html
     * this automatically puts the content in the frame
     *
     * @access public
     * @return Array  
     */
    public function processResponse(){ 
        return System_Registry_Storage::get('System_View_Load')->parse('Template/Frame.phtml');
    }
}