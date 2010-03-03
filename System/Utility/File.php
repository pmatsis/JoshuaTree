<?php
/**
 * the base file manipulation utility
 * 
 * @package System
 * @subpackage System_Utility
 * @author Mark Henderson
 */
class System_Utility_File extends System_Utility_Abstract{
    /**
     * property used to hold the path to the file excluding the filename
     * 
     * @var string
     * @access private
     */
    private $_filepath = false;
    
    /**
     * property used to hold the full file name
     * 
     * @var string
     * @access private
     */
    private $_filename = false;
    
    /**
     * property used to store the size of the file
     * 
     * @var integer
     * @access private
     */
    private $_filesize = 0;
    
    /**
     * property stores the file type
     *
     * @var string
     * @access private
     */
    private $_filetype = false;
    
    /**
     * property used to hold name of the file without the extension
     * 
     * @var string
     * @access private
     */
    private $_filename_body = false;
    
    /**
     * property used to hold the file extension
     * 
     * @var string
     * @access private
     */
    private $_file_extension = false;
    
    /**
     * class constructor
     *
     * @param string $full_path -- the full path including the file name
     * @access public
     * @return void
     */
    public function __construct($full_path = false){
        if((bool) $full_path){
            $this->setValue($full_path);
        }
    }
    
    /**
     * returns the full path of the file
     *
     * @access public
     * @return
     */ 
    public function __toString(){
        return $this->getFilePath() .'/'. $this->setFileNameBody() .'.'. $this->getFileExtension();
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
     * overwrite of the setValue method from System_Utility_Abstract
     * this adds file checking and automatic parsing
     *
     * @param string $full_path -- the full path including the file name
     * @access public
     * @throws Exception -- if the file does not exist
     * @return System_Utility_File
     */
    public function setValue($full_path){
        if(!file_exists($full_path)){
            throw new Exception('The file that you are trying to work with does not exist. You passed in '. $full_path);
        }else{
            $this->_value = $full_path;
            $this->defineFile();
        }
        
        return $this;
    }
    
    /**
     * method used to parse the file and set the class properties
     * 
     * @param string $file -- the fullpath including filename of the file
     * @access protected
     * @return System_Utility_File
     */
    protected function defineFile($file = false){
        $file = pick($file, $this->_value);
        $info = pathinfo($file);
        $size = filesize($file);
        
        $this->setFileName($info['filename'] .'.'. $info['extension'])
            ->setFilePath($info['dirname'])
            ->setFileExtension($info['extension'])
            ->setFileNameBody($info['filename'])
            ->setFileSize($size);
        
        $this->_value = $this->getFilePath() .'/'. $this->getFileNameBody() .'.'. $this->getFileExtension();
        
        return $this;
    }
    
    /**
     * sets the {@link _filename} property  
     *
     * @param string $name -- the name of the file
     * @access public
     * @return System_Utility_File
     */
    public function setFileName($name){
        $this->_filename = $name;
        
        return $this;
    }
    
    /**
     * returns the name of the file including extension
     *
     * @access public
     * @return string
     */
    public function getFileName(){
        return $this->_filename;
    }
    
    /**
     * sets the {@link _filepath} property -- the fullpath to the file without the filename
     *
     * @param string $path -- the path to the file
     * @access public
     * @return System_Utility_File
     */
    public function setFilePath($path){
        $this->_filepath = $path;
        
        return $this;
    }
    
    /**
     * returns the file path
     *
     * @access public
     * @return string
     */
    public function getFilePath(){
        return $this->_filepath;
    }
    
    /**
     * sets the {@link _file_extension} property  
     *
     * @param string $extension -- the extension for the file
     * @access public
     * @return System_Utility_File
     */
    public function setFileExtension($extension){
        $this->_file_extension = $extension;
        
        return $this;
    }
    
    /**
     * returns the file extension
     *
     * @access public
     * @return string
     */
    public function getFileExtension(){
        return $this->_file_extension;
    }
    
    /**
     * sets the {@link _filename_body} property  
     *
     * @param string $filename_body -- the name of the file without the extension
     * @access public
     * @return System_Utility_File
     */
    public function setFileNameBody($filename_body){
        $this->_filename_body = $filename_body;
        
        return $this;
    }
    
    /**
     * returns the file body name
     *
     * @access public
     * @return string
     */
    public function getFileNameBody(){
        return $this->_filename_body;
    }
    
    /**
     * sets the file size
     *
     * @param integer $size -- the size of the file
     * @access public
     * @return
     */
    public function setFileSize($size){
        $this->_filesize = $size;
        
        return $this;
    }
    
    /**
     * deletes the file from the system
     *
     * @param string $file -- the full path to the file to be deleted. Optional
     * @access public
     * @throws Exception -- if the file doesn't exist
     * @return boolean
     */
    public function delete($file = false){
        $file = pick($file, $this->_value);
        
        if(!file_exists($file)){
            throw new Exception('The file that you are trying to delete does not exist. File: '. $file);
        }
        
        return unlink($file);
    }
    
    /**
     * renames the file, does not allow for file moving
     *
     * @param string $name -- the new name for the file without the extension
     * @param string $extension -- the extension for the new file. Optional
     * @access public
     * @throws Exception -- if the proposed filename is not valid
     * @return System_Utility_File|boolean
     */
    public function newName($name, $extension = false){
        /**
         * clean the new name
         */
        $ext      = (bool) $extension ? $extension : $this->getFileExtension();
        $new_name = preg_replace('/[!\W]/', '', $name);
        
        if(trim($new_name) === ''){
            throw new Exception('That filename is not a valid name. Filename: '. $new_name);
        }
        
        $new_name = $this->_filepath .'/'. $new_name .'.'. $ext;
        
        if(!rename($this->_value, $new_name)){
            $result = false;
        }else{
            $result = new System_Utility_File($new_name);
        }
        
        return $result;
    }
    
    /**
     * copies the file and gives it a random name. This returns a new instance of System_Utility_File with the copied file as the focus
     * This method does not allow for moving of the file
     *
     * @access public
     * @return System_Utility_File|boolean
     */
    public function copy(){
        $padding = System_Registry_Storage::get('System_Utility_String')->randomString()->get();
        $copy    = $this->getFilePath() .'/'. $this->getFileNameBody() . $padding .'.'. $this->getFileExtension();
        $result  = false;

        if(copy($this->_value, $copy)){
            $result = new System_Utility_File($copy);
        }
        
        return $result;
    }
    
    /**
     * moves the file to a new location. This does not allow for file renaming
     *
     * @param string $location -- the new location for the file without the file name
     * @access public
     * @throws Exception -- if the new location doesnt exist or isnt writable
     * @return System_Utility_File|boolean
     */
    public function move($location){
        if(!is_dir($location)){
            throw new Exception('The location that you are trying to move the file to does not exist. Location: '. $location);
        }elseif(!is_writable($location)){
            throw new Exception('The location that you are trying to move the file to is not writable. Location: '. $location);
        }
        
        $move = $location .'/'. $this->getFileNameBody() . '.'. $this->getFileExtension();

        if(!rename($this->_value, $move)){
            $result = false;
        }else{
            $result = new System_Utility_File($move);
        }
        
        return $result;
    }
}