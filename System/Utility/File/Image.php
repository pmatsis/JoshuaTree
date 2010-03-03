<?php
/**
 * this class handles image file manipulation
 * 
 * @package System_Utility
 * @subpackage System_Utility_File
 * @author Mark Henderson
 */
class System_Utility_File_Image extends System_Utility_Abstract{
    /**
     * holds a reference to the image
     * 
     * @var resource
     * @access private
     */
    private $_image = false;
    
    /**
     * holds the image information
     * 
     * @var array
     * @access private
     */
    private $_image_info = array();
    
    /**
     * holds the type of image being manipulated
     * 
     * @var string
     * @access private
     */
    private $_image_type = false;
    
    /**
     * class constructor
     *
     * @param System_File_Utility $file_obj
     * @access public
     * @return void
     */
    public function __construct($file_obj = false){
        if((bool) $file_obj){
            $this->setValue($file_obj);
        }
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
     * overwrite of the setvalue method in abstract
     *
     * @param System_File_Utility $file_obj
     * @access public
     * @throws Exception -- if the param is not an instance of System_File_Utility
     * @return void
     */
    public function setValue($file_obj){
        if(!($file_obj instanceof System_Utility_File)){
            throw new Exception('You must pass an instance of System_Utility_File to use the System_Utility_File_Image class.');
        }
        
        $this->_value = $file_obj;
        
        $this->define();
    }
    
    /**
     * defines the image and image type
     * TODO: add gd-created image support for the $file argument
     *
     * @param string $file -- the fullpath to the file to be loaded. Optional
     * @access protected
     * @return System_Utility_File_Image
     */
    protected function define($file = false){
        $file              = pick($file, $this->_value->get('original'));
        $this->_image_info = getimagesize($file);
        $this->_image_type = $mime = $this->_image_info['mime']; 

        if(preg_match('/jpg|jpeg/i', $mime)){
            $this->_image = imagecreatefromjpeg($file);
        }elseif(preg_match('/gif/i', $mime)){
            $this->_image = imagecreatefromgif($file);                
        }elseif(preg_match('/png/i', $mime)){
            $this->_image = imagecreatefrompng($file);
        }elseif(preg_match('/bmp/i', $mime)){
            $this->_image = imagecreatefromwbmp($file);
        }
        
        return $this;
    }
    
    /**
     * saves the image file. Does not allow for renaming or moving
     *
     * @param boolean $stream -- flag that says to stream or save the file
     * @access public
     * @return System_Utility_File
     */
    public function save($stream = false){
        $save = !$stream ? $this->_value->getFilePath() .'/'. $this->_value->getFileNameBody() .'.'. $this->_value->getFileExtension() : false;
        $mime = $this->_image_type; 
        
        if(preg_match('/jpg|jpeg/i', $mime)){
            imagejpeg($this->_image, $save);
        }elseif(preg_match('/gif/i', $mime)){
            imagegif($this->_image, $save);
        }elseif(preg_match('/png/i', $mime)){
            imagealphablending($this->_image, false);
            imagesavealpha($this->_image, true);
            imagepng($this->_image, $save);
        }elseif(preg_match('/bmp/i', $mime)){
            imagewbmp($this->_image, $save);
        }
        
        return new System_Utility_File($save);
    }

    /**
     * method used to return the width of the image
     * 
     * @access public
     * @return integer
     */
    public function getWidth(){
        return imagesx($this->_image);
    }

    /**
     * method used to return the height of the image
     * 
     * @access public
     * @return integer
     */
    public function getHeight(){
        return imagesy($this->_image);
    }

    /**
     * method used to resize the image based on a given height
     * 
     * @param integer $height -- the height to set the image
     * @access public
     * @return System_Utility_File_Image
     */
    public function toHeight($height){
        $height = abs($height);
        $ratio  = $height / $this->getHeight();
        $width  = $this->getWidth() * $ratio;

        return $this->resize($width,$height);
    }

    /**
     * method used to resize the image based on width
     * 
     * @param integer $width -- the width to set the image
     * @access public
     * @return System_Utility_File_Image
     */
    public function toWidth($width){
        $width  = abs($width);
        $ratio  = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;

        return $this->resize($width,$height);
    }

    /**
     * method used to easily scale an image
     * 
     * @param integer $scale -- the percentage to scale the image by
     * @access public
     * @return System_Utility_File_Image
     */
    public function scale($scale){
        $scale  = abs($scale);
        $width  = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100; 

        return $this->resize($width,$height);
    }
    
    /**
     * this method resizes the image
     * 
     * @param integer $width -- the width to set the image
     * @param integer $height -- the height to set the image
     * @access public
     * @return System_Utility_File_Image
     */
    public function resize($width,$height) {
        $width     = abs($width);
        $height    = abs($height);
        $new_image = imagecreatetruecolor($width, $height);

        /**
         * if the image is a png or gif, save the transparency
         */
        if(preg_match('/gif|png/i', $this->_image_type)) {
            $trnprt_indx = imagecolortransparent($this->_image);

            if ($trnprt_indx >= 0) {
                $trnprt_color = imagecolorsforindex($this->_image, $trnprt_indx);
                $trnprt_indx  = imagecolorallocate($new_image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

                imagefill($new_image, 0, 0, $trnprt_indx);
                imagecolortransparent($new_image, $trnprt_indx);
            }elseif(preg_match('/png/i', $this->_image_type)) {
                imagealphablending($new_image, false);
                
                $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
                
                imagefill($new_image, 0, 0, $color);
                imagesavealpha($new_image, true);
            }
        }

        imagecopyresampled($new_image, $this->_image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        $this->_image = $new_image;

        return $this;
    }           
}