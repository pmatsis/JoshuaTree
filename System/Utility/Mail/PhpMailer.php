<?php
/**
 * this class is a wapper for the PhpMailer lib
 * 
 * @package System_Utility
 * @subpackage System_Utility_Mail
 * @author Mark Henderson
 */
class System_Utility_Mail_PhpMailer extends System_Utility_Mail_Abstract{
   /**
    * class constructor defines where phpmailer lives
    * 
    * @access public
    * @return void
    */
    public function __construct(){
        $php_mailer_settings = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'mail', 'phpmailer');
        $this->_library_path = $php_mailer_settings['library_path'];
        $this->_library_name = $php_mailer_settings['library_name'];

        /**
         * Set the phpmailer settings. 
         */
        $this->_settings = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'mail');
    }
    
    /**
     * method used to send emails
     *
     * @access public
     * @return System_Utility_Mail_Abstract
     */
    public function send(){
        $this->build();
        $this->_library_instance->IsSMTP();
        
        $this->_library_instance->SMTPAuth   = true;
        $this->_library_instance->SMTPSecure = 'ssl';
        $this->_library_instance->Host       = $this->_settings['smtp_server'];
        $this->_library_instance->Port       = $this->_settings['smtp_server_port'];
        $this->_library_instance->Username   = $this->_settings['username'];
        $this->_library_instance->Password   = $this->_settings['from_password'];
        $this->_library_instance->From       = $this->_settings['from'];
        $this->_library_instance->FromName   = $this->_settings['from_name'];
        $this->_library_instance->Subject    = $this->_subject;
        $this->_library_instance->WordWrap   = 50;
        
        /**
         * TODO: fix the name parsing with the email. Names should not be the key since they can easily clash
         */
        foreach($this->_email_list as $data){
            $this->_library_instance->AddAddress($data['to'], $data['to']);
            $this->_library_instance->MsgHTML($data['content']);
        }
        
        $this->_status = $this->_library_instance->send();
        
        return $this;
    }
}