<?php
/**
 * simple class to hold all of the configuration settings for a project.
 *
 * @package System
 * @subpackage System_Registry
 * @author Mark Henderson
 */
class System_Registry_Configuration{
    /**
     * holds an instance of the class
     * 
     * @param System_Request_Url -- defaults to false
     * @static
     * @access private
     */
    private static $_instance = false;
    
    /**
     * method to hold the current environment 
     * 
     * @param string -- defaults to 'development'
     * @static
     * @access private
     */
    private static $_environment = 'development';
    
    /**
     * array used to hold all of the settings
     * 
     * @var array
     * @static
     * @access private
     */
    private static $_settings = array();
    
    /**
     * class constructor made private to allow for singleton
     * 
     * @access public
     * @return void
     */
    private function __construct(){
		/**
		 * set the default paths for the js and css assets
		 */
		self::set('local', 'js', 'path', '/assets/js/');
		self::set('development', 'js', 'path', '/assets/js/');
		self::set('production', 'js', 'path', '/assets/js/');
		self::set('local', 'css', 'path', '/assets/css/');
		self::set('development', 'css', 'path', '/assets/css/');
		self::set('production', 'css', 'path', '/assets/css/');
	}
    
    /**
     * returns an instance of System_Registry_Configuration
     *
     * @access public
     * @static
     * @return System_Registry_Configuration
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * method used to set the current environment
     *
     * @access public
     * @param string $environment -- the environment variable to be set
     * @static
     * @return void
     */
    public static function setEnvironment($environment){
        self::$_environment = $environment;
    }
    
    /**
     * method used to return the environment variable
     *
     * @access public
     * @static
     * @return string
     */
    public static function environment(){
        return self::$_environment;
    }
    
    /**
     * method used to set a {@link _settings} property
     *
     * @param string $key -- the key to be written in the 
     * @param
     * @access public
     * @static
     * @return void
     */
    public static function set(){
        $arr   = array();
        $args  = func_get_args();
        $count = count($args);

        /**
         * if the argument count is greater than zero
         * process the arguments as keys for the {@link $_settings} property
         * remove the last iteration from the arguments to use as the value
         * set each argument to a variable variable whose value is an array
         */
        if($count > 2){
            $value     = array_pop($args);
            $arg_count = count($args);
            $last      = $arg_count - 1;

            foreach($args as $key){
                $$key = array();
            }

            /**
             * loop through the arguments backwards and set their value to the previous iteration's variable variable
             * if it is the last iteration set it equal to the value
             * if it is the first one, set the arr value to the first iteration's variable variable
             * else, nest the values
             */
            for($i = $last; $i >= 0; $i--){
                if($i === $last){
                    $x            = $$args[$i];
                    $x[$args[$i]] = $value;
                    $$args[$i]    = $x;
                }elseif($i === 0){
                    $x              = $$args[($i + 1)];
                    $arr[$args[$i]] = $x;
                }else{
                    $x            = $$args[$i];
                    $y            = $$args[($i + 1)];
                    $x[$args[$i]] = $y;
                    $$args[$i]    = $x;
                }
            }
        }

        self::$_settings = array_merge_recursive(self::$_settings, $arr);
    }
    
    /**
     * method used to return a specific setting
     * 
     * @param mixed -- pass in the settings that you are looking for in the order in which they would appear in the {@link _settings} property. ie get('database','default','username');
     * @access public
     * @static
     * @return boolean|array|string|integer
     */
    public static function get(){
        $return  = false;
		$args = func_get_args();

		foreach($args as $arg){
			if(isset(self::$_settings[$arg])){
				$return = self::$_settings[$arg];
			}elseif(is_array($return) && isset($return[$arg])){
				$return = $return[$arg];
			}else{
				$return = false;
			}
		}

	    return $return;
    }
}