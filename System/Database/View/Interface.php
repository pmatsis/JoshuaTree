<?php
interface System_Database_View_Interface{
    /**
     * method used to set the view's data frame
     * this must be called in the setDataFrame method of the Model
     * 
     * @access public
     * @return Object - The view object model
     */
    public function setViewDataFrame();
}