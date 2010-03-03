<?php
/**
 * Handles all of the queiries for a specific MySQL connection
 *
 * @package DefaultFramework
 * @version $Id: Query.php,v 1.1 2009/09/24 15:00:56 mhenderson Exp $
 */

/**
 * Class to handle all of the queries for a MySQL connection
 * 
 * @package System
 * @subpackage System_Database
 * @author Mark <emehrkay@gmail.com>
 */
class System_Database_Query{
    /**
     * holds the user name for the database connection
     *
     * @var boolean|string
     * @access private
     */
    private $_user_name = false;
    
    /**
     * holds the password for the database connection
     *
     * @var boolean|string
     * @access private
     */
    private $_password = false;
    
    /**
     * holds the database name for the database connection
     *
     * @var boolean|string
     * @access private
     */
    private $_database = false; 
    
    /**
     * holds the host for the database connection
     *
     * @var boolean|string
     * @access private
     */
    private $_host = false;
    
    /**
     * holds the port for the database connection
     *
     * @var boolean|string
     * @access private
     */
    private $_port = false;
    
    /**
     * holds the the database connection
     *
     * @var boolean|string
     * @access private
     */
    private $_connection = false;
    
    /**
     * holds the the result from the query
     *
     * @var boolean|string
     * @access private
     */
    private $_result = false;
    
    /**
     * holds the the error status from the resulting query
     *
     * @var boolean
     * @access private
     */
    private $_error = false;
    
    /**
     * Class Constructor
     *
     * @access public
     * @return void
     */     
    public function __construct($user_name, $password, $database, $host, $port){
        $this->_user_name   = $user_name;
        $this->_password    = $password;
        $this->_database    = $database;
        $this->_host        = $host;
        $this->_port        = $port;
        
        $this->getConnection()->setDatabase();
    }
    
    /**
     * closes the connection
     *
     * @access public
     * @return void
     */
    public function __destruct(){
        $this->close();
    }
    
    /**
     * gets a connection to the database
     *
     * @access private
     * @throws System_Database_Exception, if the db connection fails
     * @return Object, instance of this class
     */
    private function getConnection(){
        /**
         * if the port is set, append it to the host
         */
        $this->_host = (bool) $this->_port ? $this->_host .':'. $this->_port : $this->_host;
        
        if(!$this->_connection = mysql_connect($this->_host, $this->_user_name, $this->_password)){
            throw new System_Database_Exception('There is an error with the database connection - ' . mysql_error());
        }
        
        return $this;
    }
    
    /**
     * sets the database to use with this connections
     *
     * @access private
     * @throws System_Database_Exception, if the selection of the database fails
     * @return object, instance of this class
     */
    private function setDatabase(){
        if(!mysql_select_db($this->_database, $this->_connection)){
            throw new System_Database_Exception('There is an error with selecting the database for this connection - ' . mysql_error());
        }
        
        return $this;
    }
    
    /**
     * resets some of the properties
     *
     * @access public
     * @return object, instance of this class
     */
    public function reset(){
        $this->_result  = false;
        $this->_error   = false;
        
        return $this;
    }
    
    /**
     * runs the given query
     *
     * @access public
     * @throws System_Database_Exception, if there is no query passed
     * @return object, instance of this class
     */
    public function runQuery($query){
        if(!isset($query)){
            throw new System_Database_Exception('You have to set a query.');
        }

        //echo $query . "<br />\n<br />\n";
        $this->reset();

        if(!$this->_result = mysql_query($query, $this->_connection)){
            $this->_error = true;
        }
        
        return $this;
    }
    
    /**
     * takes the mysql result resource property and returns a array of results
     *
     * @access public
     * @return boolean|array
     */
    public function resultSet(){
        $return = false;
        
        if($this->getNumRows() && !$this->_error){
            $return = array();
            
            while ($row = mysql_fetch_assoc($this->_result)){
                $return[] = $row;
            }
        }
        
        return $return;
    }
    
    /**
     * returns the number of rows from the last query
     *
     * @access public
     * @return boolean|integer
     */
    public function getNumRows(){
        return mysql_affected_rows();
    }
    
    /**
     * returns the last insert id from an insert query
     *
     * @access public
     * @return boolean|integer
     */
    public function getInsertId(){
        return mysql_insert_id();
    }
    
    /**
     * returns a query to get the last insert id with a MySQL variable
     *
     * @static
     * @return string
     */
    public static function getInsertIdQuery($variable = '@lid'){
        return 'SELECT '. $variable .':= LAST_INSERT_ID()';
    }
    
    /**
     * returns the error status of this class
     *
     * @access public
     * @return boolean
     */
    public function error(){
        return $this->_error;
    }
    
    /**
     * closes the connection
     *
     * @access public
     * @return void
     */
    public function close(){
        mysql_close($this->_connection);
    }
}