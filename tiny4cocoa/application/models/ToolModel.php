<?php
class ToolModel {
  
	public function __construct() {
    
  }
  
  public static function summary($text,$length) {
    
    $ret = mb_substr(strip_tags($text),0,$length);
    $ret = str_replace("\\n","",$ret);
    $retarray = explode("。",$ret);
    $count = count($retarray);
    if($count>1){
      $ret = str_replace($retarray[$count-1],"",$ret);
    }
    return $ret;
  }
  
	public static function post($url,$datas) {
    
    $dataArray = array();
    foreach($datas as $key=>$value) { 
      $dataArray[] = $key."=".urlencode($value);
    }
    $fields_string = join("&",$dataArray);
    
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, count($datas));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}
	public static function postJSON($url,$JSON) {
    
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, count(1));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $JSON);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}
  
	public static function getUrl($url) {
    
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}
  
  
  public static function MosaicIp($ip) {
    
    if($ip=="unknown")
      return "游客";
    $numbers = explode(".",$ip);
    $ip = "$numbers[0].$numbers[1].$numbers[2].*";
    return $ip;
  }
  
  public static function toHtml($content) {
    
    $content = stripslashes($content);
    $content = str_replace("\r\n","<br/>",$content);
    $content = str_replace("\n","<br/>",$content);
    $content = str_replace("\r","<br/>",$content);
    return $content;
  }
  
  public function countTime($time)
  {
    $diff = time() - $time;
    if($diff<0) {
      $ret = "（时间不确）";
    }else if($diff<60) {
      $ret = $diff . "秒前";
    } else if($diff<3600) {
      $ret = floor($diff/60) . "分钟前";
    } else if($diff<3600*24) {
      $ret = floor($diff/3600) . "小时前";
    } else if($diff<3600*24*7) {
      $ret = floor($diff/3600/24) . "天前";
    } else if($diff<3600*24*30) {
      $ret = floor($diff/3600/24/7) . "周前";
    } else if($diff<3600*24*30*12) {
      $ret = floor($diff/3600/24/30) . "月前";
    } else {
      $ret = date("Y年m月d日",$time);
    }
    return $ret;
  }
  
  public static function getRealIpAddr()
  {
      if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
      {
        $ip=$_SERVER['HTTP_CLIENT_IP'];
      }
      elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
      {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
      }
      else
      {
        $ip=$_SERVER['REMOTE_ADDR'];
      }
      return $ip;
  }
  
	public static function pageControl($page,$count,$pagesize,$link,$large=1)
	{
    if($large==1)
      $out = "<div class=\"pagination pagination-large\"><ul>";
		else
      $out = "<div class=\"pagination\"><ul>";
    $linemax = 10;
		if($pagesize==0)
			$pagesize = 1;
		$totalpage = ceil($count/$pagesize);
		$begin = floor(($page-1) / $linemax);

		if ($totalpage <= 1) {
			return "";
		}
		if($page>1) {
			$thelink = str_replace("#page#",$page-1,$link);
			$out .= "<li>".$thelink . "«" . "</a></li>";
		} else {
			$thelink = str_replace("#page#",$page-1,$link);
      
			$out .= "<li class=\"disabled\"><a href=\"javascript:\">«</a></li>";
		}

		for ($i=($begin*$linemax)+1; $i<=($begin+1)*$linemax && $i<=$totalpage; $i++) {
			if($page == $i ) {
		    $thelink = str_replace("#page#",$i,$link);
		    $out .= "<li  class=\"disabled\">".$thelink . ($i) . "</a></li>";
      } else {
			    $thelink = str_replace("#page#",$i,$link);
			    $out .= "<li>".$thelink . ($i) . "</a></li>";
	    	}
		}
		if($page<$totalpage) {
		    $thelink = str_replace("#page#",$page+1,$link);
		    $out .= "<li>".$thelink . "»" . "</a></li>";
	    } else {
		    $thelink = str_replace("#page#",$page+1,$link);
		    $out .= "<li  class=\"disabled\"><a href=\"javascript:\">»</a></li>";
	    }
      
    $out .= "</ul></div>";
		return $out;

	}
  
  public static function error_log($str){
    
    echo $str."<br/>";
  }
  
  
  function is_iPhone() {

    $iPhone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
    $iPod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
    $AppleWebKit = strpos($_SERVER['HTTP_USER_AGENT'],"AppleWebKit");
    if($AppleWebKit && ($iPod||$iPhone))
      return 1;
    else
      return 0;
  }
  
  
  function youkuInsert($html) {
    
    if(ToolModel::is_iPhone()) {
      $width="200";
      $height="200";
    }
    else {
      
      $width="510";
      $height="498";
    }
    $html = preg_replace("/<a href=\"http:\/\/v.youku.com\/v_show\/id_(.*)?.html\">(.*)?<\/a>/","\\0<div style=\"text-align:center;\"><iframe width=\"$width\" height=\"$height\" src=\"http://player.youku.com/embed/\\1\" frameborder=0 allowfullscreen></iframe></div>",$html);
    return $html;
  }
  
  function makeDeepDir($path) {
    
    if(file_exists($path))
      return;
    system("mkdir -p $path");
  }
  
  function autoDetect($html) {

    //检测用户名提及
    $html = preg_replace("/\@([^\\s\\n\\r<>\/\'\"@]*)/","@<a href='/user/name/\\1/' target='_blank'>\\1</a>",$html);
    //检测楼层编号
    $html = preg_replace("/([0-9]+)楼/","<a href='#floor\\1'>\\0</a>",$html);
    return $html;
  }
  
  public function detectAtUsers($content) {
    
    preg_match_all("/\@([^\\s\\n\\r<>\/\'\"@]*)/",$content,$match);
    $user = array();
    if(!$match || count($match)==0)
      return $user;
    foreach($match[1] as $m) {
      
      $user[] = $m;
    }
    return array_unique($user);
  }
  
  
  public function createImageFromFile($filename) {
    
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    switch($ext) {
      
      case 'png':
        $image =  imagecreatefrompng($filename);
        break;
      case 'gif':
        $image =  imagecreatefromgif($filename);
        break;      
      case 'jpeg':
      case 'jpg':
        $image = imagecreatefromjpeg($filename);
        break;
      default:
        $image = imagecreatefromjpeg($filename);
    }
    return $image;
  }
}