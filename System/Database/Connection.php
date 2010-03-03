<?php
/**
 * Allows for multiple database connections
 *
 * @package DefaultFramework
 * @version {: }
 */

/**
 * Singleton class to manage mulitple database connections for the application
 * 
 * @package System
 * @subpackage System_Database
 * @author Mark Henderson <emehrkay@gmail.com>
 */
class System_Database_Connection {    
    /**
     * holds an instance of the System_Database_Connection class
     *
     * @var Object|Boolean
     * @access private
     * @static
     */
    private static $_instance = false;
    
    /**
     * assocative array holding the current connections to the database
     *
     * @var array
     * @access private
     */
    public static $_connections = array();
    
    /**
     * Class Constructor
     *
     * @access private
     * @return null
     */     
    private function __construct(){
    }
    
    /**
     * gets an instance of this class
     *
     * @static
     * @access public
     * @return Object, System_Database_Connection
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * creates a new connection the database and stores it in the connections property
     * 
     * @param string $connection_name, name of the connection that you want to store
     * @param string $user_name, user name that is used to create the database connection
     * @param string $password, db password
     * @param string $database, the name of the database to use for the connection
     * @param string $host, the address of the db server 
     * @param boolean|string $port, the port to use when making the database connection. defaults to false
     * @access public
     * @throws System_Database_Exception, if the connection_name is not set
     * @return Object, System_Database_Query instance
     */
    public function createConnection($connection_name, $user_name, $password, $database, $host, $port = false){        
        if(!isset($connection_name)){
            throw new System_Database_Exception('You must provide a connection name. You passed in "'. $connection_name .'"');
        }else{
            $this->_connections[$connection_name] = new System_Database_Query($user_name, $password, $database, $host, $port);
        }
        
        return $this->getConnection($connection_name);
    }
    
    /**
     * gets a connection by name
     *
     * @param string $connection_name, name of the connection that you want to store
     * @param boolean $create, defines if the connection should be created if it doesnt exist
     * @access public
     * @static
     * @throws System_Database_Exception, if the connection_name is not set of if the connection does not exist
     * @return Object, System_Database_Query instance
     */
    public function getConnection($connection_name){        
        if(!isset($connection_name)){
            throw new System_Database_Exception('You must provide a connection name. You passed in "'. $connection_name .'"');
        }elseif(!isset($this->_connections[$connection_name])){
            throw new System_Database_Exception('You must provide a connection name, the one you provided does not exist. You must create the connection first with the createConnection() method. You passed in "'. $connection_name .'"');
        }
        
        return $this->_connections[$connection_name];
    }
}