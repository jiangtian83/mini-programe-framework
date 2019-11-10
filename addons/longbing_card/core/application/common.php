<?php  if( !function_exists("p") ) 
{
	function p($array) 
	{
		echo "<pre>";
		print_r($array);
		exit();
	}
}
if( !function_exists("tablename") ) 
{
	function tablename($tablename) 
	{
		$full_tablename = config("database")["prefix"] . $tablename;
		return $full_tablename;
	}
}
if( !function_exists("lb_timediff") ) 
{
	function lb_timediff($begin_time, $end_time) 
	{
		if( $begin_time < $end_time ) 
		{
			$starttime = $begin_time;
			$endtime = $end_time;
		}
		else 
		{
			$starttime = $end_time;
			$endtime = $begin_time;
		}
		$timediff = $endtime - $starttime;
		$days = intval($timediff / 86400);
		$remain = $timediff % 86400;
		$hours = intval($remain / 3600);
		$remain = $remain % 3600;
		$mins = intval($remain / 60);
		$secs = $remain % 60;
		$res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
		return $res;
	}
}
function resultJsonStr($errno, $message, $data = "") 
{
	exit( json_encode(array( "errno" => $errno, "msg" => $message, "data" => $data )) );
}
function echoJsonStr($errno, $message, $data = "") 
{
	echo json_encode(array( "code" => $errno, "msg" => $message, "data" => $data ));
}
function lb_getDayPerMonth($year) 
{
	$arr = array( 1 => 31, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31 );
	if( $year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0 ) 
	{
		$arr[2] = 29;
	}
	else 
	{
		$arr[2] = 28;
	}
	return $arr;
}
function lb_friendly_date($sTime, $type = "mohu", $alt = "false") 
{
	if( !$sTime ) 
	{
		return "";
	}
	$cTime = time();
	$dTime = $cTime - $sTime;
	$dDay = intval($dTime / 3600 / 24);
	$dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
	if( $type == "normal" ) 
	{
		if( $dTime < 60 ) 
		{
			if( $dTime < 10 ) 
			{
				return "刚刚";
			}
			return intval(floor($dTime / 10) * 10) . "秒前";
		}
		if( $dTime < 3600 ) 
		{
			return intval($dTime / 60) . "分钟前";
		}
		if( $dYear == 0 && $dDay == 0 ) 
		{
			return "今天" . date("H:i", $sTime);
		}
		if( 0 < $dDay && $dDay <= 3 ) 
		{
			return intval($dDay) . "天前";
		}
		if( $dYear == 0 ) 
		{
			return date("m月d日 H:i", $sTime);
		}
		return date("m-d H:i", $sTime);
	}
	if( $type == "mohu" ) 
	{
		if( $dTime < 60 ) 
		{
			return $dTime . "秒前";
		}
		if( $dTime < 3600 ) 
		{
			return intval($dTime / 60) . "分钟前";
		}
		if( 3600 <= $dTime && $dDay == 0 ) 
		{
			return intval($dTime / 3600) . "小时前";
		}
		if( 0 < $dDay && $dDay <= 7 ) 
		{
			return intval($dDay) . "天前";
		}
		if( 7 < $dDay && $dDay <= 30 ) 
		{
			return intval($dDay / 7) . "周前";
		}
		if( 30 < $dDay ) 
		{
			return intval($dDay / 30) . "个月前";
		}
	}
	else 
	{
		if( $type == "full" ) 
		{
			return date("m-d , H:i", $sTime);
		}
		if( $type == "ymd" ) 
		{
			return date("Y-m-d", $sTime);
		}
		if( $dTime < 60 ) 
		{
			return $dTime . "秒前";
		}
		if( $dTime < 3600 ) 
		{
			return intval($dTime / 60) . "分钟前";
		}
		if( 3600 <= $dTime && $dDay == 0 ) 
		{
			return intval($dTime / 3600) . "小时前";
		}
		if( $dYear == 0 ) 
		{
			return date("m-d H:i", $sTime);
		}
		return date("m-d H:i", $sTime);
	}
}
function getdistance($lng1, $lat1, $lng2, $lat2) 
{
	$radLat1 = deg2rad($lat1);
	$radLat2 = deg2rad($lat2);
	$radLng1 = deg2rad($lng1);
	$radLng2 = deg2rad($lng2);
	$a = $radLat1 - $radLat2;
	$b = $radLng1 - $radLng2;
	$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;
	$s = sprintf("%.2f", $s);
	return $s;
}
function getHttpUrlRoot() 
{
	$http_type = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" || isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https" ? "https://" : "http://");
	$url = $http_type . $_SERVER["HTTP_HOST"];
	return $url;
}
function lb_getFirstChar($str) 
{
	if( empty($str) ) 
	{
		return "";
	}
	$fir = $fchar = ord($str[0]);
	if( ord("A") <= $fchar && $fchar <= ord("z") ) 
	{
		return strtoupper($str[0]);
	}
	$s1 = @iconv("UTF-8", "gb2312", $str);
	$s2 = @iconv("gb2312", "UTF-8", $s1);
	$s = ($s2 == $str ? $s1 : $str);
	if( !isset($s[0]) || !isset($s[1]) ) 
	{
		return "";
	}
	$asc = (ord($s[0]) * 256 + ord($s[1])) - 65536;
	if( is_numeric($str) ) 
	{
		return $str;
	}
	if( -20319 <= $asc && $asc <= -20284 || $fir == "A" ) 
	{
		return "A";
	}
	if( -20283 <= $asc && $asc <= -19776 || $fir == "B" ) 
	{
		return "B";
	}
	if( -19775 <= $asc && $asc <= -19219 || $fir == "C" ) 
	{
		return "C";
	}
	if( -19218 <= $asc && $asc <= -18711 || $fir == "D" ) 
	{
		return "D";
	}
	if( -18710 <= $asc && $asc <= -18527 || $fir == "E" ) 
	{
		return "E";
	}
	if( -18526 <= $asc && $asc <= -18240 || $fir == "F" ) 
	{
		return "F";
	}
	if( -18239 <= $asc && $asc <= -17923 || $fir == "G" ) 
	{
		return "G";
	}
	if( -17922 <= $asc && $asc <= -17418 || $fir == "H" ) 
	{
		return "H";
	}
	if( -17417 <= $asc && $asc <= -16475 || $fir == "J" ) 
	{
		return "J";
	}
	if( -16474 <= $asc && $asc <= -16213 || $fir == "K" ) 
	{
		return "K";
	}
	if( -16212 <= $asc && $asc <= -15641 || $fir == "L" ) 
	{
		return "L";
	}
	if( -15640 <= $asc && $asc <= -15166 || $fir == "M" ) 
	{
		return "M";
	}
	if( -15165 <= $asc && $asc <= -14923 || $fir == "N" ) 
	{
		return "N";
	}
	if( -14922 <= $asc && $asc <= -14915 || $fir == "O" ) 
	{
		return "O";
	}
	if( -14914 <= $asc && $asc <= -14631 || $fir == "P" ) 
	{
		return "P";
	}
	if( -14630 <= $asc && $asc <= -14150 || $fir == "Q" ) 
	{
		return "Q";
	}
	if( -14149 <= $asc && $asc <= -14091 || $fir == "R" ) 
	{
		return "R";
	}
	if( -14090 <= $asc && $asc <= -13319 || $fir == "S" ) 
	{
		return "S";
	}
	if( -13318 <= $asc && $asc <= -12839 || $fir == "T" ) 
	{
		return "T";
	}
	if( -12838 <= $asc && $asc <= -12557 || $fir == "W" ) 
	{
		return "W";
	}
	if( -12556 <= $asc && $asc <= -11848 || $fir == "X" ) 
	{
		return "X";
	}
	if( -11847 <= $asc && $asc <= -11056 || $fir == "Y" ) 
	{
		return "Y";
	}
	if( -11055 <= $asc && $asc <= -10247 || $fir == "Z" ) 
	{
		return "Z";
	}
	return "";
}
function formatImage($item, $arr = array( ), $type = 0, $uniacid = 0) 
{
	foreach( $arr as $vo ) 
	{
		$ret = array( );
		$value = $item[$vo];
		$valueArr = explode(",", $value);
		$attachModel = new app\base\model\BaseAttachment();
		foreach( $valueArr as $imgId ) 
		{
			if( empty($imgId) ) 
			{
				continue;
			}
			$path = $attachModel->getFilePath($imgId, $type);
			$returnArr["path"] = $path;
			$returnArr["id"] = $imgId;
			$returnArr = lb_image_rules($returnArr, array( "path" ), "list_cover_small", $uniacid);
			$ret[] = $returnArr;
		}
		$item[$vo] = $ret;
	}
	return $item;
}
function formatImageToList($list, $arr = array( ), $type = 0, $uniacid = 0) 
{
	foreach( $list as $key => $item ) 
	{
		if( is_array($item) || is_object($item) ) 
		{
			$list[$key] = formatimage($item, $arr, $type, $uniacid);
		}
	}
	return $list;
}
function lb_image_rules_list($list, $arr = array( ), $rule_name = "", $uniacid = 0) 
{
	if( $list ) 
	{
		foreach( $list as $key => $item ) 
		{
			$list[$key] = lb_image_rules($item, $arr, $rule_name, $uniacid);
		}
	}
	return $list;
}
function lb_image_rules($item, $arr = array( ), $rule_name = "", $uniacid = 0) 
{
	foreach( $arr as $vo ) 
	{
		$value = $item[$vo];
		if( strpos($value, gethttpurlroot()) === false ) 
		{
			$driver = "qiniuyun";
			$medio = "?";
			if( strpos($value, "aliyuncs.com") !== false ) 
			{
				$driver = "aliyun";
				$medio = "?x-oss-process=";
			}
			$rule_arr = app\base\model\BaseConfig::getArrRulesByDriver($driver, $uniacid);
			if( $rule_arr && isset($rule_arr[$rule_name]) ) 
			{
				$item[$vo . "_thumb"] = ($value ? $value . $medio . $rule_arr[$rule_name] : $value);
			}
			else 
			{
				$item[$vo . "_thumb"] = $value;
			}
		}
		else 
		{
			$item[$vo . "_thumb"] = $value;
		}
	}
	return $item;
}
function lb_image_rules_add($item, $column1, $column2, $rule_name = "", $uniacid = 0) 
{
	$value = $item[$column1];
	if( strpos($value, gethttpurlroot()) === false ) 
	{
		$driver = "qiniuyun";
		$medio = "?";
		if( strpos($value, "aliyuncs.com") !== false ) 
		{
			$driver = "aliyun";
			$medio = "?x-oss-process=";
		}
		$rule_arr = app\common\model\MingpianConfig::getArrRulesByDriver($driver, $uniacid);
		if( $rule_arr && isset($rule_arr[$rule_name]) ) 
		{
			$item[$column2] = $value . $medio . $rule_arr[$rule_name];
		}
	}
	else 
	{
		$item[$column2] = $value;
	}
	return $item;
}
function lb_makeRequest($url, $params = array( ), $expire = 0, $extend = array( ), $hostIp = "") 
{
	if( empty($url) ) 
	{
		return array( "code" => "100" );
	}
	$_curl = curl_init();
	$_header = array( "Accept-Language: zh-CN", "Connection: Keep-Alive", "Cache-Control: no-cache" );
	if( !empty($hostIp) ) 
	{
		$urlInfo = parse_url($url);
		if( empty($urlInfo["host"]) ) 
		{
			$urlInfo["host"] = substr(DOMAIN, 7, -1);
			$url = "http://" . $hostIp . $url;
		}
		else 
		{
			$url = str_replace($urlInfo["host"], $hostIp, $url);
		}
		$_header[] = "Host: " . $urlInfo["host"];
	}
	if( !empty($params) ) 
	{
		curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($_curl, CURLOPT_POST, true);
	}
	if( substr($url, 0, 8) == "https://" ) 
	{
		curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, false);
	}
	curl_setopt($_curl, CURLOPT_URL, $url);
	curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($_curl, CURLOPT_USERAGENT, "API PHP CURL");
	curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);
	if( 0 < $expire ) 
	{
		curl_setopt($_curl, CURLOPT_TIMEOUT, $expire);
		curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire);
	}
	if( !empty($extend) ) 
	{
		curl_setopt_array($_curl, $extend);
	}
	$result["result"] = curl_exec($_curl);
	$result["code"] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
	$result["info"] = curl_getinfo($_curl);
	if( $result["result"] === false ) 
	{
		$result["result"] = curl_error($_curl);
		$result["code"] = 0 - curl_errno($_curl);
	}
	curl_close($_curl);
	return $result;
}
function lb_api_notice_increment($url, $data) 
{
	$ch = curl_init();
	$header = "Accept-Charset: utf-8";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$tmpInfo = curl_exec($ch);
	if( curl_errno($ch) ) 
	{
		return false;
	}
	return $tmpInfo;
}
function lb_mkdirs($path) 
{
	if( !is_dir($path) ) 
	{
		lb_mkdirs(dirname($path));
		mkdir($path);
	}
	return is_dir($path);
}
function lb_recursion($result, $parentid = 0, $format = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|----") 
{
	static $list = array( );
	foreach( $result as $k => $v ) 
	{
		if( $v["pid"] == $parentid ) 
		{
			if( $parentid != 0 ) 
			{
				$v["title"] = $format . $v["title"];
			}
			$list[] = $v;
			lb_recursion($result, $v["id"], "  " . $format);
		}
	}
	return $list;
}
function lb_getMonthNum_bak($date1, $date2, $tags = "-") 
{
	$date1 = explode($tags, $date1);
	$date2 = explode($tags, $date2);
	return abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
}
function lb_getMonthNum($date1, $date2) 
{
	if( strtotime($date2) < strtotime($date1) ) 
	{
		$tmp = $date2;
		$date2 = $date1;
		$date1 = $tmp;
	}
	list($Y1, $m1, $d1) = explode("-", $date1);
	list($Y2, $m2, $d2) = explode("-", $date2);
	$Y = $Y2 - $Y1;
	$m = $m2 - $m1;
	$d = $d2 - $d1;
	if( $d < 0 ) 
	{
		$d += (int) date("t", strtotime("-1 month " . $date2));
		$m--;
	}
	if( $m < 0 ) 
	{
		$m += 12;
		$Y--;
	}
	return $m;
}
function lb_logOutput($data, $flag = 0) 
{
	if( $flag == 0 ) 
	{
		return NULL;
	}
	if( is_array($data) ) 
	{
		$data = json_encode($data);
	}
	$filename = "./" . date("Y-m-d") . ".log";
	$str = date("Y-m-d H:i:s") . "   " . $data . "\r\n";
	file_put_contents($filename, $str, FILE_APPEND | LOCK_EX);
}
function lb_operateArray($arr, $mode, $id = 0, $add_arr = array( ), $toplimit = 3, $endlimit = 1) 
{
	if( !is_array($arr) ) 
	{
		return "数组格式不对";
	}
	$res = $arr;
	$count = count($res);
	if( $mode == 1 ) 
	{
		if( $toplimit <= $count ) 
		{
			return "数量达到上限";
		}
		if( !$add_arr || !$add_arr["id"] ) 
		{
			return "添加数据有误";
		}
		$ids = array( );
		if( !empty($res) ) 
		{
			foreach( $res as $item ) 
			{
				$ids[] = $item["id"];
			}
		}
		if( in_array($add_arr["id"], $ids) ) 
		{
			return "id重复";
		}
		array_push($res, $add_arr);
		$res = array_values($res);
		return $res;
	}
	else 
	{
		if( $mode == 0 ) 
		{
			if( $count <= $endlimit ) 
			{
				return "至少保留一个";
			}
			foreach( $res as $k => $item ) 
			{
				if( $item["id"] == $id ) 
				{
					unset($res[$k]);
				}
			}
			$res = array_values($res);
			return $res;
		}
		else 
		{
			if( $mode == 2 ) 
			{
				foreach( $res as $key => $item ) 
				{
					if( $item["id"] == $id ) 
					{
						$res[$key] = $add_arr;
					}
				}
				$res = array_values($res);
				return $res;
			}
		}
	}
}
function lb_getOneArray($arr, $id) 
{
	if( !is_array($arr) ) 
	{
		return "数组格式不对";
	}
	$res = array( );
	foreach( $arr as $k => $item ) 
	{
		if( $item["id"] == $id ) 
		{
			$res = $item;
		}
	}
	return $res;
}
function checkSensitive($data, $uniacid) 
{
	if( is_array($data) ) 
	{
		$data = implode(",", $data);
	}
	$token = get_access_token($uniacid);
	if( !$token ) 
	{
		return "token wrong";
	}
	$url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=" . $token;
	$data = json_encode(array( "content" => $data ), JSON_UNESCAPED_UNICODE);
	$res = lb_api_notice_increment($url, $data);
	$res = json_decode($res, true);
	$check_res = lb_check_access_token($res, $uniacid);
	if( $check_res ) 
	{
		$url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=" . $check_res;
		$res = lb_api_notice_increment($url, $data);
		$res = json_decode($res, true);
	}
	if( $res["errcode"] == 0 ) 
	{
		return true;
	}
	if( $res["errcode"] == 87014 ) 
	{
		return "有敏感词！重新编辑";
	}
	return "API Wrong";
}
function get_access_token($uniacid) 
{
	$key = "access_token_" . $uniacid;
	$access_token = cache($key);
	if( $access_token ) 
	{
		return $access_token;
	}
	$config = app\base\model\BaseConfig::get(array( "uniacid" => $uniacid ));
	if( !$config ) 
	{
		return false;
	}
	$url = "https://api.weixin.qq.com/cgi-bin/token";
	$params = array( "appid" => $config["appid"], "secret" => $config["appsecret"], "grant_type" => "client_credential" );
	$res = lb_api_notice_increment($url, $params);
	$res = json_decode($res, true);
	if( isset($res["access_token"]) && $res["access_token"] ) 
	{
		$result = cache($key, $res["access_token"], array( "expire" => 3600 ));
		if( $result ) 
		{
			return $res["access_token"];
		}
		return false;
	}
	return false;
}
function lb_check_access_token($data, $uniacid) 
{
	if( is_array($data) && isset($data["errcode"]) && ($data["errcode"] == 42001 || $data["errcode"] == 40001) ) 
	{
		$key = "access_token_" . $uniacid;
		cache($key, NULL);
		$access_token = get_access_token($uniacid);
		if( $access_token ) 
		{
			return $access_token;
		}
		return false;
	}
	return false;
}
function lb_checkdomain($is_check = true) 
{
	if( !$is_check ) 
	{
		defined("LONGBING_TP_CARD_ALLOW") or define("LONGBING_TP_CARD_ALLOW", 1);
	}
	else 
	{
		$chars_array = array( "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z" );
		$str = "";
		for( $i = 0; $i < 4; $i++ ) 
		{
			$tmp = rand(0, 25);
			$str .= $chars_array[$tmp];
		}
		$domain = $_SERVER["HTTP_HOST"];
		$domainMd5 = md5($_SERVER["SERVER_NAME"] . APP_NAME);
		$url = "https://auth.xiaochengxucms.com/index.php/longbing_auth/test/domain";
		$dataCheck = array( "domain" => $domain, "key" => $str );
		$file_path = IA_ROOT . "/data/tpl/web/";
		if( !is_dir(IA_ROOT . "/data") ) 
		{
			mkdir(IA_ROOT . "/data");
		}
		if( !is_dir(IA_ROOT . "/data/tpl") ) 
		{
			mkdir(IA_ROOT . "/data/tpl");
		}
		if( !is_dir($file_path) ) 
		{
			mkdir($file_path);
		}
		if( is_file($file_path . $domainMd5 . "tpl.txt") ) 
		{
			$file = file_get_contents($file_path . $domainMd5 . "tpl.txt");
			$arr = explode("--", $file);
			$res = json_decode($arr[0], true);
			if( $res["code"] == 200 ) 
			{
				$e = lb_encrypt($res["data"], "D", $arr[1]);
				$e = json_decode($e, true);
			}
			else 
			{
				$e = $res["data"];
			}
			foreach( $e as $k => $v ) 
			{
				foreach( $v as $k2 => $v2 ) 
				{
					if( $k2 == "date" ) 
					{
						if( 86400 < time() - $v2 ) 
						{
							$res = lb_api_notice_increment($url, $dataCheck);
							file_put_contents($file_path . $domainMd5 . "tpl.txt", $res . "--" . $str);
							$res = json_decode($res, true);
						}
						else 
						{
							$str = $arr[1];
						}
					}
				}
			}
		}
		else 
		{
			$res = lb_api_notice_increment($url, $dataCheck);
			file_put_contents($file_path . $domainMd5 . "tpl.txt", $res . "--" . $str);
			$res = json_decode($res, true);
		}
		if( !is_array($res) ) 
		{
			defined("LONGBING_TP_CARD_ALLOW") or define("LONGBING_TP_CARD_ALLOW", 0);
		}
		else 
		{
			if( $res["code"] == 200 ) 
			{
				$res = lb_encrypt($res["data"], "D", $str);
				$res = json_decode($res, true);
				foreach( $res as $k => $v ) 
				{
					foreach( $v as $k2 => $v2 ) 
					{
						if( $k2 == "date" ) 
						{
							continue;
						}
						defined($k2) or define($k2, $v2);
					}
				}
			}
			else 
			{
				defined("LONGBING_TP_CARD_ALLOW") or define("LONGBING_TP_CARD_ALLOW", 0);
			}
		}
	}
}
function lb_encrypt($string, $operation, $key = "") 
{
	$key = md5($key);
	$key_length = strlen($key);
	$string = ($operation == "D" ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string);
	$string_length = strlen($string);
	$rndkey = $box = array( );
	$result = "";
	for( $i = 0; $i <= 255; $i++ ) 
	{
		$rndkey[$i] = ord($key[$i % $key_length]);
		$box[$i] = $i;
	}
	for( $j = $i = 0; $i < 256; $i++ ) 
	{
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for( $a = $j = $i = 0; $i < $string_length; $i++ ) 
	{
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
	}
	if( $operation == "D" ) 
	{
		if( substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8) ) 
		{
			return substr($result, 8);
		}
		return "";
	}
	return str_replace("=", "", base64_encode($result));
}
function lb_deldir($path) 
{
	if( is_dir($path) ) 
	{
		$p = scandir($path);
		foreach( $p as $val ) 
		{
			if( $val != "." && $val != ".." ) 
			{
				if( is_dir($path . $val) ) 
				{
					lb_deldir($path . $val . "/");
					@rmdir($path . $val . "/");
				}
				else 
				{
					unlink($path . $val);
				}
			}
		}
	}
}
function lb_uploadByPath($path, $uniacid) 
{
	$img = file_get_contents($path);
	if( defined("IS_WEIQIN") ) 
	{
		$path_img = $_SERVER["DOCUMENT_ROOT"] . "/attachment/upload/" . $uniacid . "/picture/";
	}
	else 
	{
		$path_img = PUBLIC_PATH . "uploads/picture/";
	}
	lb_mkdirs($path_img);
	$filename = rand(1000, 9999) . time() . ".png";
	file_put_contents($path_img . $filename, $img);
	$name = "图片" . rand(1000, 9999);
	$path2 = "/attachment/upload/" . $uniacid . "/picture/" . $filename;
	if( defined("IS_WEIQIN") ) 
	{
		$path2 = "/attachment/upload/" . $uniacid . "/picture/" . $filename;
	}
	else 
	{
		$path2 = "/uploads/picture/" . $filename;
	}
	$data = array( "name" => $name, "path" => $path2, "location" => "", "path_type" => "", "ext" => "png", "mime_type" => "image", "size" => 0, "alt" => $name, "md5" => md5_file($path_img . $filename), "sha1" => sha1_file($path_img . $filename), "download" => 0, "create_time" => time(), "update_time" => time(), "sort" => 0, "status" => 1, "uniacid" => $uniacid, "driver" => "local", "src" => gethttpurlroot() . "/" . $path2 );
	$model = new app\base\model\BaseAttachment();
	$id = $model->insertGetId($data);
	return $id;
}
function lb_check_first_data($uniacid = 0) 
{
	if( $uniacid ) 
	{
		$configModel = new app\common\model\MingpianConfig();
		$config = $configModel->get(array( "uniacid" => $uniacid ));
		$data_config = array( );
		require_once(APP_PATH . "data_config.php");
		if( !$config ) 
		{
			$att_id = lb_uploadbypath($data_config["config"]["tech_support"], $uniacid);
			$partner_poster_id = lb_uploadbypath($data_config["config"]["partner_poster"], $uniacid);
			$data = array( "uniacid" => $uniacid, "create_time" => time(), "update_time" => time(), "status" => 1, "miniapp_name" => $data_config["config"]["miniapp_name"], "base_dir" => "image", "open_oss" => 0, "tech_support" => $att_id, "index_text" => $data_config["config"]["index_text"], "debug_switch" => 1, "ios_pay_switch" => 1, "android_pay_switch" => 1, "qiniu_rules" => "list_cover_small:imageView2/1/w/200/h/200/q/75|imageslim", "aliyun_rules" => "list_cover_small:image/auto-orient,1/resize,m_fill,w_200,h_200/quality,q_81", "partner_poster" => $partner_poster_id, "withdrawal_limit" => $data_config["config"]["withdrawal_limit"], "forword_words" => $data_config["config"]["forword_words"], "group_category" => $data_config["config"]["group_category"], "info_agreement" => $data_config["config"]["info_agreement"], "baidu_ak" => $data_config["config"]["baidu_ak"] );
			$res1 = $configModel->save($data);
		}
		else 
		{
			$updata = array( );
			if( !$config["qiniu_rules"] ) 
			{
				$updata["qiniu_rules"] = "list_cover_small:imageView2/1/w/200/h/200/q/75|imageslim";
			}
			if( !$config["aliyun_rules"] ) 
			{
				$updata["aliyun_rules"] = "list_cover_small:image/auto-orient,1/resize,m_fill,w_200,h_200/quality,q_81";
			}
			if( !$config["index_text"] ) 
			{
				$updata["index_text"] = $data_config["config"]["index_text"];
			}
			if( !$config["miniapp_name"] ) 
			{
				$updata["miniapp_name"] = $data_config["config"]["miniapp_name"];
			}
			if( !$config["group_category"] ) 
			{
				$updata["group_category"] = $data_config["config"]["group_category"];
			}
			if( !$config["tech_support"] ) 
			{
				$att_id = lb_uploadbypath($data_config["config"]["tech_support"], $uniacid);
				$updata["tech_support"] = $att_id;
			}
			if( !$config["withdrawal_limit"] ) 
			{
				$updata["withdrawal_limit"] = 50;
			}
			if( !$config["partner_poster"] ) 
			{
				$partner_poster_id = lb_uploadbypath($data_config["config"]["partner_poster"], $uniacid);
				$updata["partner_poster"] = $partner_poster_id;
			}
			if( !$config["forword_words"] ) 
			{
				$updata["forword_words"] = $data_config["config"]["forword_words"];
			}
			if( !$config["info_agreement"] ) 
			{
				$updata["info_agreement"] = $data_config["config"]["info_agreement"];
			}
			if( !$config["baidu_ak"] ) 
			{
				$updata["baidu_ak"] = $data_config["config"]["baidu_ak"];
			}
			$res1_2 = $configModel->update($updata, array( "id" => $config["id"] ));
		}
		$adModel = new app\common\model\MingpianAd();
		$ad_count = $adModel->where(array( "uniacid" => $uniacid ))->count();
		if( !$ad_count ) 
		{
			$path_id = lb_uploadbypath($data_config["ad"]["path"], $uniacid);
			$data = array( "uniacid" => $uniacid, "create_time" => time(), "update_time" => time(), "status" => 1, "type" => 2, "path" => $path_id, "link" => "13980472709", "link_type" => 1 );
			$res2 = $adModel->save($data);
		}
		foreach( $data_config["template"] as $template ) 
		{
			$templateModel = new app\common\model\MingpianTemplate();
			$aaa = $templateModel->where(array( "uniacid" => $uniacid, "idn" => $template["idn"] ))->find();
			if( !$aaa ) 
			{
				$attach_id = lb_uploadbypath($template["path"], $uniacid);
				$template["uniacid"] = $uniacid;
				$template["path"] = $attach_id;
				$templateModel->save($template);
			}
			else 
			{
				$update = array( );
				if( !$aaa["name"] ) 
				{
					$update["name"] = $template["name"];
				}
				if( !$aaa["group"] ) 
				{
					$update["group"] = $template["group"];
				}
				if( !$aaa["price"] ) 
				{
					$update["price"] = $template["price"];
				}
				$templateModel->update($update, array( "id" => $aaa["id"] ));
			}
		}
		foreach( $data_config["partner"] as $partner ) 
		{
			$partnerModel = new app\common\model\MingpianPartnerLevel();
			$aaa = $partnerModel->where(array( "uniacid" => $uniacid, "idn" => $partner["idn"] ))->find();
			if( !$aaa ) 
			{
				$partner["uniacid"] = $uniacid;
				$partnerModel->save($partner);
			}
		}
		foreach( $data_config["vip_level"] as $vip_level ) 
		{
			$vipModel = new app\common\model\MingpianVip();
			$level = $vipModel->get(array( "uniacid" => $uniacid, "name" => $vip_level["name"] ));
			if( $level && $level["name"] == 2 ) 
			{
				$term_data = $level["term_data"];
				if( !$term_data ) 
				{
					$vipModel->update(array( "term_data" => $vip_level["term_data"] ), array( "uniacid" => $uniacid, "name" => 2 ));
				}
				else 
				{
					$month_column = array_column($term_data, "month");
					foreach( $vip_level["term_data"] as $term_item ) 
					{
						if( !in_array($term_item["month"], $month_column) ) 
						{
							$arr_id = array_column($term_data, "id");
							$max_id = max($arr_id);
							$term_item["id"] = $max_id + 1;
							$res = lb_operatearray($term_data, 1, 0, $term_item);
							$vipModel = new app\common\model\MingpianVip();
							$vipModel->update(array( "term_data" => $res ), array( "uniacid" => $uniacid, "name" => 2 ));
						}
					}
				}
			}
			if( !$level ) 
			{
				$vip_level["uniacid"] = $uniacid;
				$vipModel->save($vip_level);
			}
		}
		$ruleModel = new app\common\model\MingpianRule();
		$info_rule_count = $ruleModel->where(array( "uniacid" => $uniacid, "type" => 1 ))->count();
		if( !$info_rule_count ) 
		{
			$info_rule_data = array( "uniacid" => $uniacid, "status" => 1, "name" => $data_config["info_top_rule"]["name"], "days" => $data_config["info_top_rule"]["days"], "money" => $data_config["info_top_rule"]["money"], "type" => $data_config["info_top_rule"]["type"] );
			$ruleModel->save($info_rule_data);
		}
		$radarConfigModel = new app\common\model\RadarConfig();
		$radar_config = $radarConfigModel->get(array( "uniacid" => $uniacid ));
		if( !$radar_config ) 
		{
			$radar_config_data = array( "uniacid" => $uniacid, "xcx_appid" => app\common\model\MingpianConfig::where(array( "uniacid" => $uniacid ))->value("appid") );
			$radarConfigModel->save($radar_config_data);
		}
		foreach( $data_config["event"] as $event ) 
		{
			$eventModel = new app\common\model\RadarEvent();
			$abc = $eventModel->where(array( "uniacid" => $uniacid, "name" => $event["name"] ))->find();
			if( !$abc ) 
			{
				$event["uniacid"] = $uniacid;
				$eventModel->save($event);
			}
		}
	}
}
function lb_compare_version($ver1, $ver2) 
{
	$arr1 = explode(".", $ver1);
	$arr2 = explode(".", $ver2);
	if( $arr2[0] < $arr1[0] ) 
	{
		return 2;
	}
	if( $arr1[0] < $arr2[0] ) 
	{
		return 1;
	}
	if( $arr1[0] == $arr2[0] ) 
	{
		if( $arr2[1] < $arr1[1] ) 
		{
			return 2;
		}
		if( $arr1[1] < $arr2[1] ) 
		{
			return 1;
		}
		if( $arr1[1] == $arr2[1] ) 
		{
			if( $arr2[2] < $arr1[2] ) 
			{
				return 2;
			}
			if( $arr1[2] < $arr2[2] ) 
			{
				return 1;
			}
			if( $arr1[2] == $arr2[2] ) 
			{
				return 0;
			}
		}
	}
}
function ld_grouplist_bycolumn($list, $column) 
{
	$arr = array( );
	if( is_array($list) ) 
	{
		foreach( $list as $key => $item ) 
		{
			$count = 0;
			if( $key == 0 ) 
			{
				$arr[$count][$column] = $item[$column];
				$arr[$count]["data"][] = $item;
			}
			else 
			{
				if( $item[$column] == $list[$key - 1][$column] ) 
				{
					$arr[$count]["data"][] = $item;
				}
				else 
				{
					$count++;
					$arr[$count][$column] = $item[$column];
					$arr[$count]["data"][] = $item;
				}
			}
		}
	}
	return $arr;
}
function lb_remove_trim_List($data, $arr = array( )) 
{
	$res = $data;
	if( $arr ) 
	{
		foreach( $arr as $item ) 
		{
			if( isset($data[$item]) ) 
			{
				$res[$item] = trim($data[$item]);
			}
		}
	}
	return $res;
}
function lb_get_weeks($time = "", $format = "Y-m-d") 
{
	$time = ($time != "" ? $time : time());
	$date = array( );
	for( $i = 1; $i <= 7; $i++ ) 
	{
		$date[$i - 1] = date($format, strtotime(("+" . $i - 7) . " days", $time));
	}
	return $date;
}
function lb_get_view_data($day_first_seven, $uniacid) 
{
	$key = "view_data_" . $uniacid;
	$res_arr = cache($key);
	if( $res_arr ) 
	{
		return json_decode($res_arr, true);
	}
	$open_seven = array( );
	$view_seven = array( );
	if( $day_first_seven ) 
	{
		$access_token = get_access_token($uniacid);
		$url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token=" . $access_token;
		foreach( $day_first_seven as $day ) 
		{
			if( $day != date("Y-m-d", time()) ) 
			{
				$time_a = date("Ymd", strtotime($day));
				$data_arr = array( "begin_date" => $time_a, "end_date" => $time_a );
				$data_arr = json_encode($data_arr);
				$res = lb_api_notice_increment($url, $data_arr);
				$res = json_decode($res, true);
				$check_res = lb_check_access_token($res, $uniacid);
				if( $check_res ) 
				{
					$url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token=" . $check_res;
					$res = lb_api_notice_increment($url, $data_arr);
					$res = json_decode($res, true);
				}
				if( isset($res["list"][0]["session_cnt"]) && isset($res["list"][0]["visit_uv"]) ) 
				{
					$open_seven[] = $res["list"][0]["session_cnt"] / 10000;
					$view_seven[] = $res["list"][0]["visit_uv"] / 10000;
				}
				else 
				{
					$open_seven[] = 0;
					$view_seven[] = 0;
				}
			}
		}
		$open_seven[6] = 0;
		$view_seven[6] = 0;
	}
	$data = array( "open_seven" => $open_seven, "view_seven" => $view_seven );
	$data_jaon = json_encode($data);
	$result = cache($key, $data_jaon, 36000);
	if( $result ) 
	{
		return $data;
	}
	return false;
}
function lb_getQrcodePath($uid = 0, $uniacid = 0, $wx_path, $flag = "", $scene = "", $only = 0) 
{
	$uid = intval($uid);
	$user = app\base\model\BaseUser::get(array( "uniacid" => $uniacid, "id" => $uid ));
	if( !$user ) 
	{
		return false;
	}
	if( !$flag ) 
	{
		return false;
	}
	$access_token = get_access_token($uniacid);
	if( $access_token === false ) 
	{
		return false;
	}
	$res_scene = ($scene ? $scene : $uid);
	$post_data = "{\"scene\":\"" . $res_scene . "\",\"page\":\"" . $wx_path . "\"}";
	$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
	$result = lb_api_notice_increment($url, $post_data);
	$check_res = lb_check_access_token($result, $uniacid);
	if( $check_res ) 
	{
		$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $check_res;
		$result = lb_api_notice_increment($url, $post_data);
	}
	if( isset($result["errcode"]) ) 
	{
		return false;
	}
	$filepath = $_SERVER["DOCUMENT_ROOT"] . "/attachment/upload/" . $uniacid . "/" . $flag;
	lb_mkdirs($filepath);
	$only_flag = ($only ? $only : $uid);
	$filepath .= "/" . $only_flag . "_" . $flag . ".jpg";
	file_put_contents($filepath, $result);
	$as = @getimagesize($filepath);
	if( $as === false ) 
	{
		unlink($filepath);
		$img_path = gethttpurlroot() . "/static/resource/default_card_qrcode.jpg";
		if( defined("IS_WEIQIN") && IS_WEIQIN ) 
		{
			$img_path = gethttpurlroot() . "/addons/" . APP_NAME . "/core/public/static/resource/default_card_qrcode.jpg";
		}
		if( file_exists(gethttpurlroot() . "/addons/" . APP_NAME . "/core/public/static/resource/default_card_qrcode.jpg") ) 
		{
			$img_path = gethttpurlroot() . "/addons/" . APP_NAME . "/core/public/static/resource/default_card_qrcode.jpg";
		}
		$img = file_get_contents($img_path);
		file_put_contents($filepath, $img);
	}
	$filepath = substr($filepath, strlen($_SERVER["DOCUMENT_ROOT"]));
	$filepath = gethttpurlroot() . "/" . $filepath;
	return $filepath;
}
function lb_identify_card_outsite($url, $uniacid) 
{
	require_once(EXTEND_PATH . "tenxunyunimage/autoload.php");
	$bucket = "BUCKET";
	$config = app\common\model\MingpianConfig::get(array( "uniacid" => $uniacid ));
	$app_id = $config["tenxunyun_appid"];
	$secret_id = $config["tenxunyun_secretid"];
	$secret_key = $config["tenxunyun_secretkey"];
	if( !$app_id || !$secret_key || !$secret_id ) 
	{
		return array( "code" => 1, "msg" => "管理员未配置名片识别的相关数据!" );
	}
	$client = new QcloudImage\CIClient($app_id, $secret_id, $secret_key, $bucket);
	$client->setTimeout(30);
	$res = $client->namecardV2Detect(array( "urls" => array( $url ) ));
	$res = json_decode($res, true);
	if( isset($res["code"]) && $res["code"] != 0 ) 
	{
		$msg = ((isset($res["msg"]) ? $res["msg"] : isset($res["message"])) ? $res["message"] : "错误");
		return array( "code" => $res["code"], "msg" => $msg );
	}
	if( isset($res["result_list"][0]["code"]) && $res["result_list"][0]["code"] != 0 ) 
	{
		return array( "code" => $res["result_list"][0]["code"], "msg" => "识别错误，请上传正确的名片" );
	}
	$return_data["person_avatar"] = $url;
	foreach( $res["result_list"][0]["data"] as $item ) 
	{
		if( $item["item"] == "姓名" ) 
		{
			$return_data["name"] = $item["value"];
		}
		if( $item["item"] == "手机" ) 
		{
			$return_data["tel"] = str_replace("-", "", $item["value"]);
		}
		if( $item["item"] == "公司" ) 
		{
			$return_data["company_name"] = $item["value"];
		}
		if( $item["item"] == "职位" ) 
		{
			$return_data["position"] = $item["value"];
		}
		if( $item["item"] == "地址" ) 
		{
			$return_data["address"] = $item["value"];
		}
		if( $item["item"] == "微信" ) 
		{
			$return_data["wechat_number"] = $item["value"];
		}
	}
	if( !isset($return_data["name"]) ) 
	{
		$return_data["name"] = "";
	}
	if( !isset($return_data["tel"]) ) 
	{
		$return_data["tel"] = "";
	}
	if( !isset($return_data["company_name"]) ) 
	{
		$return_data["company_name"] = "";
	}
	if( !isset($return_data["position"]) ) 
	{
		$return_data["position"] = "";
	}
	if( !isset($return_data["address"]) ) 
	{
		$return_data["address"] = "";
	}
	if( !isset($return_data["wechat_number"]) ) 
	{
		$return_data["wechat_number"] = "";
	}
	$cardModel = new app\common\model\MingpianCard();
	$card = $cardModel->get(array( "uniacid" => $uniacid, "tel" => $return_data["tel"], "status" => 1 ));
	$return_data["has_card"] = false;
	if( $card ) 
	{
		$return_data["has_card"] = true;
		$return_data["card_uid"] = $card["uid"];
		$return_data["card_id"] = $card["id"];
	}
	$return_data2["data"] = $return_data;
	$return_data2["code"] = 0;
	$return_data2["msg"] = "成功";
	return $return_data2;
}
function lb_wxBizDataCrypt($uniacid, $sessionKey, $encryptedData, $iv) 
{
	include_once(EXTEND_PATH . "wxBizDataCrypt/wxBizDataCrypt.php");
	$appid = app\base\model\BaseConfig::where(array( "uniacid" => $uniacid ))->value("appid");
	$pc = new WXBizDataCrypt($appid, $sessionKey);
	$errCode = $pc->decryptData($encryptedData, $iv, $data);
	if( $errCode == 0 ) 
	{
		return array( "code" => 0, "data" => $data );
	}
	return array( "code" => $errCode );
}
function lb_getmain_color($url, $uiacid) 
{
	$image_info = @getimagesize($url);
	if( $image_info === false ) 
	{
		return false;
	}
	$mime = substr(strrchr($image_info["mime"], "/"), 1);
	$methed = "imagecreatefrom" . $mime;
	$i = $methed($url);
	$rTotal = 0;
	$gTotal = 0;
	$bTotal = 0;
	$total = 0;
	for( $x = 0; $x < imagesx($i);
	$x++ ) 
	{
		for( $y = 0; $y < imagesy($i);
		$y++ ) 
		{
			$rgb = imagecolorat($i, $x, $y);
			$r = $rgb >> 16 & 255;
			$g = $rgb >> 8 & 255;
			$b = $rgb & 255;
			$rTotal += $r;
			$gTotal += $g;
			$bTotal += $b;
			$total++;
		}
	}
	$rAverage = round($rTotal / $total);
	$gAverage = round($gTotal / $total);
	$bAverage = round($bTotal / $total);
	$color = lb_RGBToHex("rgb(" . $rAverage . "," . $gAverage . "," . $bAverage . ")");
	return $color;
}
function lb_RGBToHex($rgb) 
{
	$regexp = "/^rgb\\(([0-9]{0,3})\\,\\s*([0-9]{0,3})\\,\\s*([0-9]{0,3})\\)/";
	$re = preg_match($regexp, $rgb, $match);
	$re = array_shift($match);
	$hexColor = "#";
	$hex = array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F" );
	for( $i = 0; $i < 3; $i++ ) 
	{
		$r = NULL;
		$c = $match[$i];
		$hexAr = array( );
		while( 16 < $c ) 
		{
			$r = $c % 16;
			$c = $c / 16 >> 0;
			array_push($hexAr, $hex[$r]);
		}
		array_push($hexAr, $hex[$c]);
		$ret = array_reverse($hexAr);
		$item = implode("", $ret);
		$item = str_pad($item, 2, "0", STR_PAD_LEFT);
		$hexColor .= $item;
	}
	return $hexColor;
}
function lb_checkVersion($uniacid = 0) 
{
	if( defined("LONGBING_TP_CARD_ALLOW") && LONGBING_TP_CARD_ALLOW == 1 ) 
	{
		$flag = "unlimit_full";
	}
	else 
	{
		if( defined("LONGBING_TP_CARD_FIVE_FULL") && LONGBING_TP_CARD_FIVE_FULL == 1 ) 
		{
			$flag = "five_radar";
		}
		else 
		{
			if( defined("LONGBING_TP_CARD_ONE_FULL") && LONGBING_TP_CARD_ONE_FULL == 1 ) 
			{
				$flag = "one_radar";
			}
			else 
			{
				if( defined("LONGBING_TP_CARD_ONE_STANDARD") && LONGBING_TP_CARD_ONE_STANDARD == 1 ) 
				{
					$flag = "one_standard";
				}
				else 
				{
					$flag = "one_standard_free";
				}
			}
		}
	}
	$version = array( );
	$res_data = array( "code" => 0, "msg" => "成功" );
	require_once(APP_PATH . "version.php");
	$version_data = $version[$flag];
	define("LONGBING_CARDCLOUND_MINI", $version_data["number"]);
	define("LONGBING_CARDCLOUND_CARD", $version_data["cards"]);
	if( LONGBING_CARDCLOUND_MINI != 0 ) 
	{
		$configMode = new app\common\model\MingpianConfig();
		$mini_uniacids = $configMode->where(array( ))->order("id asc")->limit(0, LONGBING_CARDCLOUND_MINI)->column("uniacid");
		if( !empty($mini_uniacids) && !in_array($uniacid, $mini_uniacids) ) 
		{
			$res_data["code"] = -1;
			$res_data["msg"] = "您的版本为" . $version_data["name"] . "，如需升级请购买相应的版本,然后联系客服进行域名授权。";
		}
	}
	return $res_data;
}
function lb_format_time($data, $target = array( )) 
{
	$data = json_decode(json_encode($data), true);
	if( !is_array($data) ) 
	{
		return $data;
	}
	foreach( $data as $index => $item ) 
	{
		if( is_array($item) || is_object($item) ) 
		{
			$data[$index] = lb_format_time($item, $target);
		}
		else 
		{
			if( in_array($index, $target) ) 
			{
				$timestamp = @strtotime($item);
				if( $timestamp == false ) 
				{
					$timestamp = $item;
				}
				$data[$index] = $timestamp;
				$data[$index . 2] = date("Y-m-d H:i:s", $timestamp);
				$data[$index . 3] = date("Y-m-d H:i", $timestamp);
				$data[$index . 4] = date("Y-m-d H", $timestamp);
				$data[$index . 5] = date("Y-m-d", $timestamp);
				$data[$index . 6] = date("m-d", $timestamp);
			}
		}
	}
	return $data;
}
function lb_object_to_array_z($data) 
{
	$data = json_decode(json_encode($data), true);
	return $data;
}
function lb_dump_die($data) 
{
	echo "<pre>";
	$data = json_decode(json_encode($data), true);
	var_dump($data);
	exit();
}
function lb_dump_live($data) 
{
	echo "<pre>";
	$data = json_decode(json_encode($data), true);
	var_dump($data);
}
function lb_getChatTimeStr($addTime) 
{
	$nowTime = time();
	if( $nowTime < $addTime ) 
	{
		return "";
	}
	$timeStr = "";
	$addTime = explode(",", date("Y,n,j,w,a,h,i,y", $addTime));
	$nowTime = explode(",", date("Y,n,j,w,a,h,i,y", $nowTime));
	$dayPerMonthAddTime = lb_getdaypermonth($addTime[0]);
	$week = array( "星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六" );
	if( $addTime[0] == $nowTime[0] && $addTime[1] == $nowTime[1] && $addTime[2] == $nowTime[2] ) 
	{
		$timeStr .= $addTime[5] . ":" . $addTime[6];
	}
	else 
	{
		if( $addTime[0] == $nowTime[0] && $addTime[1] == $nowTime[1] && $addTime[2] == $nowTime[2] - 1 || $addTime[0] == $nowTime[0] && $nowTime[1] - $addTime[1] == 1 && $dayPerMonthAddTime[$addTime[1]] == $addTime[2] && $nowTime[2] == 1 || $nowTime[0] - $addTime[0] == 1 && $addTime[1] == 12 && $addTime[2] == 31 && $nowTime[1] == 1 && $nowTime[2] == 1 ) 
		{
			$timeStr .= "昨天 " . $addTime[5] . ":" . $addTime[6] . " ";
		}
		else 
		{
			if( $addTime[0] == $nowTime[0] && $addTime[1] == $nowTime[1] && $nowTime[2] - $addTime[2] < 7 || $addTime[0] == $nowTime[0] && $nowTime[1] - $addTime[1] == 1 && $dayPerMonthAddTime[$addTime[1]] - $addTime[2] + $nowTime[2] < 7 || $nowTime[0] - $addTime[0] == 1 && $addTime[1] == 12 && $nowTime[1] == 1 && 31 - $addTime[2] + $nowTime[2] < 7 ) 
			{
				$timeStr .= $week[$addTime[3]] . " " . $addTime[5] . ":" . $addTime[6];
			}
			else 
			{
				$timeStr .= $addTime[1] . "/" . $addTime[2] . "/" . $addTime[7] . " " . $addTime[5] . ":" . $addTime[6];
			}
		}
	}
	if( $addTime[4] == "am" ) 
	{
		$timeStr .= " 上午";
	}
	else 
	{
		if( $addTime[4] == "pm" ) 
		{
			$timeStr .= " 下午";
		}
	}
	return $timeStr;
}
function articleToXml($data) 
{
	include_once(EXTEND_PATH . "html2wxml/class.ToWXML.php");
	$content = htmlspecialchars_decode($data);
	if( $content != strip_tags($content) ) 
	{
	}
	else 
	{
		$content = "<p><span style=\"color: rgb(0, 0, 0);\">" . $content . "</span></p>";
	}
	$towxml = new ToWXML();
	$json = $towxml->towxml($content, array( "type" => "html", "highlight" => true, "linenums" => true, "imghost" => NULL, "encode" => false, "highlight_languages" => array( "html", "js", "php", "css" ) ));
	return $json;
}
?>