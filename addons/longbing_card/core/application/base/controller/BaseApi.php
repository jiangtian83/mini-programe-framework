<?php  namespace app\base\controller;
class BaseApi extends Token 
{
	protected $token = NULL;
	protected $error_array = array( "error-1" => -1, "error402" => 402, "error-2" => -2, "error401" => 401, "error10000" => 10000, "error0" => 0, "error400" => 400, "error-3" => -3 );
	protected $modular_array = array( "banner", "picture", "article", "staff", "honor", "contact" );
	protected $modular_array_sort = array( "banner", "picture", "article", "staff", "honor", "contact" );
	protected $request = NULL;
	protected $debug_switch = 0;
	protected $limit_10 = 10;
	protected $limit_15 = 15;
	protected $map = array( );
	public function _initialize() 
	{
		$uniacid = input("param.i");
		$this->map["uniacid"] = $uniacid;
		$this->request = \think\Request::instance();
		if( !$uniacid ) 
		{
			resultJsonStr($this->error_array["error-1"], "未传入uniacid", array( ));
		}
	}
	public function checkToken() 
	{
		if( !$this->token ) 
		{
			resultJsonStr(401, "需要登录", array( ));
		}
		$res = $this->getCacheByToken($this->token);
		if( !$res ) 
		{
			resultJsonStr(401, "用户标识错误");
		}
	}
	public function checkCard($uid) 
	{
		$uniacid = input("param.i");
		$cardModel = new MingpianCard();
		$card = $cardModel->where(array( "uid" => $uid, "uniacid" => $uniacid ))->find();
		if( $card && $card["status"] == 0 ) 
		{
			resultJsonStr(402, "forbidden", array( ));
		}
		return (isset($card["id"]) ? $card["id"] : 0);
	}
	public function getCacheByToken($token) 
	{
		$result = cache($token);
		if( $result ) 
		{
			$data = json_decode($result, true);
			return $data;
		}
		return $result;
	}
	public function getUid() 
	{
		$uniacid = input("param.i");
		$user = $this->getUser();
		$userModel = new \app\base\model\BaseUser();
		if( $userModel->get(array( "uniacid" => $uniacid, "id" => $user["uid"] )) ) 
		{
			return $user["uid"];
		}
		resultJsonStr(401, "需要登录", array( ));
	}
	public function getUser() 
	{
		$user = $this->getCacheByToken($this->token);
		return $user;
	}
	public function upload() 
	{
		$input = input();
		if( !isset($input["type"]) || !$input["type"] ) 
		{
			$type = "picture";
		}
		else 
		{
			$type = $input["type"];
		}
		$uniacid = $input["i"];
		$controller = new \app\common\controller\Upload();
		$return = $controller->upload($uniacid, $type);
		exit( json_encode($return) );
	}
	protected function createWeixinPay($uid, $body, $attach, $totalprice, $out_trade_no, $notify_url) 
	{
		global $_GPC;
		global $_W;
		$uniacid = input("param.i");
		$userModel = new \app\base\model\BaseUser();
		$user = $userModel->get($uid);
		if( !$user || !$user["openid"] ) 
		{
			return false;
		}
		$openid = $user["openid"];
		$config = \app\base\model\BaseConfig::get(array( "uniacid" => $uniacid, "status" => 1 ));
		if( !$config ) 
		{
			return false;
		}
		if( $config["debug_switch"] == 1 && (!$config["mini_mid"] || !$config["mini_apicode"]) ) 
		{
			resultJsonStr(-1, "请管理员配置支付数据！(如想关闭调试模式请在后台设置)", array( ));
		}
		$setting["mini_appid"] = $config["appid"];
		$setting["mini_appsecrept"] = $config["appsecret"];
		$setting["mini_mid"] = $config["mini_mid"];
		$setting["mini_apicode"] = $config["mini_apicode"];
		$setting["apiclient_cert"] = "";
		$setting["apiclient_cert_key"] = "";
		define("WX_APPID", $setting["mini_appid"]);
		define("WX_MCHID", $setting["mini_mid"]);
		define("WX_KEY", $setting["mini_apicode"]);
		define("WX_APPSECRET", $setting["mini_appsecrept"]);
		define("WX_SSLCERT_PATH", ROOT_PATH . "weixinpay/cert/" . $setting["apiclient_cert"]);
		define("WX_SSLKEY_PATH", ROOT_PATH . "weixinpay/cert/" . $setting["apiclient_cert_key"]);
		define("WX_CURL_PROXY_HOST", "0.0.0.0");
		define("WX_CURL_PROXY_PORT", 0);
		define("WX_REPORT_LEVENL", 0);
		require_once(ROOT_PATH . "weixinpay/lib/WxPay.Api.php");
		require_once(ROOT_PATH . "weixinpay/example/WxPay.JsApiPay.php");
		$tools = new \JsApiPay();
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($attach);
		$no = $out_trade_no;
		$input->SetOut_trade_no($no);
		$input->SetTotal_fee($totalprice);
		$input->SetTime_start(date("YmdHis"));
		$param_arr = array( "i" => $uniacid, "t" => $_GPC["t"], "v" => $_GPC["v"] );
		$reply_path = json_encode($param_arr);
		if( defined("IS_WEIQIN") ) 
		{
			$path = "https://" . $_SERVER["HTTP_HOST"] . "/addons/longbing_multi/wexinPay.php?params=" . $reply_path;
		}
		else 
		{
			$path = "https://" . $_SERVER["HTTP_HOST"] . "/wexinPay.php?params=" . $reply_path;
		}
		lb_logOutput("BaseApiPath:-----" . $path);
		$input->SetNotify_url($path);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openid);
		$order = \WxPayApi::unifiedOrder($input);
		if( $order["return_code"] == "FAIL" ) 
		{
			return array( "api_result" => false, "api_result_msg" => $order["return_msg"] );
		}
		$jsApiParameters = $tools->GetJsApiParameters($order);
		$jsApiParameters = json_decode($jsApiParameters, true);
		$baseOrder = new \app\base\model\BaseOrder();
		$baseOrder->user_id = $uid;
		$baseOrder->out_trade_no = $no;
		$baseOrder->body = $body;
		$baseOrder->attach = $attach;
		$baseOrder->price = $totalprice;
		$baseOrder->pay_status = 0;
		$baseOrder->uniacid = $uniacid;
		$baseOrder->save();
		return $jsApiParameters;
	}
	protected function crteateMchPay($uid, $money, $payid) 
	{
		global $_GPC;
		global $_W;
		$uniacid = input("param.i");
		$userModel = new MingpianUser();
		$openid = $userModel->getUserOpenidByUid($uid, $uniacid);
		if( !$openid ) 
		{
			return false;
		}
		$config = MingpianConfig::get(array( "uniacid" => $uniacid, "status" => 1 ));
		if( !$config ) 
		{
			return false;
		}
		if( $config["debug_switch"] == 1 ) 
		{
			if( !$config["mini_mid"] || !$config["mini_apicode"] ) 
			{
				resultJsonStr(-1, "请管理员配置支付数据！(如想关闭调试模式请在后台设置)", array( ));
			}
			if( !file_exists(ROOT_PATH . "weixinpay/cert/" . $uniacid . "_apiclient_cert.pem") || !file_exists(ROOT_PATH . "weixinpay/cert/" . $uniacid . "_apiclient_key.pem") ) 
			{
				resultJsonStr(-1, "请管理员上传微信商户证书！(如想关闭调试模式请在后台设置)", array( ));
			}
		}
		$setting["mini_appid"] = $config["appid"];
		$setting["mini_appsecrept"] = $config["appsecret"];
		$setting["mini_mid"] = $config["mini_mid"];
		$setting["mini_apicode"] = $config["mini_apicode"];
		$setting["apiclient_cert"] = $uniacid . "_apiclient_cert.pem";
		$setting["apiclient_cert_key"] = $uniacid . "_apiclient_key.pem";
		define("WX_APPID", $setting["mini_appid"]);
		define("WX_MCHID", $setting["mini_mid"]);
		define("WX_CURL_PROXY_HOST", "0.0.0.0");
		define("WX_CURL_PROXY_PORT", 0);
		define("WX_KEY", $setting["mini_apicode"]);
		define("WX_SSLCERT_PATH", ROOT_PATH . "weixinpay/cert/" . $setting["apiclient_cert"]);
		define("WX_SSLKEY_PATH", ROOT_PATH . "weixinpay/cert/" . $setting["apiclient_cert_key"]);
		require_once(ROOT_PATH . "weixinpay/lib/WxMchPay.php");
		$payClass = new \WxMchPay();
		$res = $payClass->MchPayOrder($openid, $money, $payid);
		return $res;
	}
	protected function getParams(array $rule) 
	{
		$tmp = array( );
		foreach( $rule as $index => $v ) 
		{
			if( $v ) 
			{
				$value = $this->request->param($index);
				$res = \think\Validate::is($value, $v);
				if( !$res ) 
				{
					return false;
				}
			}
			else 
			{
				$value = $this->request->param($index);
				if( !$value ) 
				{
					$value = $v;
				}
			}
			$tmp[$index] = $value;
		}
		return $tmp;
	}
	protected function checkCompanyAuth($company_id, $user_id = 0) 
	{
		if( !$user_id ) 
		{
			$user_id = $this->getUid();
		}
		$user_model = new \app\base\model\BaseUser();
		$user_info = $user_model->get($user_id);
		if( !$user_info ) 
		{
			return false;
		}
		$company_model = new \app\card\model\CardCompany();
		$company_info = $company_model->get($company_id);
		if( !$company_info ) 
		{
			return false;
		}
		$operator = explode(",", $company_info["operator"]);
		if( $company_info["admin_user"] == $user_id ) 
		{
			return 2;
		}
		if( in_array($user_id, $operator) ) 
		{
			return 1;
		}
		return false;
	}
	public function getCompanyIds($company_id) 
	{
		$company_model = new MultiCompany();
		$company_info = $company_model->get($company_id);
		if( !$company_info ) 
		{
			return array( );
		}
		$operator = $company_info["admin_user"] . "," . $company_info["operator"];
		$operator = trim($operator, ",");
		$operator = explode(",", $company_info["operator"]);
		return $operator;
	}
	public function pp($data) 
	{
		echo "<pre>";
		var_dump($data);
		exit();
	}
	public function notify() 
	{
		file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/data/tpl/web/nottttttfytpl2.txt", 123);
	}
	public function z_dump_l($data) 
	{
		echo "<pre>";
		var_dump($data);
	}
	public function z_dump_d($data) 
	{
		echo "<pre>";
		var_dump($data);
		exit();
	}
	protected function transImage($path) 
	{
		$path = tomedia($path);
		global $_GPC;
		global $_W;
		$arr = explode("/", $path);
		$fileName = "images/longbing_card/" . $_W["uniacid"] . "/" . $arr[count($arr) - 1];
		if( !is_dir(ATTACHMENT_ROOT . "/" . "images") ) 
		{
			mkdir(ATTACHMENT_ROOT . "/" . "images");
		}
		if( !is_dir(ATTACHMENT_ROOT . "/" . "images/longbing_card") ) 
		{
			mkdir(ATTACHMENT_ROOT . "/" . "images/longbing_card");
		}
		if( !is_dir(ATTACHMENT_ROOT . "/" . "images/longbing_card/" . $_W["uniacid"] . "/") ) 
		{
			mkdir(ATTACHMENT_ROOT . "/" . "images/longbing_card/" . $_W["uniacid"] . "/");
		}
		if( file_exists(ATTACHMENT_ROOT . $fileName) ) 
		{
			$path = $_W["siteroot"] . $_W["config"]["upload"]["attachdir"] . "/" . $fileName;
			$path = str_replace("ttp://", "ttps://", $path);
			if( !strstr($path, "ttps://") ) 
			{
				$path = "https://" . $path;
			}
			return $path;
		}
		if( !strstr($path, $_SERVER["HTTP_HOST"]) ) 
		{
			file_put_contents(ATTACHMENT_ROOT . "/" . $fileName, $this->http_file_get($path));
			$path = $_W["siteroot"] . $_W["config"]["upload"]["attachdir"] . "/" . $fileName;
		}
		else 
		{
			if( strstr($path, "." . $_SERVER["HTTP_HOST"]) ) 
			{
				file_put_contents(ATTACHMENT_ROOT . "/" . $fileName, $this->http_file_get($path));
				$path = $_W["siteroot"] . $_W["config"]["upload"]["attachdir"] . "/" . $fileName;
			}
			else 
			{
				$path = str_replace("ttp://", "ttps://", $path);
				if( !strstr($path, "ttps://") ) 
				{
					$path = "https://" . $path;
				}
			}
		}
		$path = str_replace("ttp://", "ttps://", $path);
		if( !strstr($path, "ttps://") ) 
		{
			$path = "https://" . $path;
		}
		return $path;
	}
	public function http_file_get($url, $data = NULL) 
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		if( !empty($data) ) 
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$info = curl_exec($curl);
		curl_close($curl);
		return $info;
	}
}
?>