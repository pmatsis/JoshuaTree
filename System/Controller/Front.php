<?php
/**
 * This file is the front controller for the system
 * All requests are routed through this class.
 * 
 * @package System
 * @subpackage System_Controller
 * @author Mark Henderson
 */
class System_Controller_Front {
    /**
     * The instance of the FrontController object. (Needed for singleton behavior.) 
     *
     * @var System_Registry_Root The System_Registry object.
     * @static
     * @access private
     */
    private static $_instance = false;
    
    /**
     * Holds the current token to be used for all forms during this page load
     * 
     * @var String
     * @static
     * @access private
     */
    private static $_request_token;
    
    /**
     * class constructor set to private
     *
     * @access private
     * @return null
     */
    private function __construct(){}
    
    /**
     * returns an instance of the front controller class
     *
     * @access public
     * @static
     * @return System_Controller_Front
     */
    public static function getInstance(){
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * This method sets the initial session state.
     * Checks if the $_SESSION superglobal has a 'user' key, 
     * if not set's the user as a guest in the system 
     *
     * @access public
     * @return System_Controller_Front
     */
    public function setSession(){
        if(!isset($_SESSION['user'])){
            $session = new System_Session_Controller;
            $session->guest();
        }
        
        return $this;
    }
    
    /**
     * This method dispatches the current call
     * 
     * @access public
     * @return null
     */
    public function dispatch(){
        try {
            echo System_Request_Call::url(false, array(), true);
        } catch(Exception $e) {
            if (System_Registry_Configuration::environment() === 'development') {
                echo System_Registry_Storage::get('System_View_Load')->parse('../System/Exception/Page.phtml', array('e' => $e));
            }else{
                System_Request_Command_Abstract::setResponseStatus(401);
            }
        }
    }    
}