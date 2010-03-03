<?php
/**
 * This Page holds the view loader for phtml files. This will allow users to parse the files needed.
 *
 * @package DefaultFramework
 * @version V2
 */

/**
 * return html based on data and a phtml snippet being run. 
 * 
 * @package System
 * @subpackage System_View_Load
 * @author Peter Matsis <pmatsis@DefaultFramework.com>
 */
class System_View_Load implements System_Registry_Interface {
    /**
     * Holds the values to be parsed in the phtml file.
     * @var array
     * @access private
     */
    private $_vars = array();

	/**
	 * holds the file's content
	 * 
	 * @var boolean|scalar
	 * @access private
	 */
	private $_output_buffer = false;
    
    /**
     * holds an instance of the system registry
     * 
     * @var System_Registry_Root
     * @access private
     */
    private $_registry;
    
    /**
     * Holds the path for the phtml files
     * @var string
     * @access private
     */
    const PATH = 'View/';
    
    /**
     * Class Constructor
     *
     * @access public
     * @return null
     */     
    public function __construct(){
        $this->_registry = System_Registry_Root::getInstance();
    }
    
    /**
     * Utility method used to parse an phtml file
     *
     * @param string $path -- the path to the phtml file including the file extension
     * @param array $arguments -- an associative array used to pass variable to the phtml file
     * @access protected
     * @return string
     */
    protected function parseHtml($path, array $arguments = array()){
        return System_Registry_Storage::get('System_View_Load')->parse($path, $arguments);
    }
    
    /**
     * Loads a Phtml template file and parses out the html based on the values of those files.
     * if the parse is done from within an already pased file and the vars are left blank, the newest parsed file will share the same lexical scope
     * 
     * @param string $file_name the filename of the phtml file without the path.
     * @param array $vars the vars to be used in the parsed file. 
     * @param boolean $lock_path -- flag stating to use the defined path constant 
     * @access public
     * @return mixed
     */
    public function parse($file_name, array $vars = array(), $lock_path = true){
		if (count($vars)) { 
			$this->setValue($vars);
		} 
		
		/**
		 * load file contents
		 */
		ob_start();
		
		$path = $lock_path ? self::PATH . $file_name : $file_name;
		
		include $path;
		
		$html = ob_get_contents();
		
		ob_end_clean();
		
		return $html;
    }
    
    /**
     * gets the variable
     * @access public
     * @return scaler
     */
    public function get($key){
		$return = false;
	
        if (isset($this->_vars[$key])) {
            $return = $this->_vars[$key];
        }

        return $return;
    }

    /**
     * sets the $_vars property of the class
     * @param string|integer $key The field of the parsed string
     * @param scalar $value The actual value.
     * @access public
     * @return self
     */
    public function setValue($vals){
        foreach ($vals as $key => $value) {
		    $this->_vars[$key] = $value;
		}
        
        return $this;
    }

    /**
     * resets the class
     * @access public
     * @return self
     */
    public function reset(){
        $this->_vars = array();
        
        return $this;
    }
}