<?php
/**
 * this class uses php's built in mail function to send emails
 * 
 * @package System_Utility
 * @subpackage System_Utility_Mail
 * @author Mark Henderson
 */
class System_Utility_Mail_Generic extends System_Utility_Mail_Abstract{
   /**
    * class constructor does nothing
    * 
    * @access public
    * @return void
    */
    public function __construct(){
        $this->_library_path = false;
    }
    
    /**
     * method used to send emails
     *
     * @access public
     * @return
     */
    public function send(){
        $this->build();

        foreach($this->_email_list as $data){
	        $this->_status = mail($data['to'], $data['subject'], $data['content'], $this->_from);
		}
    }
}