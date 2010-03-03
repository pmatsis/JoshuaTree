<?php
/**
 * this class uses mail chimps php api to send mail
 * 
 * @package System_Utility
 * @subpackage System_Utility_Mail
 * @author Peter Matsis
 */
class System_Utility_Mail_MailChimp extends System_Utility_Mail_Abstract{
   /**
    * class constructor does nothing
    * 
    * @access public
    * @return void
    */
    public function __construct(){
        $this->_library_path = System_Registry_Configuration::get(System_Registry_Configuration::environment, 'mail', 'library', 'mailchimp');
        $this->_library_name = 'MailChimp';
    }
    
    /**
     * method used to define the mail library that will be used
     * This will include the file and create an instance of the mailchimp api that will be used to send emails
     *
     * @access protected
     * @throws Exception -- if the mail library does not exist
     * @throws Exception -- if the class does not exist
     * @return System_Utility_Mail_Abstract
     */
    protected function getLibrary(){
        if($this->_library_path){
            if(file_exists($this->_library_path)){
                if(class_exists($this->_library_name)){
                    require_once $this->_library_path;
                    
                    $this->_library_instance = new $this->_library_name();
                }else{
                    throw new Exception('The library class that you are trying to use does not exist. Class name: '. $this->_library_name);
                }
            }else{
                throw new Exception('The library that you are trying to use does not exist. Path: '. $this->_library_path);
            }
        }
        
        return $this;
    }
    
    /**
     * method used to send emails
     *
     * @access public
     * @return
     */
    public function send(){
        /**
         * TODO: add the mail chimp send stuff here. 
         */
    }
}