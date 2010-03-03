<?php
/**
 * Class description
 * 
 * @package 
 * @subpackage 
 * @author Author Name <email>
 */
class System_Search_Controller implements System_Registry_Interface{
    /**
     * the path to the search config file
     */
    const SEARCH_CONFIG_FILE = 'Config/Search.ini';
    
    /**
     * the default search settings
     */
    const DEFAULT_SEARCH_SETTINGS = 'basic';
    
    /**
     * holds the variabable showinf if the search has run or not
     * defaulted to false
     * 
     * @var boolean 
     * @access private
     */
    private $_search_run = false;
    
    /**
     * holds the string to be searched against
     * 
     * @var String
     * @access private
     */
    private $_search_haystack = '';
    
    /**
     * holds the cleaned search haystack
     * 
     * @var string
     * @access private
     */
    private $_search_haystack_cleaned = '';
    
    /**
     * the original search string
     * 
     * @var string
     * @access private
     */
    private $_search_term = '';
    
    /**
     * the original search term cleaned
     * 
     * @var string
     * @access private
     */
    private $_search_term_cleaned = '';
    
    /**
     * the original search term cleaned and then put into an word tree
     * 
     * @var array
     * @access private
     */
    private $_search_term_processed = array();
    
    /**
     * holds the type of search to be loaded from the search.ini
     * 
     * @var string
     * @access private
     */
    private $_search_settings;
    
    /**
     * array used to define the default search group percentages
     * 
     * @var array
     * @access private
     */
    private $_search_groups = array();
    
    /**
     * holds the groupings of the search terms based on their percentage of the original search term
     * 
     * @var Array
     * @access private
     */
    private $_search_terms_grouped = array();
    
    /**
     * an array holding the count for the findings in each group
     * 
     * @var array
     * @access private
     */
    private $_search_terms_found = array();
    
    /**
     * holds an instance of the string object root class
     * 
     * @var System_Search_Controller Utility_String_Root
     * @access private
     */
    private $_string_obj;
    
    /**
     * Class Constructor
     *
     * @param String $search_haystack
     * @param String $search_term
     * @param String $search_settings - the settings in the ini to be used
     * @access public
     * @return null
     */     
    public function __construct($search_haystack = '', $search_term = '', $search_settings = 'basic'){
        $this->setValue(array(
            'haystack'  => $search_haystack,
            'term'      => $search_term,
            'settings'  => $search_settings
        ));
        
        $this->setup();
    }
    
    /**
     * method used to setup the class
     *
     * @access private
     * @return System_Search_Controller 
     */
    private function setup(){
        $this->_string_obj = System_Registry_Storage::get('Utility_String_Root');
    
        $this->getSettings()->defineSearchGroups()->cleanHaystack()->buildTerms()->buildTermGroups();
    }
    
    /**
     * gets the search settings from the System_Registry_Configuration settings object
     *
     * @access private
     * @return System_Search_Controller
     */
    private function getSettings(){
        $file = System_Registry_Configuration::get(System_Registry_Configuration::environment(), 'search');
        
        $this->_search_groups = $file[$this->_search_settings];
        
        return $this;
    }
    
    /**
     * defines the search groups based on the settings file
     *
     * @access private
     * @return System_Search_Controller
     */
    private function defineSearchGroups(){
        foreach($this->_search_groups as $key => $val){
            $this->_search_terms_grouped[$key]  = array();
            $this->_search_terms_found[$key]    = 0;
        }
        
        return $this;
    }
    
    /**
     * cleans the haystack
     *
     * @access private
     * @return System_Search_Controller
     */
    private function cleanHaystack(){
        $this->_search_haystack_cleaned = $this->_string_obj->reset()->setValue(strtolower($this->_search_haystack))->onlyWords()->clean(false)->get();
        
        return $this;
    }
    
    /**
     * builds the search terms and sorts them 
     *
     * @access private
     * @return System_Search_Controller
     */
    private function buildTerms(){
        $this->_search_term_cleaned     = $this->_string_obj->reset()->setValue(strtolower($this->_search_term))->onlyWords()->clean(false)->get();
        $word_tree                      = new Utility_String_WordTree($this->_search_term_cleaned);
        $this->_search_term_processed   = $word_tree->consecutive()->getTree();

        return $this;
    }
    
    /**
     * groups the search terms in the appropriate percentage group
     *
     * @access private
     * @return System_Search_Controller
     */
    private function buildTermGroups(){
        $total_count = count(explode(' ', $this->_search_term_cleaned));
        
        foreach($this->_search_term_processed as $term){
            $parts  = explode(' ', $term);
            $count  = count($parts);
            $place  = ($count / $total_count) * 100;
            
            
            foreach($this->_search_groups as $key => $group){
                if($place >= $key){
                    $this->_search_terms_grouped[$key][] = $term;
                    
                    break;
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Begins the search
     *
     * @access public
     * @return System_Search_Controller
     */
    public function start(){
        $this->_search_run  = true;
        $array_obj          = System_Registry_Storage::get('Utility_Array_Base');
        
        foreach($this->_search_terms_grouped as $key => $grouping){
            if(count($grouping)){
                foreach($grouping as $group){
                    preg_match_all('/'. $group .'/', $this->_search_haystack_cleaned, $matches);
                    
                    /**
                     * remove the empty strings from the $matches array
                     */
                    $matches_array = $array_obj->reset()->setValue($matches[0])->removeEmptyStringVals()->get();
                    
                    $this->_search_terms_found[$key] = count($matches_array);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * retuns the score after the search has run
     *
     * @access public
     * @return Float
     */
    public function getScore(){
        $score = 0;
        
        if($this->_search_run){
            foreach($this->getFoundTotals() as $group => $count){
                $value = $this->_search_groups[$group];
                $score += $value * $count;
            }
        }

        return $score;
    }
    
    /**
     * returns an array containing all of the search terms that were searched
     *
     * @access public
     * @return array
     */
    public function getSearchTerms(){
        return $this->_search_terms_grouped;
    }
    
    /**
     * retuns an array containing the total number of finds in each group
     *
     * @access public
     * @return array
     */
    public function getFoundTotals(){
        return $this->_search_terms_found;
    }
    
    /**
     * sets both the haystack and needle for the search
     *
     * @param array $values - array of params to be set. Key should be either haystack, term, or settings
     * @access public
     * @throws Exception -- if the value argument is not an array
     * @return System_Search_Controller
     */
    public function setValue($values){
        if(!is_array($values)){
            throw new Exception('the value argument must be an array');
        }
        
        foreach($values as $key => $val){
            switch($key){
                case 'haystack':
                    $this->_search_haystack = $val;
                    break;
                    
                case 'term':
                    $this->_search_term = $val;
                    break;
                    
                case 'settings':
                    $this->_search_settings = $val;
                    break;
            }
        }
        
        $this->setup();
        
        return $this;
    }
    
    /**
     * resets the class
     *
     * @access public
     * @return System_Search_Controller
     */
    public function reset(){
        $this->setValue(array(
            'haystack'  => '',
            'term'      => '',
            'settings'  => self::DEFAULT_SEARCH_SETTINGS
        ));
        
        $this->_search_run = false;
        
        $this->setup();
        
        return $this;
    }
}