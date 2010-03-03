<?php
/**
 * Singleton class that holds all of the registry information for when a page is created. 
 *
 * @package System
 * @subpackage System_Registry
 * @author Mark Henderson <emehrkay@gmail.com>
 */
class System_Registry_Root{
    /**
     * The instance of the System_Registry object. (Needed for singleton behavior.) 
     *
     * @var System_Registry The System_Registry object.
     * @access private
     */
    private static $_instance = false;

	/**
	 * Holds the layout you want to use.
	 * TODO: remove
	 * @var integer
	 * @access private
	 */
	private $_layout = 'Default';
    
    /**
	 * Holds the template you want to use.
	 * 
	 * @var string. Defaults to 'Default'
	 * @access private
	 */
	private $_template = 'Default';
    
    /**
     * holds an instance of the _system_error class
     *
     * @var Object
     * @access private
     */
    private $_system_error = false;
    
    /**
     * holds the current site url
     * 
     * @var String
     * @access private
     */
    private $_url = '';
    
    /**
     * holds the head tags for the header
     * 
     * @var String
     * @access private
     */
    private $_head_tags = array();
    
    /**
     * stores an array of css files that is used to build the current page
     *
     * @var Array
     * @access private
     */
    private $_css_files = array();

    /**
     * stores an array of print css files that is used to build the current page
     *
     * @var Array
     * @access private
     */
    private $_print_css_files = array();
	
	/**
	 * holds all of the inline css stylings for the page
	 * 
	 * @var array
	 * @access private
	 */
	private $_css_inline = array();
	
	/**
	 * holds an array of the javascript files
	 * 
 	 * @var array
 	 * @access private
	 */
    private $_js_files = array();
    
    /**
     * holds the inline js for a page
     * 
 	 * @var array
 	 * @access private
     */
    private $_js_inline = array();
    
    /**
     * holds the js load events
     * 
 	 * @var array
 	 * @access private
     */
    private $_js_load_event = array();

    /**
     * The currently requested URL. 
     *
     * @var string The currently requested URL.
     * @access private
     */
    private $_request_url = "";

    /**
     * Values from the currently requested URL. 
     * 
     * @var array Values from the currently requested URL.
     * @access private
     */
    private $_request_vars = array();    
    
    /**
     * sets the page content
     *
     * @var array. Stores the page content in an associative array. the key defines the position where it will be stored in the page
     * @access private
     */
    private $_page_content = array(
        'right'     => '',
        'left'      => '',
        'top'       => '',
        'middle'    => '',
		'bottom'	=> '',
		'spanner' 	=> ''
    );
    
    /**
     * hold the current page title
     * 
     * @var string
     * @access private
     */
    private $_page_title = '';
    
    /**
     * Holds the Active Menu Item
     * 
     * @var string
     * @access private
     */
    private $_menu_item = 'home';

    /**
     * The constructor. Left private by design. Sets the _url property
     * 
     * @access private
     * @return void
     */
    private function __construct() {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'host', 'http_host');
        $this->_url = 'http://'. $host;
        
        /**
         * set the system error property
         */
        $this->getSystemError();
        
        /**
         * set the default css and js files
         */
        $this->setDefaultCss()->setDefaultJs();
    }  
         
    /**
     * sets the default css files
     *
     * @access private
     * @return object, instance of this class
     */
    private function setDefaultCss(){
        $default = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'css', 'default');
        
        $this->addCssFile($default);
        
        return $this;
    }

  	/**
  	 * Sets the default js file
  	 * @access private
  	 * @return mixed
  	 */
  	private function setDefaultJs(){
        $default = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'js', 'default');
        
        $this->addJsFile($default);
  		
		return $this;
  	}
    
    /**
     * resets the registry to a blank state
     *
     * @access public
     * @return object, instance of the registry
     */
    public function reset(){
        $this->_layout          = 'Default';
        $this->_page_title      = '';
        $this->_js_load_event   = array();
        $this->_js_inline       = array();
        $this->_js_files        = array();
        $this->_css_files       = array();
        $this->_css_inline      = array();
        
        /**
         * loop through the conent array and set each iteration to an empty string
         */
        foreach($this->_page_content as $key => $val){
            $this->_page_content[$key] = '';
        }
        
        $this->setDefaultCss();
        
        return $this;
    }

    /**
     * Gets the instance of the registry. 
     * 
     * @static
     * @access public
     * @return System_Registry A System_Registry object.
     */
    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * gets an instance of the system error class, if it isnt defined, sets the _system_error property
     *
     * @access public
     * @return object, instance of this system error class
     */
    public function getSystemError(){
        if(!$this->_system_error){
            $this->_system_error = System_Error_Base::getInstance();
        }
        
        return $this->_system_error;
    }

	/**
	 * Sets the Layout
	 * TODO: remove 
	 * @param mixed $layout -- the layout to be set
	 * @access public
	 * @return System_Registry_Root
	 */
	public function setLayout($layout){
		return $this->setTemplate($layout);
	}
	
	/**
	 * Sets the template
     *
	 * @param mixed $template -- the layout to be set
	 * @access public
	 * @return System_Registry_Root
	 */
	public function setTemplate($template){
	    $this->_template = $template;
	    
	    return $this;
	}

	/**
	 * Returns the layout set in the registry.
	 * TODO: remove
	 * @access public
	 * @return mixed
	 */
	public function setMenuItem($item){
		$this->_menu_item = $item;
		
		return $this;
	}	

    /**
     * returns the template that is current used
     *
     * @access public
     * @return string
     */
    public function getMenuItem(){
        return $this->_menu_item;
    }

	/**
	 * Returns the layout set in the registry.
	 * TODO: remove
	 * @access public
	 * @return mixed
	 */
	public function getLayout(){
		return $this->getTemplate();
	}	

    /**
     * returns the template that is current used
     *
     * @access public
     * @return string
     */
    public function getTemplate(){
        return $this->_template;
    }
    
    /**
     * Adds a Link tag, like a meta or something the the head.
     * 
     * @param string $tag -- tag to be added
     * @access public
     * @return System_Registry_Root
     */
    public function addHeadTag($tag){
        if(is_array($tag)){
            foreach($tag as $header_tag){
                $this->addHeadTag($header_tag);
            }
        }else{
            if($tag != '' && !in_array($tag, $this->_head_tags)) $this->_head_tags[] = $tag;
        }

        return $this;        
    }

    /**
     * Adds a file to the css stack
     * will remove previous reference if the file name already exists in the stack
     *
     * @param $file mixed Could either be an array of css file names or a single file name
     * @param string $media -- the type of media that the css file represents. Defaults to all
     * @access public
     * @return Object, instance of this class
     */
    public function addCssFile($file = '', $media = 'all') {
        if(is_array($file)) {
            foreach($file as $css_file) {
                $this->addCssFile($css_file, $media);
            }
        } else {
            if($file != '') {
                $count = count($this->_css_files);
                
                if (!empty($this->_css_files)) {
                    foreach ($this->_css_files as $css_file) {
                        if (in_array($file, $css_file)) {
                            $in_array = 1; // yes the css file was already included in array
                        } else {
                            $in_array = 0; // no the css file is not already included in array
                        }
                    }
                } else {
                    $in_array = 0; // no the css file is not already included in array
                }

                if ($in_array == 0) {
                    $this->_css_files[] = array('file' => $file, 'media' => $media);
                }
            }
        }
        return $this;
    }

    /**
     * Adds a file to the print css stack
     *
     * @param $file mixed Could either be an array of css file names or a single file name
     * @access public
     * @return Object, instance of this class
     */
    public function addPrintCssFile($file = ''){
        if(is_array($file)){
            foreach($file as $css_file){
                $this->addCssFile($css_file);
            }
        }else{
            if($file != '' && !in_array($file, $this->_print_css_files)) $this->_print_css_files[] = $file;
        }

        return $this;
    }
    
    /**
     * adds a css package
     *
     * @param string $package -- the name of the package to be added
     * @access public
     * @return System_Registry_Root
     */
    public function addCssPackage($package){
        $package = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'css', $package);
        
        $this->addCssFile($package);
        
        return $this;
    }
    
    /**
     * adds inline css to the current inline_css property stack
     *
     * @param string $styling
     * @param string $media -- the type of media that the css file represents. Defaults to all
     * @access public
     * @return object, instance of this class
     */
    public function addInlineCss($styling = '', $media = 'all'){
        if(trim($styling) !== '') $this->_css_inline[] = array('css' => $styling, 'media' => $media);
        
        return $this;
    }
    
    /**
     * adds js file to the stack, checks the stack before adding duplicate files
     *
     * @param $file mixed file Array of files or single file
     * @access public
     * @return class instance
     */
    public function addJsFile($file = ''){
        if(is_array($file)){
            foreach($file as $js_file){
                $this->addJsFile($js_file);
            }
        }else{
            if(trim($file) !== '' && !in_array($file, $this->_js_files)) $this->_js_files[] = $file;
        }
        
        return $this;
    }
    
    /**
     * adds a javascript package
     *
     * @param string $package -- the name of the package to be added
     * @access public
     * @return System_Registry_Root
     */
    public function addJsPackage($package){
        $package = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'js', $package);
        
        $this->addJsFile($package);
        
        return $this;
    }
    
    /**
     * adds inline js to the js_inline property
     *
     * @param $js string of javascript code
     * @access public
     * @return null
     */
    public function addInLineJs($js = ''){
        if(trim($js) !== '') $this->_js_inline[] = $js;
        
        return $this;
    }
    
    /**
     * adds inline js to the js_load_event property
     *
     * @param $js string of javascript code
     * @access public
     * @return Object, Instance of registry
     */
    public function addJsLoadEvent($js = ''){
        if(trim($js) !== '') $this->_js_load_event[] .= $js;
        
        return $this;
    }    
    
    /**
     * adds content to the page_content property
     *
     * @param $content String containing content to be added
     * @param string $where, where to add the page content can be any location, defaults to left
     * @access public
     * @return Object, Instance of registry
     */
    public function addPageContent($content = '', $where = 'left'){
        
        if (!is_array($content)) {
            if(trim($content) !== '') {
                if (isset($this->_page_content[$where])) {
                    $this->_page_content[$where] .= $content;
                } else {
                    $this->_page_content[$where] = $content;
                }
            }
        } else {
            foreach ($content as $where => $value) {
                if(trim($value) !== '') {
                    if(isset($this->_page_content[$where])){
                      $this->_page_content[$where] .= $value;
                    }else{
                      $this->_page_content[$where] = $value;
                    }
                } 
            }
        }
        
        return $this;
    }
    
    /**
     * Adds to or overwrites the page_title property
     *
     * @param $title String, page title to be appended to or overwrite the page_title property
     * @param $overwrite Boolean, flag to overwrite the page title or not
     * @access public
     * @return Object, Instance of registry
     */
    public function setPageTitle($title, $overwrite = false){
       if(trim($title) !== ''){
            if($overwrite){
                $this->_page_title = $title;
            }else{
                $this->_page_title .= $title;
            }
        }
        
        return $this;
    }
    
    /**
     * returns the page title
     *
     * @access public
     * @return String, the current page title
     */
    public function getPageTitle(){
        return $this->_page_title;
    }
    
    /**
     * returns the page_content property value
     *
     * @access public
     * @return array
     */
    public function getPageContent(){
        return $this->_page_content;
    }
    
    /**
     * returns the JavaScript files
     *
     * @access public
     * @return array
     */
    public function getJavaScriptFiles(){
        return $this->_js_files;
    }
    
    /**
     * returns the JavaScript load Events
     *
     * @access public
     * @return array
     */
    public function getJavaScriptLoadEvents(){
        return $this->_js_load_event;
    }
    
    /**
     * returns the inline JavaScript code
     *
     * @access public
     * @return array
     */
    public function getInlineJavaScript(){
        return $this->_js_inline;
    }
    
    /**
     * returns the head tags
     *
     * @access public
     * @return array
     */
    public function getHeadTags(){
        return $this->_head_tags;
    }
    
    /**
     * returns the css fiels
     *
     * @access public
     * @return array
     */
    public function getCssFiles(){
        return $this->_css_files;
    }

    /**
     * returns the print css fields
     *
     * @access public
     * @return array
     */
    public function getPrintCssFiles(){
        return $this->_print_css_files;
    }
    
    /**
     * returns the inline css
     *
     * @access public
     * @return array
     */
    public function getInlineCss(){
        return $this->_css_inline;
    }
}