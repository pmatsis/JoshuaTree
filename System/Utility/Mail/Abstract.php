<?php
/**
 * abstract class used to define the functionality for different mail engines
 * 
 * @package System_Utility
 * @subpackage System_Utility_Mail
 * @author Mark Henderson
 */
abstract class System_Utility_Mail_Abstract{
    /**
     * this property holds all of the defined emails that will be used with the engine
     * it has the to email address, subject, and content
     * 
     * @var array
     * @access protected
     */
    protected $_email_list = array();
    
    /**
     * holds the email to
     * 
     * @var array
     * @access protected
     */
    protected $_to = array();
    
    /**
     * holds the from email address
     * 
     * @var string
     * @access protected
     */
    protected $_from = '';

    /**
     * holds the email subject
     * 
     * @var string
     * @access protected
     */
    protected $_subject = '';
     
    /**
     * holds the email content
     * 
     * @var boolean|string
     * @access protected
     */
    protected $_content = false;
    
    /**
     * holds the template to be used in place of the content
     * 
     * @var boolean|string
     * @access protected
     */
    protected $_template = false;
    
    /**
     * method used to hold that data that will be binded with the template
     * 
     * @var array
     * @access protected
     */
    protected $_template_data = array();
    
    /**
     * holds the setting for the email connection
     * 
     * @var array
     * @access protected
     */
    protected $_settings = array();
    
    /**
     * property that holds the sent mail status
     * 
     * @var boolean
     * @access protected
     */
    protected $_status = false;
    
    /**
     * property used to hold the path of the mail library
     * 
     * @var string
     * @access protected
     */
    protected $_library_path = false;
    
    /**
     * the name of the object to be instantiated 
     *
     * @var mixed
     * @access protected
     */
    protected $_library_name = false;
    
    /**
     * property used to hold an instance of the mail library that is being used
     * 
     * @var mixed
     * @access protected
     */
    protected $_library_instance = false;
    
    /**
     * sets the to property
     *
     * @param array $to -- an array of emails
     * @access public
     * @return System_Utility_Mail_Abstract
     */
    public function to(array $to = array()){
        $this->_to = $to;
        
        return $this;
    }
    
    /**
     * set the from property
     *
     * @param string $from
     * @access public
     * @return System_Utility_Mail_Abstract
     */
    public function from($from){
        $this->_from = $from;
        
        return $this;
    }
    
    /**
     * Sets the subject for the message
     * 
     * @param string $subject
     * @access public
     * @return System_Utility_Mail_Abstract
     */
    public function subject($subject){
        $this->_subject = $subject;
        
        return $this;
    }
    
    /**
     * sets the content for the message 
     *
     * @param string $content 
     * @access public
     * @return System_Utility_Mail_Abstract
     */
    public function content($content){
        $this->_content = $content;
        
        return $this;
    }
    
    /**
     * sets the path to the template for the email
     *
     * @param string $template 
     * @access public
     * @return System_Utility_Mail_Abstract
     */
    public function template($template){
        $this->_template = $template;
        
        return $this;
    }
    
    /**
     * sets the data to be used in the template
     * must be a multidimensional array where each row corresponds with the email address for the template
     *
     * @param array $template_data
     * @access public
     * @return System_Utility_Mail_Abstract
     */
    public function templateData(array $template_data = array()){
        $this->_template_data = $template_data;
        
        return $this;
    }
    
    /**
     * method used to build each message
     * TODO: allow for different sending types. Currently it only allows for individual emails, not mass mailings
     * 
     * @access protected
     * @return System_Utility_Mail_Abstract
     */
    protected function build(){
        $vals   = array();
        $count  = count($this->_to);
        $unique = false;

        /**
         * define the content
         * if the content property is set, use it
         * if the template_data is an array with a count greater than zero, create a unique template for each email
         * else, parse the template once and use it throughout
         */
        if((bool) $this->_content){
            $content = $this->_content;
        }elseif(count($this->_template_data)){
            if(isset($this->_template_data[0]) && is_array($this->_template_data[0])){
                $unique = true;
            }else{
                $content = System_Registry_Storage::get('System_View_Load')->parse($this->_template, $this->_template_data);
            }
        }else{
            $content = System_Registry_Storage::get('System_View_Load')->parse($this->_template);
        }
        
        for($i = 0; $i < $count; $i++){
            if($unique){
                $template_data = isset($this->_template_data[$i]) ? $this->_template_data[$i] : $this->_template_data;
                $content       = System_Registry_Storage::get('System_View_Load')->parse($this->_template, $template_data);
            }
            
            /**
             * TODO: figure out a way to make the subject dynamic
             */
            $subject = (bool) $this->_subject
                ? $this->_subject
                : $this->_subject;
            
            $vals[] = array(
                'to'        => $this->_to[$i],
                'content'   => $content,
                'subject'   => $subject
            );
        }
        
        $this->_email_list = $vals;
        
        return $this;
    }
    
    /**
     * sets the settings property
     *
     * @param array $settings -- an array of emails
     * @access public
     * @return System_Utility_Mail_Abstract
     */
    public function settings(array $settings = array()){
        $this->_settings = $settings;
        
        return $this;
    }
    
    /**
     * Returns the status
     * @access public
     * @return mixed
     */
    public function getStatus(){
        return (bool) $this->_status;
    }

    /**
     * method used to define the mail library that will be used
     * This will include the file and create an instance of the mail library that will be used to send emails
     *
     * @access protected
     * @throws Exception -- if the mail library does not exist
     * @throws Exception -- if the class does not exist
     * @return System_Utility_Mail_Abstract
     */
    public function getLibrary(){
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
     * abstract send method
     */
    abstract public function send();
}