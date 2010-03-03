<?php
/**
 * Singleton that handles the users' session state. 
 * 
 * @package System
 * @subpackage System_Session
 * @author Mark Henderson
 */
class System_Session_Controller{    
    /**
     * the amount of time the default rememberMe method will use
     * 2 weeks
     *
     * @var object
     * @access private
     */
    private static $_remember_me_seconds = 1209600;
    
    /**
     * Class Constructor, private by design
     *
     * @access public
     * @return null
     */     
    public function __construct(){}
    
    /**
     * sets the session values
     *
     * @param array $values holds the values to be set in the session
     * @access public
     * @return void
     */
    public static function setValue($values){
        foreach($values as $key => $val){
            $_SESSION[$key] = $val;
        }
    }
    
    /**
     * sets the guest session
     *
     * @access public
     * @return object, instance of this class
     */
    public function guest(){
        $_SESSION = array(); 
        
        $this->setValue(array(
            'user' => array(
                'user_id'        => 0,
                'user_name'      => 'guest',
                'session_id'     => session_id(),
                'access_level'   => System_Security_Access_level::getValue('guest')
            ),
            'login' => array(
                'attempt'            => 0,
                'require_captcha'    => false
            ),
            'search_object'  => array()
        ));
        
        return $this;
    }
    
    /**
     * Sets the user cookie
     *
     * @param boolean|integer $seconds The amount of time to set the cookie
     * @return void
     */
    public function rememberMe($seconds = false){
        $seconds = (int) $seconds;
        $seconds = $seconds > 0 ? $seconds : self::$_remember_me_seconds;
        
        self::rememberUntil($seconds);
    }
    
    /**
     * removes the user cookie
     *
     * @return object, instance of this class
     */
    public function forgetMe(){
        self::rememberUntil(0);
        
        return $this;
    }
    
    
    /**
     * rewrites the session cookie with a new timestamp
     *
     * @param Integer $seconds - The number of seconds to be set
     * @return object, instance of this class
     */
    public function rememberUntil($seconds){
        $cookie_params = session_get_cookie_params();
        
        session_set_cookie_params(
            $seconds,
            $cookie_params['path'],
            $cookie_params['domain'],
            $cookie_params['secure']
        );
        
        return $this;
    }
    
    /**
     * expires the session cookie causing the client to delete the cookie
     *
     * @return object, instance of this class
     */
    public function expireSessionCookie(){
        $session_name = session_name();
        
        if (isset($_COOKIE[$session_name])) {
            $cookie_params = session_get_cookie_params();

            setcookie(
                $session_name,
                false,
                315554400, 
                $cookie_params['path'],
                $cookie_params['domain'],
                $cookie_params['secure']
            );
        }
        
        return $this;
    }
    
    /**
     * gets a new session id
     *
     * @access public
     * @return object, instance of this class
     */
    public function getSessionId(){
        session_regenerate_id(true);
        
        return $this;
    }
    
    /**
     * starts the session
     *
     * @access public
     * @return 
     */
    public function start(){
        session_start();
        
        return $this;
    }
    
    /**
     * determines if the user is currently logged in or not
     *
     * @access public
     * @static
     * @return boolean
     */
    public static function isLoggedIn(){
        $user_is = System_Security_Access_Level::define($_SESSION['user']['access_level']);
        $private = array_keys(System_Security_Access_Level::get('private'));

        return (bool) count(array_intersect($user_is, $private));
    }
    
    /**
     * utility method to test if the user is a certain access level. Allows for multiple arguments. Will return true if any of the arguments evaluate to true
     * example: System_Session_Controller::is('admin', 8, 'root'); //would check to see if the user is an admin, access_level 8, or root user
     *
     * @access public
     * @static
     * @return boolean
     */
    public static function is(){
        $access_levels = func_get_args();
        $passed        = false;
        
        foreach($access_levels as $key => $acl){
            if(System_Security_Access_Level::is($acl)){
                $passed = true;
                break;
            }
        }
        
        return $passed;
    }
}