<?php
class UploaderModel extends baseDbModel {  
  
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
  
  public function crop($image,$cx,$cy,$cw,$ch,$w,$h) {
    
    $newCanves 	= imagecreatetruecolor($w,$h);
    imagecopyresampled(
      $newCanves,$image,
      0,0,
      $cx,$cy,
      $w,$h,
      $cw,$ch  
    );
    return $newCanves;
  }
}