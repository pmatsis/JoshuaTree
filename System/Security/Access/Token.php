<?php
/**
 * Class to handle the request tokens
 * only three session tokens can exist at a time
 * each will have a five minute lifespan
 * both numbers are defined as class properties
 * 
 * @package System_Request
 * @subpackage System_Request_Validation
 * @author Mark Henderson <emehrkay>
 */
class System_Access_Token extends System_Request_Validation_Abstract{
    /**
     * holds the token lifespan in seconds
     * 
     * @var integer
     * @access private
     */
    private static $_token_life = DEFAULT_TOKEN_LIFE;
    
    /**
     * holds the max number of tokens allowed
     * 
     * @var integer
     * @access private
     */
    private static $_token_allowance = DEFAULT_TOKEN_ALLOWANCE;
    
    /**
     * Class Constructor
     *
     * @access private
     * @return null
     */     
    private function __construct(){}
    
    /**
     * Checks the token stack
     * only run when new tokens need to be added
     *
     * @access private
     * @return void
     */
    private function checkStack(){
        /**
         * if there are session request tokens
         * loop through them and see if any are older than the token lifespan
         * if so remove it
         * Check to see if there are more than 2 tokens, If so, remove the first one
         */
        if(isset($_SESSION['request']['token']) && count($_SESSION['request']['token'])){
            foreach($_SESSION['request']['token'] as $id => $arr){                
                if(time() - self::$_token_life > $arr['time']){
                    unset($_SESSION['request']['token'][$id]);
                }
            }
            
            if(count($_SESSION['request']['token']) >= self::$_token_allowance){
                //unset(reset($_SESSION['request']['token']));
                /**
                 * HACK
                 */
                foreach($_SESSION['request']['token'] as $id => $arr){
                    unset($_SESSION['request']['token'][$id]);
                    break;
                }
            }
        }
    }
    
    /**
     * adds a session token to the stack 
     * will automatically pop the oldest token off of the stack
     *
     * @param String $id Form id to be assigned to the token
     * @throws System_Request_Exception if the id is not set
     * @static
     * @return string
     */
    public static function register($id){
        if(!isset($id)){
            throw new System_Request_Exception('You must provide an ID to be used.');
        }
        
        self::checkStack();
        
        $token = System_Registry_Storage::get('Utility_String_Root')->randomString(15)->get();
        
        $_SESSION['request']['token'][$id] = array(
            'value' => $token,
            'time'  => time(),
        );

        return $token;
    }
    
    /**
     * checks the session request token stack against an id and token value
     *
     * @param String $id -- Id to be checked
     * @param String $value -- Value to be checked
     * @static
     * @return boolean -- True if the value exists and it is within the time limit, false otherwise
     */
    public static function check($id, $value){
        $passed = true;
        
        /**
         * check to see that there are session tokens set
         */
        if(!count($_SESSION['request']['token'])){
            $passed = false;
        }
        
        /**
         * check for the id
         */
        if($passed){
            if(!isset($_SESSION['request']['token'][$id])) $passed = false;
        }
        
        /**
         * check the time
         */
        if($passed){            
            if(time() - self::$_token_life > $_SESSION['request']['token'][$id]['time']) $passed = false;
        }
        
        /**
         * check the values
         */
        if($passed){
            if($value != $_SESSION['request']['token'][$id]['value']) $passed = false;
        }

        return $passed;
    }
}