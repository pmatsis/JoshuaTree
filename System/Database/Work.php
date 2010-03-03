<?php
/**
 * Unit of Work class. Singleton, allows usage across multiple models
 * 
 * @package System
 * @subpackage System_Database
 * @author Mark Henderson <emehrkay@gmail.com>
 */
class System_Database_Work{
    /**
     * constant to hold the prefix for all new variables
     *
     * @var string
     */
    const NEW_PREFIX = 'input_';
    
    /**
     * constant to hold the prefix for all dirty variables
     *
     * @var string
     */
    const DIRTY_PREFIX = 'update_';
    
    /**
     * constant to hold the prefix for all removed variables
     *
     * @var string
     */
    const REMOVED_PREFIX = 'delete_';
    
    /**
     * property used to hold an instance of this class
     *
     * @static
     * @var boolean|object
     * @access private
     */
    private static $_instance = false;
    
    /**
     * property used to hold the order of objets when they are registered
     *
     * @var integer
     * @access private
     */
    private static $_registration_order = 0;
    
    /**
     * property used to hold either the queries to be run or the object
     *
     * @var array
     * @access private
     */
    private $_query_stack = array();
    
    /**
     * property used to hold the new objects added to the work class
     *
     * @var array
     * @access private
     */
    private $_registered_new = array();
    
    /**
     * property used to hold the dirty objects added to the work class
     *
     * @var array
     * @access private
     */
    private $_registered_dirty = array();
    
    /**
     * property used to hold the deleted objects added to the work class
     *
     * @var array
     * @access private
     */
    private $_registered_removed = array();
    
    /**
     * property holds a boolean if there are any errors with the class
     *
     * @var array
     * @access private
     */
    private $_errors = false;
    
    /**
     * property used to hold the dependecies for insert, update, and delete actions
     *
     * @var array
     * @access private
     */
    private $_dependencies_stack = array(
        'new'       => array(),
        'dirty'     => array(),
        'removed'   => array()    
    );
    
    /**
     * this property increments an index everytime a corrosponding action is registered
     *
     * @var array
     * @access private
     */
    private $_variable_stack = array(
        'new'       => 0,
        'dirty'     => 0,
        'removed'   => 0
    );
    
    /**
     * Class Constructor
     *
     * @access private
     * @return Object, System_Database_Unit 
     */     
    private function __construct(){
        return $this;   
    }
    
    /**
     * returns an instance of this class
     *
     * @static
     * @return Object, instance of this class
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * resets the class to its initial state
     *
     * @access public
     * @return object, instance of this class
     */
    public function reset(){
        $this->_variable_stack = array(
            'new'       => 0,
            'dirty'     => 0,
            'removed'   => 0
        );
        
        $this->_dependencies_stack = array(
            'new'       => array(),
            'dirty'     => array(),
            'removed'   => array()    
        );
        
        $this->_errors              = false;
        $this->_registered_new      = array();
        $this->_registered_dirty    = array();
        $this->_registered_removed  = array();
        $this->_query_stack         = array();
        
        return $this;
    }
    
    /**
     * method used to return the appropriate MySQL variable to use 
     *  
     * @param String $type, the type of action that defines which variable stack to check
     * @param Integer $index, the index of the variable to return
     * @access private
     * @throws System_Database_Exception if the variable index does not exist   
     * @return string - A MySQL variable based on the type passed in
     */
    private function getMySQLVariable($type = 'new', $index = 0){
        $return = '';
        
        if($index <= $this->_variable_stack[$type]){
            switch($type){
                case 'new':
                    $return = '@'. self::NEW_PREFIX . $index;
                    break;
                    
                case 'dirty':
                    $return = '@'. self::DIRTY_PREFIX . $index;
                    break;
                    
                case 'removed':
                    $return = '@'. self::REMOVED_PREFIX . $index;
                    break;
            }
        }
        
        return $return;
    }
    
    /**
     * builds the insert queries and joins objects if the key is set in the join_with property
     *
     * @access private
     * @return array
     */
    private function buildNew(){
        $queries = array();

        foreach($this->_registered_new as $key => $set){
            $order  = $set['order'];
            $object = $set['object'];
            
            $this->setDependencyValue($key, 'new');
            
            $this->_query_stack[$order] = array('type' => 'insert', 'query' => $object);

            /**
             * get the last_insert_id query for this insert
             */
            $this->_query_stack[(++$order)] = array('type' => 'query', 'query' => System_Database_Query::getInsertIdQuery('@'. self::NEW_PREFIX . $key));

            if($object->error()) $this->_errors = true;
        }

        return $this;
    }
    
    /**
     * builds the update (dirty) queries for the dirty_objects stored in the class
     *
     * @access private
     * @return System_Database_Work
     */
    private function buildDirty(){
        $queries = array();
        
        foreach($this->_registered_dirty as $key => $set){     
            $order  = $set['order'];
            $object = $set['object'];
                   
            $this->setDependencyValue($key, 'dirty');
            
            $this->_query_stack[$order] = array('type' => 'update', 'query' => $object);
            
            if($object->error()) $this->_errors = true;
        }
        
        return $this;
    }
    
    /**
     * builds the delete (removed) queries for the removed_objects stored in the class
     *
     * @access private
     * @return System_Database_Work
     */
    private function buildRemoved(){
        $queries = array();
        
        foreach($this->_registered_removed as $key => $set){      
            $order  = $set['order'];
            $object = $set['object'];
                       
            $this->setDependencyValue($key, 'removed');
            
            $this->_query_stack[$order] = array('type' => 'delete', 'query' => $object);
            
            if($object->error()) $this->_errors = true;
        }
        
        return $this;
    }
    
    /**
     * checks all of the registered objected against the passed in registration id
     *
     * @param String|Boolean $registration_id - Id of the object that wants to be registred
     * @access private
     * @throws System_Database_Exception if an instance of the object already exists in the stack
     * @return boolean
     */
    private function okToRegister($registration_id = false){
        $result     = true;
        $reg_stack  = array();
        
        if($registration_id){
            $reg_stack  = array_merge($reg_stack, $this->_registered_new, $this->_registered_dirty, $this->_registered_removed);
            
            foreach($reg_stack as $object){
                if($object['object']->getRegistrationId() === $registration_id){
                    $result = false;
                    throw new System_Database_Exception('The object that you are attempting to register already exists in the current registration stack. You must register a new object with the class.');
                }
            }
        }else{
            throw new System_Database_Exception('The object that you are trying to register with the class does not have a registration_id set.');
        }
        
        return $result;
    }

    /**
     * registers an object as new, dirty, or removed with the class
     *
     * @param Object $object, instance of database table model with its data fields already set
     * @param String $type, the type of registration to use. Defaulted to 'new' registration
     * @param Array $dependencies a key value pair array defining which other objects the current object's action is dependent on. array(field_name => object_stack_key (numeric starting at zero))
     * @access public
     * @throws System_Database_Exception when the type of registration is not found
     * @return object, System_Database_Unit
     */
    public function register($object, $type = 'new', array $dependencies = array()){
        /**
         * make sure the given object is an object model and make sure that it doesn't already exist in the registration stacks
         */
        if($object instanceof System_Database_Base && $this->okToRegister($object->getRegistrationId())){
            switch($type){
                /**
                 * alias for insert action on an object
                 */
                case 'new':
                    $this->_registered_new[] = array(
                        'object' => $object,
                        'order'  => $this->_registration_order++
                    );
                    
                    /**
                     * pad the registration order for inserts to allow for the last_insert_id() query
                     */
                    $this->_registration_order++;

                    $this->_variable_stack['new']++;

                    /**
                     * if the dependancies array is set and the current index is not zero, add it to the _dependencies_stack property
                     */
                    $index = $count = count($this->_registered_new) - 1;
                    $dependency_type = 'new';
                    
                    break;

                /**
                 * alias for update action on an object
                 */
                case 'dirty':
                    $this->_registered_dirty[] = array(
                        'object' => $object,
                        'order'  => $this->_registration_order++
                    );

                    $this->_variable_stack['dirty']++;
                    
                    /**
                     * if the dependancies array is set and the current index is greater than -1, 
                     * add it to the _dependencies_stack property
                     * set the count to the length of the registered_dirty property array
                     */
                    $index  = count($this->_registered_new) - 1;
                    $count  = count($this->_registered_dirty) - 1;
                    $dependency_type = 'dirty';
                    
                    break;

                /**
                 * alias for delete action on an object
                 */
                case 'removed':
                    $this->_registered_removed[] = array(
                        'object' => $object,
                        'order'  => $this->_registration_order++
                    );

                    $this->_variable_stack['removed']++;
                    
                    /**
                     * if the dependancies array is set and the current index is greater than -1, 
                     * add it to the _dependencies_stack property
                     * set the count to the length of the registered_dirty property array
                     */
                    $index  = count($this->_registered_new) - 1;
                    $count  = count($this->_registered_removed) - 1;
                    $dependency_type = 'removed';
                    
                    break;

                default:
                    throw new System_Database_Exception('You must pass an existing object type to the register method. You passed "'. $type .'"');
            }
            
            /**
             * add the dependencies to the appropriate stack
             */
            if(count($dependencies) && $index > -1){
                $this->_dependencies_stack[$dependency_type][$count] = $dependencies;
            }
        }else{
            throw new System_Database_Exception('You must pass a proper model instance to the registration method.');
        }
        
        return $this;
    }
    
    /**
     * creates the commit query based on the object registrations
     *
     * @access public
     * @return boolean status of the transaction
     */
    public function commit(){
        /**
         * gather all of the queries
         */
        $this->buildNew()->buildDirty()->buildRemoved();
        
        $committed          = false;
        $count              = count($this->_query_stack);
        $transaction_stack  = $this->_query_stack;
        
        ksort($transaction_stack);
        
        /**
         * get the current db being used
         */
        $db = System_Database_Connection::getInstance()->getConnection('default');
        
        /**
         * check for errors, if so set the return to false and do not process any queries
         * check to see if the transaction_stack has any queries. if none set the return to false and process none
         * if no errors, loop through the transaction_stack and run each query
         * if the query fails, run the ROLLBACK query, set the return to false, and break the loop
         * if all of the queries execute fine, run a COMMIT query to commit the transaction
         * run the reset method no matter what happens
         */
        if($this->_errors || !$count){
            $committed = false;
        }else{
            $db->runQuery('START TRANSACTION');
            
            $iter = 0;
            
            foreach($transaction_stack as $group){
                $rollback   = false;
                $query      = $group['query'];
                $query_type = $group['type'];
                
                if(is_object($query)){                        
                    $query->$query_type()->run();

                    if($query->error()){
                        $rollback = true;
                    } 
                     
                }elseif(is_string($query)){
                    $db->runQuery($query);
                    
                    if($db->error()){
                        $rollback = true;
                    }
                }

                if($rollback){
                    $db->runQuery('ROLLBACK');
                    $committed = false;
                    break;
                }else{
                    $iter++;
                }                
            }

            if($iter == $count){
                $db->runQuery('COMMIT');
                $committed = true;
            }
        }
        
        $this->reset();

        return $committed;
    }
    
    /**
     * check to see if the object has any new dependencies set for the current key
     * if so, get the variable for this index and set the appropriate value using the setValue method for the object
     * 
     * @access private
     * @param string $key Is the registration id of the unit of work stack.
     * @param string $type Passes the type of work to be done, either [new, dirty, removed]
     * @return Object, instance of this class
     */
    private function setDependencyValue($key, $type){
        switch ($type) {
            case 'new':
                $object = $this->_registered_new[$key]['object'];
                break;
            
            case 'dirty':
                $object = $this->_registered_dirty[$key]['object'];
                break;
                
            case 'removed':
                $object = $this->_registered_removed[$key]['object'];
                break;
        }

        if(isset($this->_dependencies_stack[$type][$key])){
            $dependencies = $this->_dependencies_stack[$type][$key];
            
            foreach($dependencies as $field_key => $object_key){
                if(!is_numeric($object_key)){
                    $object_key = $key - 1;
                }
                
                $object->setValue(array(
                   $field_key => $this->getMySQLVariable($type, $object_key)
                ), true);
            }
        }
        
        return $this;
    }
    
    /**
     * method used to return the order of registration for a certain object 
     *
     * @param string $registration_id -- the id to search for in the stack
     * @access public
     * @return boolean/array
     */
    public function getRegistrationPlace($registration_id){
        $found = false;
        $registration_stacks  = array(
            'dirty'   => $this->_registered_dirty, 
            'new'     =>$this->_registered_new, 
            'removed' => $this->_registered_removed
        );
        
        foreach($registration_stacks as $type => $objs){
            foreach($objs as $key => $obj){
                if($obj['object']->getRegistrationId() === $registration_id){
                    $found = array(
                        'key'  => $key,
                        'type' => $type
                    );
                    break 2;
                }
            }
        }
        
        return $found;
    }
}