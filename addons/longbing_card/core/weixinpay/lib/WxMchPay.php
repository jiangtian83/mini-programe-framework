<?php  require_once("WxPay.Data.php");
class WxMchPay extends WxPayDataBase 
{
	public function MchPayOrder($openid, $money, $payId) 
	{
		$this->values["mch_appid"] = WX_APPID;
		$this->values["mchid"] = WX_MCHID;
		$this->values["nonce_str"] = self::getNonceStr();
		$this->values["partner_trade_no"] = $payId . date("YmdHis", $_SERVER["REQUEST_TIME"]);
		$this->values["openid"] = $openid;
		$this->values["check_name"] = "NO_CHECK";
		$this->values["amount"] = $money;
		$this->values["desc"] = "提现";
		$this->values["spbill_create_ip"] = gethostbyname($_SERVER["SERVER_NAME"]);
		$this->SetSign();
		$api = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
		$xml = $this->ToXml();
		$response = self::postXmlCurl($xml, $api, true);
		return $this->FromXml($response);
	}
	private static function postXmlCurl($xml, $url, $useCert = false, $second = 30) 
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		if( WX_CURL_PROXY_HOST != "0.0.0.0" && WX_CURL_PROXY_PORT != 0 ) 
		{
			curl_setopt($ch, CURLOPT_PROXY, WX_CURL_PROXY_HOST);
			curl_setopt($ch, CURLOPT_PROXYPORT, WX_CURL_PROXY_PORT);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if( $useCert == true ) 
		{
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, "PEM");
			curl_setopt($ch, CURLOPT_SSLCERT, WX_SSLCERT_PATH);
			curl_setopt($ch, CURLOPT_SSLKEYTYPE, "PEM");
			curl_setopt($ch, CURLOPT_SSLKEY, WX_SSLKEY_PATH);
		}
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$data = curl_exec($ch);
		if( $data ) 
		{
			curl_close($ch);
			return $data;
		}
		$error = curl_errno($ch);
		curl_close($ch);
		throw new WxPayException("curl出错，错误码:" . $error);
	}
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for( $i = 0; $i < $length; $i++ ) 
		{
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
}
?>