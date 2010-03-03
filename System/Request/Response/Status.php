<?php
/**
 * Class used to store the various response states 
 * 
 * @package 
 * @subpackage 
 * @author Mark Henderson
 */
class System_Request_Response_Status{
    /**
     * holds the response state type
     * 
     * @var string
     * @access private
     */
    private $_type;
    
    /**
     * holds the various states for a response status
     *
     * @var array
     * @access private
     */
    private $_states = array(
        'success'   => array(
                            'default' => 'success'
                        ),
        'failure'   => array(
                            'default' => 'failure'
                        ),
        'error'     => array(
                            'default' => 'error'
                        )
    );
    
    /**
     * Class Constructor
     *
     * @param String $type - Type of response states to return
     * @access public
     * @return null
     */     
    public function __construct($type){
        $this->_type = $type;
    }
    
    /**
     * returns the status from the _states property
     *
     * @param String $status - the status to be returned
     * @access public
     * @return string
     */
    public function get($status){
        return $this->_states[$status][$this->_type];
    }
}