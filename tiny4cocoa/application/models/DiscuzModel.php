<?php
class DiscuzModel {
  
  protected $path;
  protected $cookie;
  protected $cache;
  protected $auth_key;
  
	public function __construct() {
    
    parent::__construct();
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    
    $this->path = dirname(dirname(dirname(dirname(__FILE__))));
    
    $cookiepre = "wwv_";
    $prelength = strlen($cookiepre);
    foreach($_COOKIE as $key => $val) {
      if(substr($key, 0, $prelength) == $cookiepre) {
        $this->cookie[(substr($key, $prelength))] = MAGIC_QUOTES_GPC ? $val : $this->daddslashes($val);
      }
    }
    $cachelost = (@include $this->path.'/forumdata/cache/cache_settings.php') ? '' : 'settings';
    @extract($_DCACHE['settings']);
    $this->cache = $_DCACHE['settings'];
    $this->auth_key = md5($this->cache['authkey'].$_SERVER['HTTP_USER_AGENT']);
  }
  
  function checklogin() {
    
    //仅仅检查了Cookie没有对session表做任何检查
    list($discuz_pw, $discuz_secques, $discuz_uid) = empty($this->cookie['auth']) ? array('', '', 0) : $this->daddslashes(explode("\t", $this->authcode($this->cookie['auth'], 'DECODE')), 1);
    return $discuz_uid;
  }
  
  
  function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

    $ckey_length = 4;
    $key = md5($key ? $key : $this->auth_key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
      $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
      $j = ($j + $box[$i] + $rndkey[$i]) % 256;
      $tmp = $box[$i];
      $box[$i] = $box[$j];
      $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
      $a = ($a + 1) % 256;
      $j = ($j + $box[$a]) % 256;
      $tmp = $box[$a];
      $box[$a] = $box[$j];
      $box[$j] = $tmp;
      $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
      if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
        return substr($result, 26);
      } else {
        return '';
      }
    } else {
      return $keyc.str_replace('=', '', base64_encode($result));
    }

  }

  function daddslashes($string, $force = 0) {
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    if(!MAGIC_QUOTES_GPC || $force) {
      if(is_array($string)) {
        foreach($string as $key => $val) {
          $string[$key] = $this->daddslashes($val, $force);
        }
      } else {
        $string = addslashes($string);
      }
    }
    return $string;
  }
  
}