<?php
class System_Model_Root{
    /**
     * holds and instance of the registry
     * 
     * @var System_Registry_Root
     * @access protected
     */
    protected $_registry;
    
    /**
     * class constructor
     * 
     * @access public
     * @return void
     */
    public function __construct(){
        $this->_registry = System_Registry_Root::getInstance();
    }
    
    /**
     * returns a data model
     *
     * @param Boolean|String $table - Name of the model that will be returned
     * @param Boolean $get_new - If this is set, a new instance of the model will be returned
     * @static
     * @throws System_Request_Exception 
     * @return object, Instance of an object model
     */
    public static function get($table = false, $get_new = false){
        if(!$table || !file_exists('Object/'. $table .'/Model.php')){
            throw new Exception('You must pass in a correct object model. You passed in '. $table);
        }else{
            $model = 'Object_'. $table .'_Model';
            $model = new $model;// : System_Registry_Storage::get($model);
        }
        
        return $model;
    }
    
}