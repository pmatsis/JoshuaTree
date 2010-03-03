<?php
/**
 * function pick
 * you pass it an unlimited amount of argument and it will return the first one that is defined or the very last argument
 *
 * @return boolean|mixed
 */
function pick(){
    $arguments = func_get_args();
    
    foreach($arguments as $arg){
        if((bool) $arg) return $arg;
    }
    
    return end($arguments);
}

/**
 * Checks to see if the file for a class exists. 
 * 
 * @param string $class_name The class name to check for. Does no case-conversion, so the class you give it is the class it looks for.
 * @return mixed Boolean false if the class doesn't exist, and the path to the file if it does exist.
 */
function classFileExists($class_name) {
    $filename = str_replace('_', '/', $class_name) .'.php';
    
    if(!file_exists($filename)){
        $filename = false;
    }

    return $filename;    
}

/**
 * function that will create a simple CAPTCHA math equation
 *
 * @return string
 */
function mathCap(){
    $count      = 2; //rand(2, 3);
    $type       = array('plus' => '+', 'minus' => '-');
    $range      = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
    $equation   = '';
    
    for($i = 0; $i < $count; $i++){
        $number     = rand(1, 9);
        $pm         = $i < $count - 1 ? array_rand($type) .' ' : '';
        $equation   .= $number .' '. $pm;
    }

    $math = preg_replace(array('/plus/', '/minus/'), array('+', '-'), $equation);
    
    eval('$result = ('.$math.');');
    
    $_SESSION['captcha'] = $result;
    
    return $equation;
}

/**
 * sets the request token
 * 
 * @return string
 */
function requestToken(){
    $_SESSION['request']['token'] = $request_token = System_Registry_Storage::get('Utility_String_Root')->randomString(15)->get();
    
    return $request_token;
}