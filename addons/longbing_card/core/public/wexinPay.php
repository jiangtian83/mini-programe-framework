<?php  lb_logoutput22("in--weixinPay");
$xmlData = file_get_contents("php://input");
if( empty($xmlData) ) 
{
	$xmlData = "empty  xmlData";
}
lb_logoutput22("xmlData in   weixinPay:-----" . $xmlData);
$xml_data = simplexml_load_string($xmlData);
$params = json_decode($xml_data->params, true);
$i = $params["i"];
unset($xml_data->params);
$xmlData = $xml_data->asXML();
lb_logoutput22("weixinPay-\$params:-----" . $params);
$data = $xmlData;
$reply_path = "https://" . $_SERVER["HTTP_HOST"] . "/index.php/base/other/notify/i/" . $i;
lb_api_notice_increment22($reply_path, $data);
function lb_api_notice_increment22($url, $data) 
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
function lb_logOutput22($data, $flag = 0) 
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
function lb_makeRequest22($url, $params = array( ), $expire = 0, $extend = array( ), $hostIp = "") 
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
?>