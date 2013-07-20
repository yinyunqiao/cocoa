<?php
class LoggerModel{
    private $_filePath;
    public function __construct($filePath){
        $this->_filePath = $filePath;
    }

    public function log($content){
        $log = date("Y-m-d H:i:s O") . ", " . $content;
        system("echo \"$log\" >> ". $this->_filePath);
    }
}