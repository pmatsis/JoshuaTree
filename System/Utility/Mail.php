<?php
/**
 * the Mail utility
 * 
 * @package System
 * @subpackage System_Utility
 * @author Mark Henderson
 */
class System_Utility_Mail extends System_Utility_Abstract{
    /**
     * the email engine to be used to process the mail
     * 
     * @var boolean|System_Utility_Mail_Abstract
     * @access private
     */
    private $_mail_engine = false;
    
    /**
     * Class Constructor
     *
     * @param System_Utility_Mail_Abstract $engine -- the email engine to be used
     * @access public
     * @return null
     */     
    public function __construct($engine = false){
        $engine = (bool) $engine && $engine instanceof System_Utility_Mail_Abstract 
            ? $engine 
            : new System_Utility_Mail_Generic();
        
        $this->setValue($engine);
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
     * method to send the email 
     *
     * @access public
     * @return System_Utility_Mail
     */
    public function send(){        
        /**
         * Set the Proper From user from the settings. 
         */
        $from = isset($this->_settings['from']) 
            ? $this->_settings['from']
            : System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'mail', 'from');
            
        /**
         * add all of the optional settings
         */
        if(isset($this->_settings['content'])){
            $this->_value->content($this->_settings['content']);
        }
        
        if(isset($this->_settings['template'])){
            $this->_value->template($this->_settings['template']);
        }
        
        if(isset($this->_settings['template_data'])){
            $this->_value->templateData($this->_settings['template_data']);
        }
        
        if(isset($this->_settings['subject'])){
            $this->_value->subject($this->_settings['subject']);
        }
        
        $this->_value
            ->getLibrary()
            ->from($from)
            ->to($this->_settings['to'])
            ->send();
        
        return $this;
    }
    
    /**
     * Get's the status of the Mail abstract being used. 
     * @access public
     * @return mixed
     */
    public function getStatus(){
        return $this->_value->getStatus();
    }
    
    /**
     * defines who the email will be sent to    
     *
     * @param array|string $to -- the email(s) to use
     * @access public
     * @return System_Utility_Mail
     */
    public function to($to){
        if(is_array($to)){
            $to = array('to' => $to);
        }elseif(trim($to) !== ''){
            $to = array('to' => array($to));
        }
        
        return $this->settings($to);
    }
    
    /**
     * defines the email address that the email will be sent from
     * 
     * @param string $from
     * @access public
     * @return System_Utility_Mail
     */
    public function from($from){
        return $this->settings(array('from' => $from));
    }
    
    /**
     * defines the subject for the email
     *
     * @param string $subject
     * @access public
     * @return mixed
     */
    public function subject($subject){
        return $this->settings(array('subject' => $subject));
    }    

    /**
     * sets the email content
     *
     * @param string $content -- the email content
     * @access public
     * @return System_Utility_Mail
     */
    public function content($content = ''){
        return $this->settings(array('content' => $content));
    }

    /**
     * sets the email template
     *
     * @param string $template -- the path to the email template relative to the /View/Email Folder
     * @access public
     * @return System_Utility_Mail
     */
    public function template($template = ''){
        return $this->settings(array('template' => $template));
    }
    
    /**
     * sets the data to be used in the template
     * must be a multidimensional array where each row corresponds with the email address for the template
     * 
     * @param array $value - Holds a key pair values of an array with settings for the template
     * @access public
     * @return System_Utility_Mail
     */
    public function templateData(array $values = array()){
        return $this->settings(array('template_data' => $values));
    }
    
    /**
     * TODO: REMOVE THIS METHOD. if it is being used in your code, convert to the templateData() method
     * sets the values for a template email
     * 
     * @param array $value - Holds a key pair values of an array with settings for the template
     * @access public
     * @return System_Utility_Mail
     */
    public function values(array $values = array()){
        return $this->settings(array('values' => $values));
    }
}