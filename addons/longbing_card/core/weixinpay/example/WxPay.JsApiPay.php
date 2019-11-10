<?php  require_once(ROOT_PATH . "/weixinpay/lib/WxPay.Api.php");
class JsApiPay 
{
	public $data = NULL;
	public function GetOpenid() 
	{
		if( !isset($_GET["code"]) ) 
		{
			$baseUrl = urlencode("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
			$url = $this->__CreateOauthUrlForCode($baseUrl);
			Header("Location: " . $url);
			exit();
		}
		$code = $_GET["code"];
		$openid = $this->getOpenidFromMp($code);
		return $openid;
	}
	public function GetJsApiParameters($UnifiedOrderResult) 
	{
		if( !array_key_exists("appid", $UnifiedOrderResult) || !array_key_exists("prepay_id", $UnifiedOrderResult) || $UnifiedOrderResult["prepay_id"] == "" ) 
		{
			throw new WxPayException("参数错误");
		}
		$jsapi = new WxPayJsApiPay();
		$jsapi->SetAppid($UnifiedOrderResult["appid"]);
		$timeStamp = time();
		$jsapi->SetTimeStamp((string) $timeStamp);
		$jsapi->SetNonceStr(WxPayApi::getNonceStr());
		$jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult["prepay_id"]);
		$jsapi->SetSignType("MD5");
		$jsapi->SetPaySign($jsapi->MakeSign());
		$parameters = json_encode($jsapi->GetValues());
		return $parameters;
	}
	public function GetOpenidFromMp($code) 
	{
		$url = $this->__CreateOauthUrlForOpenid($code);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if( WX_CURL_PROXY_HOST != "0.0.0.0" && WX_CURL_PROXY_PORT != 0 ) 
		{
			curl_setopt($ch, CURLOPT_PROXY, WX_CURL_PROXY_HOST);
			curl_setopt($ch, CURLOPT_PROXYPORT, WX_CURL_PROXY_PORT);
		}
		$res = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($res, true);
		$this->data = $data;
		$openid = $data["openid"];
		return $openid;
	}
	private function ToUrlParams($urlObj) 
	{
		$buff = "";
		foreach( $urlObj as $k => $v ) 
		{
			if( $k != "sign" ) 
			{
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}
	public function GetEditAddressParameters() 
	{
		$getData = $this->data;
		$data = array( );
		$data["appid"] = WX_APPID;
		$data["url"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$time = time();
		$data["timestamp"] = (string) $time;
		$data["noncestr"] = "1234568";
		$data["accesstoken"] = $getData["access_token"];
		ksort($data);
		$params = $this->ToUrlParams($data);
		$addrSign = sha1($params);
		$afterData = array( "addrSign" => $addrSign, "signType" => "sha1", "scope" => "jsapi_address", "appId" => WX_APPID, "timeStamp" => $data["timestamp"], "nonceStr" => $data["noncestr"] );
		$parameters = json_encode($afterData);
		return $parameters;
	}
	private function __CreateOauthUrlForCode($redirectUrl) 
	{
		$urlObj["appid"] = WX_APPID;
		$urlObj["redirect_uri"] = (string) $redirectUrl;
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE" . "#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
	}
	private function __CreateOauthUrlForOpenid($code) 
	{
		$urlObj["appid"] = WX_APPID;
		$urlObj["secret"] = WX_APPSECRET;
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
	}
}
?>