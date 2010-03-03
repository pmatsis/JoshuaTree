<?php
/**
 * this class acts as a generic command that the request controller an call to do specific tasks
 * 
 * @package System_Request
 * @subpackage System_Request_Command
 * @author Mark
 */
class System_Request_Command_Generic extends System_Request_Command_Abstract{
    /**
     * method that is executed by the System_Request_Controller
     * will automatically fire the event tied to the key data in the data array property
     *
     * @access public
     * @return
     */
    public function doExecute(){
        $data = $this->getData();

        $this->setResponseStatus($data['event']);
    }
}