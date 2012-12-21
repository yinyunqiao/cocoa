<?php
class ToolModel {
  
	public function __construct() {
    
  }
  
	public static function post($url,$datas) {
    
    $dataArray = array();
    foreach($datas as $key=>$value) { 
      $dataArray[] = $key."=".urlencode($value);
    }
    $fields_string = join("&",$dataArray);
    
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
    // curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, count($datas));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
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
	public static function pageControl($page,$count,$pagesize,$link)
	{
		$out = "";
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
			$out .= $thelink . "&lt;" . "</a> ";
		} else {
			$out .= "&lt; ";
		}

		for ($i=($begin*$linemax)+1; $i<=($begin+1)*$linemax && $i<=$totalpage; $i++) {
			if($page == $i ) {
	        	$out .= $i . " ";
			} else {
			    $thelink = str_replace("#page#",$i,$link);
			    $out .= $thelink . ($i) . "</a> ";
	    	}
		}

		if($page<$totalpage) {
		    $thelink = str_replace("#page#",$page+1,$link);
		    $out .= $thelink . "&gt;" . "</a> ";
	    } else {
	    	$out .= " &gt;";
	    }

		return $out;

	}
}