<?php
class UploaderModel extends baseDbModel {
  
  
  public $type;
  public $ext;
  public $filename;
  public $image;
  public $width;
  public $height;
	public $quality;
  
  
  public function savetoTempDir($file,$tempPath) {
    
    $seed   = rand(0, 99999);
    $time = time();
    $filename = "$time-$seed";
   
    $ImageType = $_FILES[$file]['type'];
    switch(strtolower($ImageType))
    {
      case 'image/png':
        $ext = "png";
        break;
      case 'image/gif':
        $ext = "gif";
        break;      
      case 'image/jpeg':
      case 'image/pjpeg':
      default:
        $ext = "jpg";
        break;
    }
    
    $path = $tempPath.$filename.".".$ext;
    copy($_FILES['ImageFile']['tmp_name'],$path);
    return $filename.".".$ext;
  }
  
  public function xxxxx() {
    
    parent::__construct();
  	if(!isset($_FILES['ImageFile']) || !is_uploaded_file($_FILES['ImageFile']['tmp_name']))
  			return;
    $filename = NULL;
    $this->type = $_FILES['ImageFile']['type'];
    switch(strtolower($ImageType))
    {
      case 'image/png':
        $this->ext = "png";
        break;
      case 'image/gif':
        $this->ext = "gif";
        break;      
      case 'image/jpeg':
      case 'image/pjpeg':
      default:
        $this->ext = "jpg";
        break;
    }
    
    $seed   = rand(0, 99999);
    $time = time();
    $this->filename = "$time-$seed";
    
    switch(strtolower($this->type))
    {
      case 'image/png':
        $this->image =  imagecreatefrompng($_FILES['ImageFile']['tmp_name']);
        break;
      case 'image/gif':
        $this->image =  imagecreatefromgif($_FILES['ImageFile']['tmp_name']);
        break;      
      case 'image/jpeg':
      case 'image/pjpeg':
        $this->image = imagecreatefromjpeg($_FILES['ImageFile']['tmp_name']);
        break;
      default:
        $this->image = NULL;
    }
    list($this->width,$this->height)=getimagesize($_FILES['ImageFile']['tmp_name']);
    $this->quality = 85;
  }
  
  function cropAndSave($sizes,$path){
    
    foreach($sizes as $size) {
      
      if($size[2]!=-1)
        $newimage = $this->crop($size);
      else
        $newimage = $this->resample($size);
      $destFilename = $path . $this->filename . "_" . $size["0"] . "." . $this->ext;
      if($newimage)
        $this->save($newimage,$destFilename);
    }
    $srcFilename = $path . $this->filename . "." . $this->ext;
    $this->save($this->image,$srcFilename);
  }
  
  function save($image,$filename) {
    
		switch(strtolower($this->type))
		{
			case 'image/png':
				imagepng($image,$filename);
				break;
			case 'image/gif':
				imagegif($image,$filename);
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
			default:
			  imagejpeg($image,$filename,$this->quality);
			  break;
		}
  }
  
  function crop($size) {
    
    $targetWidth = $size["1"];
    $targetHeight = $size["2"];
    $sourceRatio = $this->width/$this->height;
    $targetRatio = $size["1"]/$size["2"];
    
    $lWidth = $this->width;
    $lHeight = $this->height;
    $x_offset = 0;
    $y_offset = 0;
    if($sourceRatio>$targetRatio) {
      
      $lWidth = ceil($targetWidth*$this->height/$targetHeight);
    	$x_offset = ceil(($this->width-$lWidth)/2);
    }else if($sourceRatio<$targetRatio) {
      
      $lHeight = ceil($targetHeight*$this->width/$targetWidth);
    	$y_offset = ceil(($this->height-$lHeight)/2);
    }
    $newCanves 	= imagecreatetruecolor($targetWidth,$targetHeight);
    imagecopyresampled(
      $newCanves,$this->image,
      0,0,
      $x_offset,$y_offset,
      $targetWidth,$targetHeight,
      $lWidth,$lHeight
    );
    return $newCanves;
  }
  
  function resample($size) {
    
    $targetWidth = $size["1"];
    $targetHeight = $this->height*$targetWidth/$this->width;
    $newCanves 	= imagecreatetruecolor($targetWidth,$targetHeight);
    imagecopyresampled(
      $newCanves,$this->image,
      0,0,
      0,0,
      $targetWidth,$targetHeight,
      $this->width,$this->height
    );
    return $newCanves;
  }
}