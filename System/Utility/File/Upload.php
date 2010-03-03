<?php
/**
 * class used to upload files 
 * 
 * @package System_Utility
 * @subpackage System_Utility_File
 * @author Mark Henderson
 */
class System_Utility_File_Upload extends System_Utility_Abstract{    
    /**
     * class constructor
     *
     * @param array $file -- this is the index of the file that is being uploaded
     * @access public
     * @return void
     */
    public function __construct($file){
        $this->setValue($file);
    }
    
    /**
     * abstract method used to define the default settings for this utility
     * 
     * @access protected
     * @return void
     */
    protected function defineSettings(){        
        $this->settings(array());
    }
    
    /**
     * This takes the $_FILE superglobal and sets the {@link _value} property to the temp path
     *
     * @param array $file -- this is the index of the file that is being uploaded
     * @access public
     * @return void
     */
    public function setValue($file){
        $this->_value = $file;
        
        //$this->defineFile($file['name']);
    }
    
    /**
     * saves the file to the defined location
     * returns a System_Utility_File object to allow for further manipulation of the file
     *
     * @access public
     * @return System_Utility_File|boolean
     */
    public function save(){
        $result = $this->validateUpload();
        
        if($result){
            $upload_to = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'upload', 'directory');
        
            if(!$upload_to || !is_dir($upload_to) || !is_writable($upload_to)){
                throw new Exception('The directory that you passed in either doesn\'t exist or isn\'t writable. Directory: '. $upload_to);
            }
        
            $final_file = $upload_to .'/'. $this->_value['name'];

            if(!move_uploaded_file($this->_value['tmp_name'], $final_file)){
                $result = false;
            }else{
                $result = new System_Utility_File($final_file);
            }
        }
        
        return $result;
    }
    
    /**
     * method used to validate the upload against the {@link _settings} property
     *      Possible params:
     *          max_size -- integer
     *          allowed_extensions -- array of extensions
     *
     * @access private
     * @return boolean
     */
    private function validateUpload(){
        $valid = true;
        
        
        return $valid;
    }
}