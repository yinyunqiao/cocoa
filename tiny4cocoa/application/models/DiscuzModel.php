<?php
function tpl_codedisp($code) {
	return '<div class="blockcode"><code id="code">'.$code.'</code></div>';
}

function codedisp($code) {
  
	$code = dhtmlspecialchars(str_replace('\\"', '"', preg_replace("/^[\n\r]*(.+?)[\n\r]*$/is", "\\1", $code)));
  $code ="<code>".$code."</code>";
  return $code;
}


function parseattach($aid) {
  
  $threadModel = new ThreadModel();
  $attach = $threadModel->attach($aid);
  if($attach["isimage"]==1)
    $return = "<img src='/attachments/$attach[attachment]'/>";
  else
    $return = "<p>附件:<a href='/attachments/$attach[attachment]'>$attach[filename]</a></p>";
  return $return;
}

function parseemail($email, $text) {
	if(!$email && preg_match("/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i", $text, $matches)) {
		$email = trim($matches[0]);
		return '<a href="mailto:'.$email.'">'.$email.'</a>';
	} else {
		return '<a href="mailto:'.substr($email, 1).'">'.$text.'</a>';
	}
}

function bbcodeurl($url, $tags) {
	if(!preg_match("/<.+?>/s", $url)) {
		if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://'))) {
			$url = 'http://'.$url;
		}
		return str_replace(array('submit', 'logging.php'), array('', ''), sprintf($tags, $url, addslashes($url)));
	} else {
		return '&nbsp;'.$url;
	}
}

function tpl_quote() {
	return '<div class="quote"><blockquote>\\1</blockquote></div>';
}

function parseurl($url, $text) {
	if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
		$url = $matches[0];
		$length = 65;
		if(strlen($url) > $length) {
			$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		return '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
	} else {
		$url = substr($url, 1);
		if(substr(strtolower($url), 0, 4) == 'www.') {
			$url = 'http://'.$url;
		}
		return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
	}
}
function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
		//$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

class DiscuzModel {
  
  protected $path;
  protected $cookie;
  protected $cache;
  protected $auth_key;
  
	public function __construct() {
    
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
  
  
  function logout() {
    setcookie("wwv_auth","",time()-3600*24,"/");
    setcookie("wwv_cookietime","",time()-3600*24,"/");
    setcookie("wwv_sid","",time()-3600*24,"/");
    header("location:/");
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
  
  public static function get_avatar_path($uid,$size) {
    
  	$uid = abs(intval($uid));
  	$uid = sprintf("%09d", $uid);
  	$dir1 = substr($uid, 0, 3);
  	$dir2 = substr($uid, 3, 2);
  	$dir3 = substr($uid, 5, 2);
    // $typeadd = $type == 'real' ? '_real' : '';
  	$avpath = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
    $pathadd = "/var/www/cocoa/uc_server/data/avatar/";
    return $pathadd.$avpath;
  }
  public static function get_avatar($uid, $size = 'middle', $type = '') {
	
  	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
  	$uid = abs(intval($uid));
  	$uid = sprintf("%09d", $uid);
  	$dir1 = substr($uid, 0, 3);
  	$dir2 = substr($uid, 3, 2);
  	$dir3 = substr($uid, 5, 2);
  	$typeadd = $type == 'real' ? '_real' : '';
  	$avpath = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
  	if(file_exists("/var/www/cocoa/uc_server/data/avatar/" . $avpath))
  		$ret = "/uc_server/data/avatar/" . $avpath;
  	else
  		$ret =  "/uc_server/images/noavatar_$size.gif";
  	return $ret;
  }
  
  static function discuzcode($message, $smileyoff=1, $bbcodeoff=0, $htmlon = 0, $allowsmilies = 1, $allowbbcode = 1, $allowimgcode = 1, $allowhtml = 0, $jammer = 0, $parsetype = '0', $authorid = '0', $allowmediacode = '0', $pid = 0) {
  	global $discuzcodes, $credits, $tid, $discuz_uid, $highlight, $maxsmilies, $db, $tablepre, $hideattach, $allowattachurl;


  	$msglower = strtolower($message);


  	if(!$htmlon) {
  		$message = $jammer ? preg_replace("/\r\n|\n|\r/e", "jammer()", dhtmlspecialchars($message)) : dhtmlspecialchars($message);
  	}

  	if(!$smileyoff && $allowsmilies && !empty($GLOBALS['_DCACHE']['smilies']) && is_array($GLOBALS['_DCACHE']['smilies'])) {
  		if(!$discuzcodes['smiliesreplaced']) {
  			foreach($GLOBALS['_DCACHE']['smilies']['replacearray'] AS $key => $smiley) {
  				$GLOBALS['_DCACHE']['smilies']['replacearray'][$key] = '<img src="images/smilies/'.$GLOBALS['_DCACHE']['smileytypes'][$GLOBALS['_DCACHE']['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'" smilieid="'.$key.'" border="0" alt="" />';
  			}
  			$discuzcodes['smiliesreplaced'] = 1;
  		}
  		$message = preg_replace($GLOBALS['_DCACHE']['smilies']['searcharray'], $GLOBALS['_DCACHE']['smilies']['replacearray'], $message, $maxsmilies);
  	}

      $message = preg_replace("/\[attach\](\d+)\[\/attach\]/ie",
              "parseattach('\\1')", $message);
      
  	if(!$bbcodeoff && $allowbbcode) {
  		if(strpos($msglower, '[/url]') !== FALSE) {
  			$message = preg_replace("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.|mailto:)([^\s\[\"']+?))?\](.+?)\[\/url\]/ies", "parseurl('\\1', '\\5')", $message);
  		}
  		if(strpos($msglower, '[/email]') !== FALSE) {
  			$message = preg_replace("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/ies", "parseemail('\\1', '\\4')", $message);
  		}
  		$message = str_replace(array(
  			'[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
  			'[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
  			'[list=A]', '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
  		), array(
  			'</font>', '</font>', '</font>', '</p>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="solidline" />', '</p>', '<i class="pstatus">', '<i>',
  			'</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
  			'<ul type="A" class="litype_3">', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
  		), preg_replace(array(
  			"/\[color=([#\w]+?)\]/i",
  			"/\[size=(\d+?)\]/i",
  			"/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i",
  			"/\[font=([^\[\<]+?)\]/i",
  			"/\[align=(left|center|right)\]/i",
  			"/\[p=(\d{1,2}), (\d{1,2}), (left|center|right)\]/i",
  			"/\[float=(left|right)\]/i"

  		), array(
  			"<font color=\"\\1\">",
  			"<font size=\"\\1\">",
  			"<font style=\"font-size: \\1\">",
  			"<font face=\"\\1 \">",
  			"<p align=\"\\1\">",
  			"<p style=\"line-height: \\1px; text-indent: \\2em; text-align: \\3;\">",
  			"<span style=\"float: \\1;\">"
  		), $message));
  		$nest = 0;
  		while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
  			$message = preg_replace("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies", "parsetable('\\1', '\\2', '\\3')", $message);
  			if(++$nest > 4) break;
  		}

  		if($parsetype != 1) {
  			if(strpos($msglower, '[/quote]') !== FALSE) {
  				$message = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", tpl_quote(), $message);
  			}
  			if(strpos($msglower, '[/free]') !== FALSE) {
  				$message = preg_replace("/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is", tpl_free(), $message);
  			}
  		}
  		if(strpos($msglower, '[/media]') !== FALSE) {
  			$message = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", $allowmediacode ? "parsemedia('\\1', '\\2')" : "bbcodeurl('\\2', '<a href=\"%s\" target=\"_blank\">%s</a>')", $message);
  		}
  		if($allowmediacode && strpos($msglower, '[/audio]') !== FALSE) {
  			$message = preg_replace("/\[audio\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/ies", "parseaudio('\\1')", $message);
  		}
  			$message = preg_replace("/\[flash\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", "<script type=\"text/javascript\" reload=\"1\">document.write(AC_FL_RunContent('width', '550', 'height', '400', 'allowNetworking', 'internal', 'allowScriptAccess', 'never', 'src', '\\1', 'quality', 'high', 'bgcolor', '#ffffff', 'wmode', 'transparent', 'allowfullscreen', 'true'));</script>", $message);
  		if($parsetype != 1 && $allowbbcode == 2 && $GLOBALS['_DCACHE']['bbcodes']) {
  			$message = preg_replace($GLOBALS['_DCACHE']['bbcodes']['searcharray'], $GLOBALS['_DCACHE']['bbcodes']['replacearray'], $message);
  		}
  		if($parsetype != 1 && strpos($msglower, '[/hide]') !== FALSE) {
  			if(strpos($msglower, '[hide]') !== FALSE) {
  				if($GLOBALS['authorreplyexist'] === '') {
  					$GLOBALS['authorreplyexist'] = !$GLOBALS['forum']['ismoderator'] ? $db->result_first("SELECT pid FROM {$tablepre}posts WHERE tid='$tid' AND ".($discuz_uid ? "authorid='$discuz_uid'" : "authorid=0 AND useip='$GLOBALS[onlineip]'")." LIMIT 1") : TRUE;
  				}
  				if($GLOBALS['authorreplyexist']) {
  					$message = preg_replace("/\[hide\]\s*(.+?)\s*\[\/hide\]/is", tpl_hide_reply(), $message);
  				} else {
  					$message = preg_replace("/\[hide\](.+?)\[\/hide\]/is", tpl_hide_reply_hidden(), $message);
  					$message .= '<script type="text/javascript">replyreload += \',\' + '.$pid.';</script>';
  				}
  			}
  			if(strpos($msglower, '[hide=') !== FALSE) {
  				$message = preg_replace("/\[hide=(\d+)\]\s*(.+?)\s*\[\/hide\]/ies", "creditshide(\\1,'\\2', $pid)", $message);
  			}
  		}
  	}

  	if(!$bbcodeoff) {
  		if($parsetype != 1 && strpos($msglower, '[swf]') !== FALSE) {
  			$message = preg_replace("/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/ies", "bbcodeurl('\\1', ' <img src=\"images/attachicons/flash.gif\" align=\"absmiddle\" alt=\"\" /> <a href=\"%s\" target=\"_blank\">Flash: %s</a> ')", $message);
  		}
  		if(strpos($msglower, '[/img]') !== FALSE) {
  			$message = preg_replace(array(
  				"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
  				"/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
  			), $allowimgcode ? array(
  				"bbcodeurl('\\1', '<img src=\"%s\" onload=\"thumbImg(this)\" alt=\"\" />')",
  				"parseimg('\\1', '\\2', '\\3')"
  			) : array(
  				"bbcodeurl('\\1', '<a href=\"%s\" target=\"_blank\">%s</a>')",
  				"bbcodeurl('\\3', '<a href=\"%s\" target=\"_blank\">%s</a>')"
  			), $message);
  		}
  	}

  	for($i = 0; $i <= $discuzcodes['pcodecount']; $i++) {
  		$message = str_replace("[\tDISCUZ_CODE_$i\t]", $discuzcodes['codehtml'][$i], $message);
  	}

  	if($highlight) {
  		$highlightarray = explode('+', $highlight);
  		$sppos = strrpos($message, chr(0).chr(0).chr(0));
  		if($sppos !== FALSE) {
  			$specialextra = substr($postlist[$firstpid]['message'], $sppos + 3);
  			$message = substr($message, 0, $sppos);
  		}
  		$message = preg_replace(array("/(^|>)([^<]+)(?=<|$)/sUe", "/<highlight>(.*)<\/highlight>/siU"), array("highlight('\\2', \$highlightarray, '\\1')", "<strong><font color=\"#FF0000\">\\1</font></strong>"), $message);
  		if($sppos !== FALSE) {
  			$message = $message.chr(0).chr(0).chr(0).$specialextra;
  		}
  	}

  	unset($msglower);
		$message = preg_replace("/\s?\[code\](.+?)\[\/code\]\s?/ies", "codedisp('\\1')", $message);
    
  	return $htmlon ? $message : nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
  }
  
}