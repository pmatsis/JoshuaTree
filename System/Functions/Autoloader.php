<?php
/**
 * This is the Autoloader for the class names. 
 */
function __autoload($class_name){
   try {
        $filename = classFileExists($class_name);

        if(!$filename) {
            throw new Exception("The file $class_name couldn't be autoloaded.");
        } else {
            require_once $filename;
        }
    } catch(Exception $e) {
        echo $e->getMessage();
    }
}