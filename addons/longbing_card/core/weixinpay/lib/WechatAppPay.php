<?php  class wechatAppPay 
{
	private $wxappid = NULL;
	private $mch_id = NULL;
	private $nonce_str = NULL;
	private $sign = NULL;
	private $body = NULL;
	private $out_trade_no = NULL;
	private $total_fee = NULL;
	private $spbill_create_ip = NULL;
	private $notify_url = NULL;
	private $trade_type = NULL;
	private $key = NULL;
	private $SSLCERT_PATH = NULL;
	private $SSLKEY_PATH = NULL;
	private $params = array( );
	const API_URL_PREFIX = "https://api.mch.weixin.qq.com";
	const UNIFIEDORDER_URL = "/pay/unifiedorder";
	const ORDERQUERY_URL = "/pay/orderquery";
	const CLOSEORDER_URL = "/pay/closeorder";
	public function __construct($wxappid, $mch_id, $notify_url, $key) 
	{
		$this->appid = $wxappid;
		$this->mch_id = $mch_id;
		$this->notify_url = $notify_url;
		$this->key = $key;
	}
	public function unifiedOrder($params) 
	{
		$this->body = $params["body"];
		$this->out_trade_no = $params["out_trade_no"];
		$this->total_fee = $params["total_fee"];
		$this->trade_type = $params["trade_type"];
		$this->nonce_str = $this->genRandomString();
		$this->spbill_create_ip = $_SERVER["REMOTE_ADDR"];
		$this->params["appid"] = $this->appid;
		$this->params["mch_id"] = $this->mch_id;
		$this->params["nonce_str"] = $this->nonce_str;
		$this->params["body"] = $this->body;
		$this->params["out_trade_no"] = $this->out_trade_no;
		$this->params["total_fee"] = $this->total_fee;
		$this->params["spbill_create_ip"] = $this->spbill_create_ip;
		$this->params["notify_url"] = $this->notify_url;
		$this->params["trade_type"] = $this->trade_type;
		$this->sign = $this->MakeSign($this->params);
		$this->params["sign"] = $this->sign;
		$xml = $this->data_to_xml($this->params);
		$response = $this->postXmlCurl($xml, self::API_URL_PREFIX . self::UNIFIEDORDER_URL);
		if( !$response ) 
		{
			return false;
		}
		$result = $this->xml_to_data($response);
		if( !empty($result["result_code"]) && !empty($result["err_code"]) ) 
		{
			$result["err_msg"] = $this->error_code($result["err_code"]);
		}
		return $result;
	}
	public function orderQuery($out_trade_no) 
	{
		$this->params["appid"] = $this->appid;
		$this->params["mch_id"] = $this->mch_id;
		$this->params["nonce_str"] = $this->genRandomString();
		$this->params["out_trade_no"] = $out_trade_no;
		$this->sign = $this->MakeSign($this->params);
		$this->params["sign"] = $this->sign;
		$xml = $this->data_to_xml($this->params);
		$response = $this->postXmlCurl($xml, self::API_URL_PREFIX . self::ORDERQUERY_URL);
		if( !$response ) 
		{
			return false;
		}
		$result = $this->xml_to_data($response);
		if( !empty($result["result_code"]) && !empty($result["err_code"]) ) 
		{
			$result["err_msg"] = $this->error_code($result["err_code"]);
		}
		return $result;
	}
	public function closeOrder($out_trade_no) 
	{
		$this->params["appid"] = $this->appid;
		$this->params["mch_id"] = $this->mch_id;
		$this->params["nonce_str"] = $this->genRandomString();
		$this->params["out_trade_no"] = $out_trade_no;
		$this->sign = $this->MakeSign($this->params);
		$this->params["sign"] = $this->sign;
		$xml = $this->data_to_xml($this->params);
		$response = $this->postXmlCurl($xml, self::API_URL_PREFIX . self::CLOSEORDER_URL);
		if( !$response ) 
		{
			return false;
		}
		$result = $this->xml_to_data($response);
		return $result;
	}
	public function getNotifyData() 
	{
		$xml = $GLOBALS["HTTP_RAW_POST_DATA"];
		$data = array( );
		if( empty($xml) ) 
		{
			return false;
		}
		$data = $this->xml_to_data($xml);
		if( !empty($data["return_code"]) && $data["return_code"] == "FAIL" ) 
		{
			return false;
		}
		return $data;
	}
	public function replyNotify() 
	{
		$data["return_code"] = "SUCCESS";
		$data["return_msg"] = "OK";
		$xml = $this->data_to_xml($data);
		echo $xml;
		exit();
	}
	public function getAppPayParams($prepayid) 
	{
		$data["appid"] = $this->appid;
		$data["partnerid"] = $this->mch_id;
		$data["prepayid"] = $prepayid;
		$data["package"] = "Sign=WXPay";
		$data["noncestr"] = $this->genRandomString();
		$data["timestamp"] = time();
		$data["sign"] = $this->MakeSign($data);
		return $data;
	}
	public function MakeSign($params) 
	{
		ksort($params);
		$string = $this->ToUrlParams($params);
		$string = $string . "&key=" . $this->key;
		$string = md5($string);
		$result = strtoupper($string);
		return $result;
	}
	public function ToUrlParams($params) 
	{
		$string = "";
		if( !empty($params) ) 
		{
			$array = array( );
			foreach( $params as $key => $value ) 
			{
				$array[] = $key . "=" . $value;
			}
			$string = implode("&", $array);
		}
		return $string;
	}
	public function data_to_xml($params) 
	{
		if( !is_array($params) || count($params) <= 0 ) 
		{
			return false;
		}
		$xml = "<xml>";
		foreach( $params as $key => $val ) 
		{
			if( is_numeric($val) ) 
			{
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			}
			else 
			{
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}
	public function xml_to_data($xml) 
	{
		if( !$xml ) 
		{
			return false;
		}
		libxml_disable_entity_loader(true);
		$data = json_decode(json_encode(simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA)), true);
		return $data;
	}
	private static function getMillisecond() 
	{
		$time = explode(" ", microtime());
		$time = $time[1] . $time[0] * 1000;
		$time2 = explode(".", $time);
		$time = $time2[0];
		return $time;
	}
	private function genRandomString($len = 32) 
	{
		$chars = array( "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9" );
		$charsLen = count($chars) - 1;
		shuffle($chars);
		$output = "";
		for( $i = 0; $i < $len; $i++ ) 
		{
			$output .= $chars[mt_rand(0, $charsLen)];
		}
		return $output;
	}
	private function postXmlCurl($xml, $url, $useCert = false, $second = 30) 
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if( $useCert == true ) 
		{
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, "PEM");
			curl_setopt($ch, CURLOPT_SSLKEYTYPE, "PEM");
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
		return false;
	}
	public function error_code($code) 
	{
		$errList = array( "NOAUTH" => "商户未开通此接口权限", "NOTENOUGH" => "用户帐号余额不足", "ORDERNOTEXIST" => "订单号不存在", "ORDERPAID" => "商户订单已支付，无需重复操作", "ORDERCLOSED" => "当前订单已关闭，无法支付", "SYSTEMERROR" => "系统错误!系统超时", "APPID_NOT_EXIST" => "参数中缺少APPID", "MCHID_NOT_EXIST" => "参数中缺少MCHID", "APPID_MCHID_NOT_MATCH" => "appid和mch_id不匹配", "LACK_PARAMS" => "缺少必要的请求参数", "OUT_TRADE_NO_USED" => "同一笔交易不能多次提交", "SIGNERROR" => "参数签名结果不正确", "XML_FORMAT_ERROR" => "XML格式错误", "REQUIRE_POST_METHOD" => "未使用post传递参数 ", "POST_DATA_EMPTY" => "post数据不能为空", "NOT_UTF8" => "未使用指定编码格式" );
		if( array_key_exists($code, $errList) ) 
		{
			return $errList[$code];
		}
	}
}
?>