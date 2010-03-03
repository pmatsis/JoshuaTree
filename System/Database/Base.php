<?php

/**
* System Abstract DB Class that is extended by each object's DB class
*/
abstract class System_Database_Base implements Iterator{
    /**
     * holds an the unique registration id of the class. This is used when the object is registered with the unit of work class
     *
     * @var string
     * @access private
     */
    private $_registration_id;
    
    /**
     * holds an instance of the validator class
     *
     * @var object
     * @access private
     */
    private $_validator;
    
    /**
     * tells the class to validate the input or not during setvalue() method call
     *
     * @var object
     * @access private
     */
    private $_validate_input = true;
    
    /**
     * holds an instance of the system_log_controller class
     *
     * @var object
     * @access private
     */
    private $_system_log;
    
    /**
     * holds an instance of the System_Database_Word class
     *
     * @var object
     * @access private
     */
    private $_unit_of_work;
    
    /**
     * an array that holds the CRUD settings
     *
     * @var array
     * @access protected
     */
    protected $_crud = array(
        'create'    => '*',
        'read'      => '*',
        'update'    => '*',
        'delete'    => '*'
    );
    
    /**
     * variable to hold the database outline of all fields in that table
     *
     * @var array
     * @access protected
     */
    protected $_data_frame = array();
    
    /**
     * holds the virtual frame created during joins
     *
     * @var array|boolean
     * @access private
     */
    private $_v_data_frame = false; 
     
    /**
     * variable to hold the database value fields for actions in the sql statement
     *
     * @var array
     * @access private
     */
    private $_fields = array();
    
    /**
     * the fields that will go in the select statement
     *
     * @var array
     * @access private
     */
    private $_select_fields = array();
    
    /**
     * holds an assocative array of fields that are required for a type of query action
     * select, update, delete, insert
     *
     * @var array
     * @access private
     */
    private $_required_fields = array(
        'select'    => array(),
        'delete'    => array(),
        'insert'    => array(),
        'update'    => array()
    );
    
    /**
     * holds an array of fields that are marked as index
     * 
     * @var array
     * @access private
     */
    private $_index_fields = array();
    
    /**
     * An instance of the Database from the System_Database_Root Class
     * 
     * @var object|boolean
     * @access private
     */
    private $_db = false;
    
    /**
     * Number of rows affected by the running of a query
     * 
     * @var integer
     * @access private
     */
    private $_affected_rows = 0;
    
    /**
     * Any errors that happen when a query is run
     * 
     * @var ?i'm not sure Pete can you define this
     * @access private
     */
    private $_errors = false;
    
    /**
     * The status returned by the system_database_root
     *
     * @var boolean
     * @access private
     */
    private $_status = false;
    
    /**
     * the table name of the query being affected. Used in building the SQL statement
     *
     * @var string
     * @access private
     */
    private $_table_name;
    
    /**
     * holds the dataset
     *
     * @var array
     * @access private
     */
    public $_data_set = array();
    
    /**
     * sets the current index for the retrieved data array stored in the _data_set property
     *
     * @var integer
     * @access private
     */
    private $_current = 0;
    
    /**
     * holds the first part of the query
     *
     * @var boolean|string
     * @access private
     */
    private $_sql_main  = false;
    
    /**
     * holds the join portion for an sql select statement 
     *
     * @var boolean|string
     * @access private
     */
    private $_sql_join = false;
    
    /**
     * holds the where portion for an sql statement 
     *
     * @var boolean|string
     * @access private
     */
    private $_sql_where = false;
    
    /**
     * holds the order portion for an sql statement 
     *
     * @var boolean|string
     * @access private
     */
    private $_sql_order = false;
    
    /**
     * Holds the group by portion for an sql statement
     * 
     * @var boolean|string
     * @access private
     */
    private $_sql_group = false;
    
    /**
     * holds the limit portion for an sql statement 
     *
     * @var boolean|string
     * @access private
     */
    private $_sql_limit = false;
    
    /**
     * holds the entire query after it is built 
     *
     * @var string
     * @access private
     */
    private $_sql_full  = '';
    
    /**
     * Holds the field that is set as the primary key
     * 
     * @var string
     * @access protected
     */
    protected $_primary_key_field;
     
    /**
     * holds the last insert id
     *
     * @var string
     * @access private
     */
    private $_insert_id  = '';

    /**
     * tells the sql function whether to check for a primary key or not for it's method. 
     *
     * @var boolean
     * @access private
     */
    private $_check_pk  = true;
    
    /**
     * Class Constructor that gets an instance of the System_Database_Root
     * 
     * @access public
     * @return Object instance of this class
     */
    public function __construct(){
        $this->_validator       = System_Registry_Storage::get('System_Utility_Validate');
        $this->_system_log      = System_Registry_Storage::get('System_Log_Controller');
        $this->_unit_of_work    = System_Database_Work::getInstance();
        $this->_registration_id = $this->defineRegistrationId();
        
        $this->setIndexes();

        return $this;
    }
    
    /**
     * when the object is cloned request a new registration id
     *
     * @access public
     * @return object, instance of this class
     */
    public function __clone(){
        $this->_registration_id = $this->defineRegistrationId();
    }
    
    /**
     * gets a database connection if one doesnt exist
     *
     * @access private
     * @return object, instance of this class
     */
    private function connectDB(){
        if(!$this->_db){
            $this->_db  = System_Database_Connection::getInstance()->getConnection('default');
        }
        
        return $this;
    }
    
    /**
     * gets a registration_id
     *
     * @access private
     * @return string
     */
    private function defineRegistrationId(){
        $str_obj = new System_Utility_String();
        
        return $str_obj->randomString(mt_rand(10, 40))->get();
    }
    
    /**
     * abstract method to set the data_frame property
     * 
     * @access protected
     */
    abstract protected function setDataFrame();
    
    /**
     * Sets the _primary_key_field to a string
     * 
     * @access public
     */
    abstract public function setPrimaryKey($pk);
    
    /**
     * gets a connection to the database and sets it to the _db property
     *
     * @param string $connection_name, name of the connection to get
     * @access public
     * @return object, System_Database_Base
     */
    public function getConnection($connection_name){
        $this->_db = System_Database_Connection::getInstance()->getConnection($connection_name);
        
        return $this;
    }    
    
    /**
     * Set the Table Name of the Class
     *
     * @param $table String table name
     * @access public
     * @return Object Instance of this class
     */
    public function setTable($table){
        $name              = $this->quoteFields(array($table));
        $this->_table_name = $name[0];

        return $this;
    }
    
    /**
     * Gets the last insert Id
     * @access public
     * @return mixed
     */
    public function getInsertId(){
        return $this->_insert_id;
    }
    
    /**
     * returns the full query (_sql_full property)
     *
     * @access public
     * @return boolean|string
     */
    public function getQuery(){
        return $this->_sql_full;
    }
    
    /**
     * checks the _crud property against the current action to see if it is allowed
     *
     * @param sring $type, type of action to be checked
     * @access private
     * @return boolean
     */
    private function checkCrud($type){
        $process    = true;
        $check      = isset($this->_curd[$type]) ? $this->_curd[$type] : false;
        
        /**
         * TODO: Remove this block. it doesnt make sense and is never executed
         * if the crud[type] is numeric, do an access level check
         * if the user passes, logs the attemp and the error in the error class to be returned to the user
         */
        if(is_numeric($check)){
            if(!System_Security_Controller::andThis($check, false)){
               
 				/**
                 * log error attempt and set process to false
                 */
                $this->_system_log->setValue('database_access')->write('tried to run an '. $type .' on database '. $this->_table_name .'.');
                $process = false;
            }
        }
        
        return $process;
    }
    
    /**
     * creates a string to insert a value into the database
     *
     * @access public
     * @return Object Instance of this class
     */
    public function insert(){        
        /**
         * if the user is allowed to create
         */
        if($this->checkCrud('create')){
            $fields = array();
            $values = array();
            
            /**
             * set the fields and values for the insert, also check to make sure the the fields being added to the insert statement
             * are actually in the _data_frame property. We don't want to add fields to the insert for an object that was joined. 
             */
            foreach ($this->_fields as $column => $value) {
                if ($column != $this->_primary_key_field && isset($this->_data_frame[$column])) {
                    $fields[] = $column;
                    $values[] = $this->quoteValue($column, $this->scrub($value));
                }
            }
        
            /**
             * check to see if the required fields are met
             * if they are, run the query
             */
			$required_fields = $this->requiredFieldCheck('insert', $fields);
			$required_values = $this->requiredFieldValueCheck('insert', false);
			
            if($required_fields && $required_values){
    			$field_string       = '`'. join('`,`', $fields) .'`'; // TODO: replace with: $this->quoteFileds($fields);
                $value_string       = join(',', $values);
                $this->_sql_main    = 'INSERT INTO ' . $this->_table_name . ' (' . $field_string . ') VALUES (' . $value_string . ')';
            }
        }
        
        return $this;
    }
        
    /**
     * adds backticks to the elements in the array
     *
     * @param array $fields
     * @access private
     * @return array
     */
    private function quoteFields(array $fields = array()){
        $ret = array();
        
        foreach($fields as $field){
            if(preg_match('/\./', $field)){
                $parts = explode('.', $field);
                $ret[] = '`'. implode('`.`', $parts) .'`';
            }else{
                $ret[] = '`'. $field .'`';
            }
        }
        
        return $ret;
    }
    
    /**
     * adds quotes around a vaule based on its datatype defined in the data_frame property
     * this method should only be called from one of the existing CRUD methods as it does not throw an exception for invalid column entries
     *
     * @param String $column the name of the column to check against
     * @param mixed $value the valued to be returned
     * @access private
     * @return mixed string|integer|boolean
     */
    private function quoteValue($column, $value){            
        $column_clean   = $this->extractTableName($column);
        $table          = $column_clean['table'];
        $column         = $column_clean['column'];

        $data_frame = pick($this->_v_data_frame, $this->_data_frame); 
        $type       = strtolower($data_frame[$column]['type']);
        
        if (preg_match('/^\(select/i', $value) || $value === 'NULL') {
            $type = 'sql';
        }

        switch($type){
            case 'integer':
            case 'boolean':
            case 'float':
            case 'sql':
                break;
                
            case 'text':
            case 'string':
            case 'email':
            case 'date':
            case 'datetime':
            case 'password':
                $value = "'". $value ."'";
        }
        
        return $value;
    }
    
    /**
     * checks a val array for the required fields based on query type
     *
     * @param string $type, the type of query that will be checked
     * @param array $fields, 
     * @access private
     * @return boolean
     */
    private function requiredFieldCheck($type, $fields = false){
		$fields     = isset($fields) ? $fields : array_keys($this->_fields);
        $required   = $this->_required_fields[$type];
		$difference = array_diff($required, $fields);
        $error      = System_Registry_Root::getInstance()->getSystemError();
        $passed     = true;
        
        /**
         * if there is no difference, return true
         * else, loop through the difference and add those fields to the system_error object
         */
        if(count($difference)){
            $passed = false;
            
            foreach($difference as $field){
                $proper         = str_replace('_', ' ', $field);
                $this->_errors  = true;
                $error->addEntry($field, $proper . ' is required for an ' . $type . ' statement when using the object associated with '. $this->_table_name .'.');
            }
        }
        
        return $passed;
    }

	/**
	 * Checks the values of the required fields to make sure they are set and not empty
	 * @access private
	 * @return boolean
	 */
	private function requiredFieldValueCheck($type, $fields = false){
		$fields     = ($fields !== false) ? $fields : $this->_fields;
		$required   = $this->_required_fields[$type];
		$error 		= System_Registry_Root::getInstance()->getSystemError();		
		$passed 	= true;

		/**
		 * Checks to see what fields have passed the required state, and then checks to see if their values are set
		 * If they are not set, then they will be added to the error stack. 
		 */
		if (count($required)) {
			foreach ($required as $column) {
				if (isset($fields[$column]) && trim($fields[$column]) === ''){
					$proper         = str_replace('_', ' ', $column);
					$passed         = false;
					$this->_errors  = true;
					$error->addEntry($column, $proper . ' value is required.');
				} 
			}			
		}
		
		return $passed;
	}

    /**
     * Get the primary key field
     * @access public
     * @return mixed
     */
    public function getPrimaryKey(){
        return $this->_primary_key_field;
    }
    
    /**
     * sets the _requried_fields property based on the _data_frame
     *
     * @access private
     * @return object, instance of this class
     */
    public function setRequiredFields(){
        $frame = $this->_data_frame;
        
		foreach($frame as $column => $definition){
            if(isset($definition['required'])){
                $types = explode('|', $definition['required']);
                
                foreach($types as $action){
                    $this->_required_fields[$action][] = $column;
                }
            }
        }
        
        return $this;
    }
    
    /**
     * sets the index fields
     *
     * @access public
     * @return Object System_Database_Base
     */
    public function setIndexes(){
        $frame = pick($this->_v_data_frame, $this->_data_frame);
        
        foreach($frame as $column => $definition){
            if(isset($definition['index']) && $definition['index']){
                $this->_index_fields[] = $column;
            }
        }
        
        return $this;
    }
    
    /**
     * returns the fields marked as index
     *
     * @access public
     * @return array
     */
    public function getIndexes(){
        return $this->_index_fields;
    }
    
    /**
     * Updates a Record in a database table
     *
     * @access public
     * @return object, instance of this class
     */
    public function update(){        
        /**
         * if the user is allowed to update
         */
        if($this->checkCrud('update')){
            //$this->getProcessValue();
            
            $pairs = array();
            
            foreach ($this->_fields as $column => $value) {
                /**
                 * Omits the primary key from being set in the construction of the update clause
                 */
                if ($column != $this->_primary_key_field) {
                    $pairs[] = "`" . $column . "` = " . $this->quoteValue($column, $this->scrub($value));
                }
            }
        
            $pairs_string       = join(', ', $pairs);
            $this->_sql_main    = "UPDATE " . $this->_table_name . " SET $pairs_string";
        
            /**
             * Automatically set the where clause for the update statement if the primary key field is set and the primary key check is set to true.
             */
            if (isset($this->_fields[$this->_primary_key_field]) && $this->_check_pk) {
                $this->_sql_where = '';
                
                $this->where(array($this->_primary_key_field => $this->_fields[$this->_primary_key_field])); 
            }
        }
        
        return $this;
    }

    /**
     * Deletes a Record from a table in the database
     *
     * @access public
     * @return object, instance of this class
     */
    public function delete(){
        /**
         * if the user is allowed to delete
         */
        if($this->checkCrud('delete')){ 
        
            $this->_sql_main = "DELETE FROM " . $this->_table_name;
        
            /**
             * Automatically set the where clause for the delete statement if the primary key field is set.
             */
            if (isset($this->_fields[$this->_primary_key_field]) && $this->_check_pk) {
                $this->where(array($this->_primary_key_field => $this->_fields[$this->_primary_key_field])); 
            }
        }
        
        return $this;
    }
    
    /**
     * Selects a set of fields from the table
     *
     * @access public
     * @return object, instance of this class
     */
    public function select(){        
        /**
         * if the user is allowed to read
         */
        if($this->checkCrud('read')){
            $select     = array();
        
            foreach ($this->_select_fields as $key => $val) {
                $key    = $key;
                $val    = $val;
            
                if($val === '*'){
                    $select[] = $val;
                }elseif(is_numeric($key)){
                    $select[] = $val;
                }elseif(is_string($key)){
                    $select[] = $key ." AS ". $val;
                }
            }
        
            if(is_array($select)){
                $select = join(', ', $select);
            }

            $this->_sql_main = "SELECT $select FROM " . $this->_table_name;
        }
        
        return $this;
    }

    /**
     * Creates the Order By clause
     *
     * @access public
     * @return object, instance of this class
     */
    public function order(array $data = array()){   
        $pairs = array();
        
        foreach ($data as $column => $direction) {
            $pairs[] = $this->scrub($column) . " " . $this->scrub($direction);
        }
        
        $this->_sql_order = 'ORDER BY ' . join(', ', $pairs);
        
        return $this;
    }

    /**
     * creates the Limit for the sql_query
     *
     * @param array $data - an array where the key is the start of the limit and the val is the length
     * @access public
     * @return object, instance of this class
     */
    public function limit(array $data){
        $paris = array();
        
        foreach ($data as $key => $value) {
            $pairs[] = "LIMIT " . $key . ", " . $value;
        }
        
        $this->_sql_limit = $pairs[0];
        
        return $this;
    }

    /**
     * Creates the Where statement
     *
     * @access public
     * @return object, instance of this class
     */
    public function where(array $where, $condition_connector = 'AND'){
        $where_array = array();
        
        foreach ($where as $column => $value) {
            if(!is_array($value)) {
                $value = array($column => $value);
                $column = "=";
            }
            
            /**
             * added hack to allow for in array
             */
            switch(strtolower($column)){
                case 'in':
                    foreach($value as $field => $in_array){
                        $where_part = $field .' '. $column .'(';
                        $val_array = array();
                        
                        foreach($in_array as $in_val){
                            $val_array[] = $this->quoteValue($field, $this->scrub($in_val));
                        }
                        
                        $where_array[] = $field . ' ' . $column . ' ('. implode(',', $val_array) .')';
                    }
                    
                    break;
                
                default:
                    foreach ($value as $field => $field_value) {
                        if(is_array($field_value)){
                            foreach($field_value as $sub_field => $sub_field_value){
                                $where_array[] =  $sub_field . ' ' . $column . ' '.  $this->quoteValue($sub_field, $this->scrub($sub_field_value));
                            }
                        }else{
                            $where_array[] =  $field . ' ' . $column . ' '.  $this->quoteValue($field, $this->scrub($field_value));
                        }
                    }                    
            }
        }
        
        $this->_sql_where .= 'WHERE ' . join(" $condition_connector ", $where_array);
        
        return $this;
    }
    
    /**
     * Manually sets the Where Clause
     * @access public
     * @return object this
     */
    public function setWhere($where){
        if ($where != '') {
            $this->_sql_where = 'WHERE ' . $where;
        }
        
        return $this;
    }
    
    /**
     * This class will create a full-text searching match statement in the where clause. 
     * Passed a string of rows to search against and the search text.
     * @access public
     * @param array $rows An array of the columns that will be searched in the table.
     * @param string $text_search The String that is being searched for. 
     * @return object System_Database_Base
     */
    public function whereMatch($rows, $text_search){
        if (count($rows)) {
            $row_string = implode(', ', $rows);
            
            $sql = 'MATCH(' . $row_string . ')' . " AGAINST ('" . trim($text_search) . "')";
            if (preg_match('/WHERE/' ,$this->_sql_where)) {
                $this->_sql_where .= ' AND ' . $sql;
            } else {
                $this->_sql_where = 'WHERE ' . $sql;
            }
        }
        
        return $this;    
    }
    

    /**
     * Allows joins
     * **UNDER CONSTRUCTION**
     *
     * @access public
     * @return Object, instance of this class
     */
    public function join(array $join = array(), $join_type = 'INNER JOIN'){
        $join_array = array();
        
        if(count($join)){
            /**
             * create the join clause and also append the _data_frame to this object's data frame
             * if the table has a space in it, assume that whatever comes after the space is the table alias
             */
            foreach($join as $table => $clause){
                $alias = '';
                
                if(preg_match('/\s/', $table)){
                    $parts = explode(' ', $table);
                    $table = $parts[0];
                    $alias = $parts[1];
                }
                
                $join_array[]   = '`'. $table .'` '. $alias .' ON '. $clause;
                $object_name    = System_Registry_Storage::get('System_Utility_String')->setValue($table)->underscoreToCamelcase()->get();
                $object         = System_Model_Root::get($object_name, true);

                $this->joinObject($object);
            }
        }
        
        $this->_sql_join .= ' '. $join_type .' '. implode(' '. $join_type .' ', $join_array);
        
        return $this;
    }
        
    /**
     * cleans the data for insertion to the database
     *
     * @param mixed $value, the value to be cleaned
     * @access public
     * @return boolean|string|integer
     */
    public function scrub($value){
        
        return mysql_real_escape_string($value);
    }
    
    /**
     * Builds the SQL statement for the Query and sets it to _sql_main
     * 
     * @access public
     * @return object, instance of this class
     */
    public function build(){
        $this->_sql_full = $this->_sql_main;
        
        /**
         * add the join if the sql statement is a select
         */
        if(preg_match('/^SELECT/i', $this->_sql_full) && $this->_sql_join){
            $this->_sql_full .= ' '. $this->_sql_join;
        }

        if ($this->_sql_where) {
            $this->_sql_full .= ' ' . $this->_sql_where;
        }
        
        if ($this->_sql_group) {
            $this->_sql_full .= ' ' . $this->_sql_group;
        }
        
        if ($this->_sql_order) {
            $this->_sql_full .= ' ' . $this->_sql_order;
        }
        
        if ($this->_sql_limit) {
            $this->_sql_full .= ' ' . $this->_sql_limit;
        }
        
        $this->_sql_full = trim($this->_sql_full);
        
        $this->validateSql();
        
        return $this;
    }
    
    /**
     * Checks a sql statement to see if it's valid
     * 
     * @access public
     * @throws System_Database_Exception
     * @return null
     */
    public function validateSql(){
        if ($this->_sql_main === '') {
            $this->_errors = true;
            throw new System_Database_Exception('Please select a method before building the Query, the sql main statement is not set.');
        } else {
            if (preg_match('/^UPDATE|DELETE/i', $this->_sql_main)) {
                if ($this->_sql_where === ''){
                    $this->_errors = true;
                } 
            }
        }
    }
    
    /**
     * Runs the Query and returns a Data Object
     * 
     * @access public
     * @return Object, instance of this class
     */
    public function run(){
        /**
         * connect to the database if not already connected
         * then build the query
         */
        $this->connectDB()->build();
        
        /**
         * check to see if the _sql_full is a select statement
         * if true, run System_Database_Root::getData 
         * else run System_Database_Root::Query
         */
        if(preg_match('/^SELECT/i', $this->_sql_full)){
            $this->_data_set = $this->_db->runQuery($this->_sql_full)->resultSet();
            $data = pick($this->_data_set[0], array());
            $this->setValue($data, false, false);
        }else{
            $this->_db->runQuery($this->_sql_full);
        }
        
        /**
         * if there was a db error, set the errors property to true
         */
        if($this->_db->error()) $this->_errors = true;
        
        $this->_affected_rows   = $this->_db->getNumRows();
        $this->_insert_id       = $this->_db->getInsertId();
        
        /**
         * if the insert_id property is set then we will rewrite the field[primary_key_field] to it
         */
        if((bool) $this->_insert_id){
            $this->setValue(array(
               $this->_primary_key_field => $this->_insert_id 
            ));
        }
            
        return $this;
    }
    
    /**
     * Run an sql statement
     * @access public
     * @param string $sql - The Sql statement
     * @return mixed
     */
    public function runSql($sql){

        $this->connectDB()->_db->runQuery($sql);

        if($this->_db->error()) $this->_errors = true;
        
        $this->_affected_rows   = $this->_db->getNumRows();
        $this->_insert_id       = $this->_db->getInsertId();
           
        return $this;
    }
    
    /**
     * Gets the Total Number of Rows from the Query
     * 
     * @access public
     * @return mixed
     */
    public function getTotal(){
        return $this->_affected_rows;
    }
    
    /**
     * returns the data_Set property
     *
     * @access public
     * @return array
     */
    public function getAllData(){
        return $this->_data_set;
    }

    /**
     * gets a value from the _frame property array based on key
     *
     * @access public
     * @return mixed String value of key passed in, if asterisk is passed array of values
     */
    public function getValue($key = '*', $allow_html = false){
        $value  = false;
        $run    = true;
        $frame  = pick($this->_v_data_frame, $this->_data_frame);
        
        /**
         * check to see if an asterisk is passed as the key
         * if it is, return all the rows that are available to the current user
         * if not, return the requested row
         */
        if($key === '*'){
            $result = array();
            
            foreach($this->_fields as $key => $val){
                $add = true;
                
                /**
                 * TO DO: implement access level check
                 */
                if($add) $result[$key] = stripslashes($val);
            }
            
            $value = count($result) ? $result : false;
        }else{
            if(isset($frame[$key]['get'])){
                if(!System_Security_Access::andThis($this->_data_frame[$key]['get'])) $run = false;
            }
        
            if($run && array_key_exists($key, $frame) && array_key_exists($key, $this->_fields)){
                $value = stripslashes($this->_fields[$key]);
            }
        }
        
        if (!$allow_html && is_string($value) && !is_int($value)) {
            $value = htmlspecialchars($value);
        }
        
        return $value;
    }
    
    /**
     * returns a string of values based on the indexed fields for the object
     *
     * @access public
     * @return string
     */
    public function getIndexFieldValues(){
        $index_string = array();
        
        foreach($this->_index_fields as $field){
            $index_string[] = $this->getValue($field);
        }
        
        return implode(' ', $index_string);
    }
    
    /**
     * sets the values for the fields property array
     * checks to see if the key exists in either the virtual data frame property (_v_data_frame) if set, or the default data frame (_data_frame)
     * does an access level set if the key has set restrictions
     * validates the input
     *
     * @param array, $val_array array of key value pairs to be set and validated against
     * @param boolean, $mysql_variable_check flag to tell the validator to run the variables through the mysqlVariable validation method. Defaults to false
     * @param boolean $validate_input Flag that tells the method to validate the input or not
     * @access public
     * @return Object, instance of the class
     */
    public function setValue(array $val_array = array(), $mysql_variable_check = false, $validate_input = true){
        $frame = pick($this->_v_data_frame, $this->_data_frame);
        $validation_errors = false;
        
        foreach($val_array as $key => $val){
            $run = true;
            
            /**
             * check to see if the key is in the _frame property array
             * if it is, check the _set_restrictions property array
             */
            if(array_key_exists($key, $frame)){
                if(isset($frame[$key]['set'])){
                    if(!System_Security_Access::andThis($this->_data_frame[$key]['set'])) $run = false;
                }
                
                /**
                 * if the user can add the value the the _fields property
                 * validate the data and if it is valid, add it
                 * else, skip it
                 */
                if($run){
                    /**
                     * get the rules and processed valued based on the type definition
                     * if mysql_variable_check is set, redefine the rules to check against the mysqlVariable method
                     */
                    $rules  = $mysql_variable_check ? array('type' => 'mysqlVariable') : $frame[$key];                    
                    $value  = $this->getProcessValue($frame[$key]['type'], $val);
                    
                    /**
                     * if the rules is an array and is greater than zero
                     * validate the values against the rules
                     * if there are validation errors, set run to false
                     */
                    if($validate_input && is_array($rules) && count($rules)){
                        
						$error  = $this->validate($key, $value, $rules);
						$run    = $error ? false : true;
                        
                        if($error){
                            $validation_errors  = true;
                            $this->_errors      = true;
                        }
                    }

                    if($run){
                        /**
                         * HACK - encodes the password field
                         */
                        if($frame[$key]['type'] == 'password'){
                            $value = System_Registry_Storage::get('System_Utility_String')->setValue($value)->encodePassword()->get();
                        }
                        
                        $this->_fields[$key] = $value;
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * sets the validate_input property
     *
     * @param boolean $validate
     * @access public
     * @return System_Database_Base
     */
    public function setValidateInput($validate){
        $this->_validate_input = (bool) $validate;
        
        return $this;
    }
    
    /**
     * cleans a model out and return a cloned model with only the data rows needed for the page in question.
     * @param integer $start The start number in the array for the first record.
     * @param integer $per_page The number of results to leave.
     * @access public
     * @return object SYSTEM_DATABASE_BASE
     */
    public function pagination($start = 0, $per_page = 20){
        $new_model = clone $this;
        $new_model->_data_set = array_slice($this->_data_set, $start, $per_page);
        $new_model->rewind();
        
        return $new_model;
    }

    
    
    /**
     * gets the value of a certain object based on the type of object it is
     *
     * @access private
     * @return Object System_Database_Base
     */
    private function getProcessValue($type, $value){
        $return = trim($value);
        
        /**
         * loop through the fields property and get the processed value for each value
         */
            if($type){
                switch($type){
                    case 'date':
                    case 'datetime':
                        $method = 'mysql'. $type;
                        $obj    = new System_Utility_Date;
                    
                        if (!$value) {
                            $value = '0000-00-00 00:00:00';
                        }
                    
                        $return = $obj->setValue($value)->$method()->get();
                        break;
                
                    case 'text':
                        /**
                         * just use the default method for now
                         */
                        //$obj    = System_Registry_Storage::get('System_Utility_String');
                        //$return = $object->setValue($value)->get('original');
                        break;
                }
            }
        
        return $return;
    }
    
    /**
     * validates the data passed to the setValue method
     *
     * @access private
     * @return boolean, status of the validation
     */
    private function validate($name, $value, $rules){
       	try {
    		$this->_validator->reset()->setName($name)->setValue($value)->setRules($rules)->validate();
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    
        return $this->_validator->error();
    }
    
    /**
     * sets the select statement fields
     *
     * @param Array $val_array - Array containing the names of the fields to be used in the select statement in this format: array(field, field2). If the array contains key => value paris, then the value becomes the "AS" to the key
     * @access public
     * @return Object, Instance of this class
     */
    public function selectFields(array $val_array = array()){        
        foreach($val_array as $key => $val){
            $run              = true;
            $index            = is_string($key) ? $key : $val;
            $data_frame       = is_array($this->_v_data_frame) ? $this->_v_data_frame : $this->_data_frame;
            $column_aggregate = $this->extractAggregateFunction($index);
            $column_clean     = $this->extractTableName($column_aggregate['column']);
            $table            = $column_clean['table'];
            $column           = $column_clean['column'];
                        
            if(isset($column_aggregate['aggregate']) || (is_string(trim($key)) && is_string(trim($val)))){
                $this->updateDataFrame($val);
                
                $data_frame = is_array($this->_v_data_frame) ? $this->_v_data_frame : $this->_data_frame;
                $column     = $val;
            }
            
            /**
             * check to see if the key is in the _data_frame property array
             * if it is, check the get restrictions for the current key
             */
            if(array_key_exists($column, $data_frame) || $column === '*'){
                if(isset($data_frame[$key]['get'])){
                    if(!System_Security_Access::andThis($data_frame[$key]['get'])) $run = false;
                }
            
                if($run){
                    $this->_select_fields[$key] = $val;
                }
            }
        }

        return $this;
    }
    
    /**
     * Sets the group_by Clause in the sql string. 
     * @access public
     * @return mixed
     */
    public function group(array $data = array()){
        
        if (count($data)) {
            $this->_sql_group = 'GROUP BY ' . join(", ", $data);
        }    
        
        return $this;
        
    }

    /**
     * this class will create a match series for full-text searching. a string of rows to search against and the text itself.
     * @access public
     * @param array $rows An array of the columns that will be searched in the table.
     * @param string $text_search The String that is being searched for. 
     * @param string $as The name of the match results column in the returning data.
     * @return this
     */
    public function selectMatch($rows, $text_search, $as = 'match'){
        if (count($rows)) {
            $row_string = implode(', ', $rows);
            
            $sql = 'MATCH(' . $row_string . ')' . "AGAINST ('" . trim($text_search) . "')";
            $this->_select_fields[$sql] = $as;
        }
        
        return $this;    
    }
    
    /**
     * Joins a data_frame from another object_model to this one in the _v_data_frame property
     *
     * @access public
     * @return Object, instance of this class
     */
    public function joinObject(){
        if(func_num_args()){
            $args       = func_get_args();
            $frame      = $this->getDataFrame(true);
            $count      = 0;
            
            /**
             * get an instance of the object, get its data_frame and merge it with this class' data_frame
             */
            foreach($args as $object){
                if($object->getStatus() !== 'error'){
                    $frame = array_merge($frame, $object->getDataFrame(true));
                    
                    if($object_fields  = $object->getValue('*')){
                        $this->_fields  = array_merge($this->_fields, $object_fields);
                    }
                    
                    $count++;
                }
            }
            
            if($count) $this->_v_data_frame = $frame;
        } 
         
        $this->setRequiredFields()->setIndexes();
        
        return $this;
    }
    
    /**
     * returns the error property
     *
     * @access public
     * @return boolean
     */
    public function error(){
        return $this->_errors;
    }
    
    /**
     * resets the fields property array, clears the _data_set, affected_rows, and sets _current to zero
     *
     * @access public
     * @return Object, instance of the class
     */
    public function reset(){        
        $this->_validator;
        $this->_system_log;
        $this->_crud = array(
            'create'    => '*',
            'read'      => '*',
            'update'    => '*',
            'delete'    => '*'
            );
        $this->_data_frame = array();
        $this->_v_data_frame = false; 
        $this->_fields = array();
        $this->_select_fields = array();
        $this->_required_fields = array(
            'select'    => array(),
            'delete'    => array(),
            'insert'    => array(),
            'update'    => array()
        );
        $this->_index_fields = array();
        $this->_db;
        $this->_affected_rows = 0;
        $this->_errors = null;
        $this->_status = false;
        $this->_table_name;
        $this->_data_set = array();
        $this->_current = 0;
        $this->_sql_main  = false;
        $this->_sql_join = false;
        $this->_sql_where = false;
        $this->_sql_order = false;
        $this->_sql_limit = false;
        $this->_sql_full  = '';
        $this->_primary_key_field;
        $this->_insert_id  = '';
        $this->__construct();
        
        $this->setIndexes();
        
        return $this;
    }
    
    /**
     * returns the object's data frame property
     *
     * @param boolean $virtual -- if the virtual data frame is available, grab it. Defaulted to false
     * @access public
     * @return array
     */
    public function getDataFrame($virtual = false){
        return $virtual ? pick($this->_v_data_frame, $this->_data_frame) : $this->_data_frame;
    }
    
    /**
     * returns the object's virutal data frame property
     *
     * @access public
     * @return array
     */
    public function getVirtualDataFrame(){
        return $this->_v_data_frame;
    }
    
    /**
     * returns the status of the last query ran
     *
     * @access public
     * @return string
     */
    public function getStatus(){
        return $this->_status;
    }
    
    /**
     * returns the insatance of the current database
     *
     * @access public
     * @return object, instance of System_Database_Query
     */
    public function getDb(){
        return $this->_db;
    }
    
    /**
     * returns a row for the _data_set property if it is set
     * sets the current state of the object to the _data_set row
     *
     * @var integer $row
     * @access public
     * @return Mixed Array of data, boolean 
     */
    public function getRow($row){
        $row    = (int) $row;
        $result = false;

        /**
         * if there is data, set the index
         */
        if($this->_affected_rows && $this->valid()){
            $this->_fields  = array();
            $this->_current = $row;

            $this->setValue($this->_data_set[$this->_current], false, $this->_validate_input);
            
            $result = $this->getValue('*'); 
        }

        return $result;
    }
    
    /**
     * sets the current pointer to zero
     * method inherited from the Iterator interface
     *
     * @access public
     * @return Object instance of this class
     */
    public function rewind(){
        $this->_current = 0;
        $this->getRow(0);
        
        
        return $this;
    }
    
    /**
     * returns the key from the _current property
     *
     * @access public
     * @return integer
     */
    public function key(){
        return $this->_current;
    }
    
    /**
     * Checks to see if the current key of the data_set is set
     *
     * @access public
     * @return boolean
     */
    public function current(){
        $return = false;
        
        if($this->valid()){
            $return = true;
        }
        
        return $return;
    }
    
    /**
     * Checks to see if the next key of the data_set is set
     *
     * @access public
     * @return boolean
     */
    public function next(){
        $this->_validate_input = false;
        $return                = false;
        
        if($this->getRow(++$this->_current)){
            $return = true;
        }
        
        return $return;
    }
    
    /**
     * checks to see if the current index of the data_set is valid
     *
     * @access public
     * @return boolean
     */
    public function valid(){
        $return = false;
        
        if(isset($this->_data_set[$this->_current])){
            $return = true;
        }
        
        return $return;
    }
    
    /**
     * resets the keys in the _data_set property to be consecutive digits
     *
     * @access public
     * @return Object System_Database_Base
     */
    public function resetDataSetKeys(){
        $arr = array();
        
        foreach($this->_data_set as $key => $value){
            $arr[] = $value;
        }
        
        $this->_data_set = $arr;
        
        return $this;
    }
    
    /**
     * Checks to see if a record exists in the table. Will return a true if it does exist, false of it doesn't.
     * This function is useful if you want to make sure that a user's input is unique, such as emails on registration. 
     *
     * @param array $vals, the values to be used in the select statement
     * @access public
     * @return boolean
     */
    public function recordExist($field, $value){
        $return = false;
        
        if (isset($field) && isset($value)) {
            $this->selectFields(array($this->_primary_key_field))->where(array($field => $value))->select()->run();
            
            if ($this->_affected_rows > 0) {
                $this->reset();
                $return = true;
            }
        }
        
        $this->reset();
        
        return $return;    
    }
    
    /**
     * returns the registration_id for this class
     *
     * @access public
     * @return string
     */
    public function getRegistrationId(){
        return $this->_registration_id;
    }
    
    /**
     * method used to register this object as new with the unit of work class
     *
     * @param array $dependancies, an array o dependent objects based on the stack that is present in the unit of work class
     * @access public
     * @return object, instance of System_Database_Work
     */
    public function registerNew(array $dependicies = array()){
        return $this->_unit_of_work->register($this, 'new', $dependicies);
    }
    
    /**
     * method used to register this object as dirty with the unit of work class
     *
     * @param array $dependancies, an array o dependent objects based on the stack that is present in the unit of work class
     * @access public
     * @return object, instance of System_Database_Work
     */
    public function registerDirty(array $dependicies = array()){
        return $this->_unit_of_work->register($this, 'dirty', $dependicies);
    }
    
    /**
     * method used to register this object as removed with the unit of work class
     *
     * @param array $dependancies, an array o dependent objects based on the stack that is present in the unit of work class
     * @access public
     * @return object, instance of System_Database_Work
     */
    public function registerRemoved(array $dependicies = array()){
        return $this->_unit_of_work->register($this, 'removed', $dependicies);
    }
    
    /**
     * Returns an array of the table and column for a table.column format to $table => $column.
     * 
     * @access public
     * @param string $column_name The column value being passed.
     * @return array
     */
    public function extractTableName($column_name){
        $return = array('table' => false, 'column' => $column_name);
        
        if (stripos($column_name, '.')){ 
            list($table, $column)   = explode('.', $column_name); 
            $return['table']        = $table;
            $return['column']       = $column;
        }
        
        return $return;
    }
    
    /**
     * extract the aggregate function in the select field if one exists
     * such as count(), max(), min(), etc.
     * @param string $column_name the column name as set in selectFields()
     * @access public
     * @return array the array with the column name and the aggregate function if one is set.
     */
    public function extractAggregateFunction($column_name){

        $return = array('column' => $column_name);
        $aggregates = array(
            'avg', 'bit_and', 'bit_or', 'bit_xor', 'count', 'group_concat', 'datediff', 'ifnull', 'max', 'min', 'std', 'stddev_pop', 'stddev_samp', 'stddev', 'sum', 'var_pop', 'var_samp', 'variance'
        );
        
        if(preg_match_all('/('. implode('|', $aggregates) .')?\((.*?)\)/', $column_name, $matches)){
            $return['aggregate']    = $matches[1][0];
            $return['column']       = $matches[2][0];
        }

        return $return;
    }

    /**
     * Appends a field to the dataframe
     * @access public
     * @param string $column_name The name of the column being updated.
     * @param array $rules the rules for the column
     * @param boolean $overwrite If true will allow the function to overwrite an existing entry in the dataframe
     * @return System_Database_Base
     */
    public function updateDataFrame($column_name, array $rules = array('type' => ''), $overwrite = false){
        $proceed = false;
        
        if ($this->_v_data_frame) {
            $data_frame    =   &$this->_v_data_frame;
        } else {
            $data_frame    =   &$this->_data_frame;
        }
                        
        if (isset($data_frame[$column_name])) {
            if ($overwrite) {
                $proceed = true;
            }
        } else {
            $proceed = true;
        }
        
        if ($proceed) {
            $data_frame[$column_name] = $rules;
        }
                
        return $this;
    }

    
    /**
     * Removes a row of data from the fields property
     *
     * @access public
     * @return System_Database_Base
     */
    public function removeRow(){
        unset($this->_data_set[$this->_current]);

        $this->_affected_rows--;
        
        return $this;
    }
    
    /**
     * rewrites the data_set property
     *
     * @access public
     * @return Object System_Database_Base
     */
    public function resetDataSet(array $data){
        $this->_data_set        = $data;
        $this->_affected_rows   = count($this->_data_set);
        
        return $this;
    }
    
    /**
     * Sets the property $_check_pk = false
     * @access public
     * @return Object System_Database_Base
     */
    public function ignorePk(){
        $this->_check_pk = false;
        
        return $this;
    }
    
    /**
     * converts the complete dataset to an array
     *
     * @param mixed $args -- pass in a comma-dillemented string defining which columns to return
     * @access public
     * @return array
     */
    public function toArray(){
        $ret = array();
        
        if((bool) func_num_args()){
            $fields = func_get_args();
            
            foreach($this->_data_set as $set){
                $row = array();
                
                foreach($set as $field => $value){
                    if(in_array($field, $fields)){
                        $row[$field] = $value;
                    }
                }
                
                $ret[] = $row;
            }
        }else{
            $ret = $this->_data_set;
        }
        
        return $ret;
    }
    
    /**
     * converts the dataset to a key value pair array. The key should be a field that is unique to each row in the result set
     *
     * @param string $key -- the name of the field to get the key from
     * @param string $val -- the name of the field to get the value from
     * @throws Exception -- if either the key or the val does not exist in the dataframe
     * @access public
     * @return array
     */
    public function toKeyValArray($key, $val){
        $ret        = array();
        $data_frame = pick($this->_v_data_frame, $this->_data_frame);
        
        if(!isset($data_frame[$key]) || !isset($data_frame[$val])){
            throw new Exception('You must use two fields that exist in the data frame. You passed in: '. $key .' and '. $val);
        }
        
        foreach($this->_data_set as $set){
            $ret[$set[$key]] = $set[$val];
        }
        
        return $ret;
    }
}