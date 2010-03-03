<?php
/**
 * This class builds a page 
 *
 * @package System
 * @subpackage System_View
 * @author Mark Henderson
 */
class System_View_Root{
    /**
     * holds an instance of the registry
     *
     * @var Object
     * @access protected
     */
    protected $_registry;
    
    /**
     * Class Constructor
     *
     * @access public
     * @return null
     */     
    public function __construct(){
        /**
         * get an instance of the registry
         * and set the network view object
         */
        $this->_registry    = System_Registry_Root::getInstance();        
    }
        
    /**
     * create the JavaScript head content for the current page
     *
     * @access private
     * @return String JavaScript links, and inline code
     */
    public function createJavaScript(){
        /**
         * create the links
         * if the links start with "http://" use that as the source,
         * else the source should start at the js folde
         */
        $links	= '';
        $files  = $this->_registry->getJavaScriptFiles();
		$path   = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'js', 'path');
        
        foreach($files as $file){
            $src = preg_match('/^http\:\/\//i', $file) ? $file : $path . $file;
            
            $links .= '<script type="text/javascript" src="'. $src .'"></script>';
        }
        
        /**
         * create the load event js
         */
        $load_events    = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'js', 'default_events');
        $events         = $this->_registry->getJavaScriptLoadEvents();
        
        foreach($events as $event){
            $load_events .= $event ."\n";
        }
        
        $dom_load = trim($load_events) == '' ? '' : "
            window.addEvent('domready', function(){
                ". $load_events ."
            });
        ";
         
        /**
         * create the inline js, including the load events
         */
        $inline_code    = $dom_load ."\n" . System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'js', 'default_inline') ."\n";
        $inline         = $this->_registry->getInlineJavaScript();
        
        foreach($inline as $code){
            $inline_code .= $code ."\n";
        }
        
        /**
         * return the complete JavaScript Code
         */
        return $links . "<script type=\"text/javascript\">
        /* <![CDATA[ */\n"
            .  $inline_code .
        "/* ]]> */
        </script>
        ";
    }
    
    /**
     * creates the css head content for the current page
     *
     * @access private
     * @return String, css links and inline code
     */
    public function createcss(){
        /**
         * create the css links
         */
        $links              = '';
        $files              = $this->_registry->getCssFiles();
        $print_css_files    = $this->_registry->getPrintCssFiles();
		$path               = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'css', 'path');
        
        /**
         * Add all the CSS Files in the stack
         */
        foreach($files as $def){
            $links .= "<link rel=\"stylesheet\" href=\"". $path . $def['file'] ."\" type=\"text/css\" media=\"". $def['media'] ."\" />\n";
        }
        
        /**
         * Add all the Print Css files if there are any. 
         */
        foreach($print_css_files as $print_file){
            $links .= "<link rel=\"stylesheet\" href=\"". $path . $print_file ."\" type=\"text/css\" media=\"print\" />\n";
        }        
        
        /**
         * links the css fix files for lovely IE!!
         */
        $links .= '
            <!--[if IE 7]>
                <link rel="stylesheet" href="/assets/css/ie7fix.css" media="screen,projection" type="text/css" />
                <link rel="stylesheet" href="/assets/css/ie7fixprint.css" media="print" type="text/css" />
            <![endif]-->
            <!--[if IE 8]>
                <link rel="stylesheet" href="/assets/css/ie8fix.css" media="screen,projection" type="text/css" />
            <![endif]-->
        ';
            
        /**
         * create inline css
         */
        $css   = $this->_registry->getInlinecss();
        $style = '';
        
        if(count($css)){
            $inline = '';
            $count  = count($css);
            $media  = '';
            
            for($x = 0; $x < $count; $x++){
                $style  .= $this->getInlineCssBlock($css[$x]['css'], $css[$x]['media']);        
            }
        }
        
        /**
         * return the complete css code
         */
        return $links . $style;
    }
    
    /**
     * returns a style tag definition
     * 
     * @param string $code -- the css to be used
     * @param string $media -- the media type for the css code
     * @access private
     * @return string 
     */
    private function getInlineCssBlock($code, $media){
        return "
        <style type=\"text/css\" media=\"". $media ."\">
            ". $code ."
        </style>";
    }
}