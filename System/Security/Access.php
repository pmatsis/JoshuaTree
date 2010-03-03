<?php
/**
 * Namespace for access checking
 */
class System_Security_Access{
    /**
     * Checks to see if the user is root and today is sunday
     *
     * @param integer $access_level - the access level to be checked. if omitted, it will be the current logged in user
     * @access public
     * @static
     * @return boolean
     */
    public static function isRootSunday($access_level = false){
        $access_level = (bool) $access_level ? $access_level : $_SESSION['user']['access_level'];
        
        return System_Registry_Storage::get('System_Security_Rules')->setValue($access_level)->isRoot()->isSunday()->process();
    }
    
    /**
     * checks to see if the user is logged in
     *
     * @access public
     * @static
     * @return boolean
     */
    public static function isLoggedIn(){
        $sess_ac = $_SESSION['user']['access_level'];
        
        return $sess_ac > 0 && $sess_ac != 4;
    }
    
    /**
     * checks to see if the user is a root user and checks against another access_level
     *
     * @param integer $access_level - the access level to be checked. if omitted, it will be the current logged in user
     * @param integer $other_access_level - the access level to be checked against the current $access_level param
     * @access public
     * @static
     * @return boolean
     */
    public static function isRootAnd($other_access_level = 0, $access_level = false){
        $rule_obj       = System_Registry_Storage::get('System_Security_Rules');
        $access_level   = (bool) $access_level ? $access_level : $_SESSION['user']['access_level'];
        $is_root        = $rule_obj->setValue($access_level)->isRoot()->process();
        $check          = $rule_obj->reset()->setValue($access_level)->checkAnd($other_access_level)->process();

        return $is_root || $check;
    }
    
    /**
     * checks to see if the user is an expert user (16)
     * 
     * @param integer $access_level - The access level, if left blank, will pull a user's session access var
     * @access public
     * @static
     * @return boolean
     */
    public static function isExpert($access_level = false){
        $access_level = (bool) $access_level ? $access_level : $_SESSION['user']['access_level'];
        
        return System_Registry_Storage::get('System_Security_Rules')->setValue($access_level)->isExpert()->process();
    }
    
    /**
     * checks to see if the user is a root user
     *
     * @param integer $access_level - the access level to be checked. if omitted, it will be the current logged in user
     * @access public
     * @static
     * @return boolean
     */
    public static function isRoot($access_level = false){
        $rule_obj       = System_Registry_Storage::get('System_Security_Rules');
        $access_level   = (bool) $access_level ? $access_level : $_SESSION['user']['access_level'];
        $is_root        = $rule_obj->setValue($access_level)->isRoot()->process();

        return $is_root;
    }
    
    /**
     * Check to see if the user is an admin user in the system
     *
     * @param integer $access_level - the access level to be checked. if omitted, it will be the current logged in user
     * @access public
     * @static
     * @return boolean
     */
    public static function isAdmin($access_level = false){
        $rule_obj       = System_Registry_Storage::get('System_Security_Rules');
        $access_level   = (bool) $access_level ? $access_level : $_SESSION['user']['access_level'];
        $is_admin       = $rule_obj->setValue($access_level)->isAdmin()->process();

        return $is_admin;
    }
}