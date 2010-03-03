<?php
/**
 * this class uses php's built in mail function to send emails
 * 
 * @package System_Utility
 * @subpackage System_Utility_Mail
 * @author Mark Henderson
 */
class System_Utility_Mail_Database extends System_Utility_Mail_Abstract{
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
     * method used to define which mail library to use
     * not needed for generic php-based emails
     *
     * @access public
     * @return System_Utility_Mail_Generic
     */
    public function getLibrary(){
        $this->_library_instance = new Object_Email_Model();
        
        return $this;
    }
    
    /**
     * method used to send emails
     *
     * @access public
     * @return
     */
    public function send(){
        $this->build();
        $date_created   = System_Registry_Storage::get('System_Utility_Date')->setValue(time())->mysqlDateTime()->get();
        $count = 0;
        
        foreach($this->_email_list as $data){
            $values = array(
                'to'        => $data['to'],
                'from'      => $this->_from,
                'subject'   => $this->_subject,
                'content'   => $data['content'],
                'date_created' => $date_created
            );
            
            $email = new Object_Email_Model();            
	        $work = $email->setValue($values)->registerNew();
	        $count++;
        }
		
		if ($count) {
		  	$this->_status = $work->commit();
		}
		
		return $this;
    }
}