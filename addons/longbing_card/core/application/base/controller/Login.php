<?php  namespace app\base\controller;
class Login extends \app\common\controller\Base 
{
	protected $token_salt = "gaoapp.com";
	protected $token_expire_in = 36000;
	protected $token = NULL;
	protected $request = NULL;
	protected $error_array = array( "error-1" => -1, "error402" => 402, "error-2" => -2, "error401" => 401, "error10000" => 10000, "error0" => 0, "error400" => 400, "error-3" => -3 );
	const SALT = "longbing_multi";
	public function login() 
	{
		$code = input("param.code");
		$uniacid = input("param.i");
		if( !$code ) 
		{
			resultJsonStr($this->error_array["error-1"], "请求参数错误code", array( ));
		}
		if( !$uniacid ) 
		{
			resultJsonStr($this->error_array["error-1"], "请求参数错误uniacid", array( ));
		}
		$resultCache = cache($_SERVER["HTTP_HOST"] . "config_check_init" . $uniacid);
		if( $resultCache ) 
		{
			$config = json_decode($resultCache, true);
		}
		else 
		{
			$config = \app\base\model\BaseConfig::get(array( "uniacid" => $uniacid ));
		}
		$appid = $config["appid"];
		$appsecret = $config["appsecret"];
		if( !$appid || !$appsecret ) 
		{
			resultJsonStr($this->error_array["error-1"], "请管理员配置: APPID，APPSECRET！", array( ));
		}
		$time1 = microtime();
		$url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $appsecret . "&js_code=" . $code . "&grant_type=authorization_code";
		$info = file_get_contents($url);
		$time2 = microtime();
		$info = json_decode($info, true);
		if( isset($info["errcode"]) && $info["errcode"] ) 
		{
			resultJsonStr($info["errcode"], $info["errmsg"], array( $info ));
		}
		if( isset($info["openid"]) ) 
		{
			$openid = $info["openid"];
			$userModel = new \app\base\model\BaseUser();
			$user = $userModel->where(array( "uniacid" => $uniacid, "openid" => $openid ))->find();
			if( !$user ) 
			{
				$time = time();
				$uid = $userModel->insertGetId(array( "uniacid" => $uniacid, "openid" => $openid, "session_key" => $info["session_key"], "unionid" => (isset($info["unionid"]) ? $info["unionid"] : ""), "create_time" => $time, "update_time" => $time ));
			}
			else 
			{
				$uid = $user["id"];
				$unionid = (isset($info["unionid"]) ? $info["unionid"] : "");
				if( $unionid ) 
				{
					$userModel->update(array( "session_key" => $info["session_key"], "unionid" => $unionid ), array( "id" => $uid ));
				}
				else 
				{
					$userModel->update(array( "session_key" => $info["session_key"] ), array( "id" => $uid ));
				}
			}
			$info["uid"] = $uid;
			$token = $this->getToken($info);
			if( !$token ) 
			{
				resultJsonStr($this->error_array["error-1"], "服务端缓存错误", array( ));
			}
			$user_info = $userModel->get($uid);
			$user_info = lb_format_time($user_info, array( "create_time" ));
			$user_info["token"] = $token;
			$radar_token = substr(md5($uid . $uniacid . self::SALT), 0, 16);
			\think\Cache::store("redis")->set($radar_token, json_encode(array( "id" => $uid )));
			$user_info["radar_token"] = $radar_token;
			$user_info["time1"] = $time1;
			$user_info["time2"] = $time2;
			$user_info["time3"] = $this->getMicTime($time1, $time2);
			resultJsonStr($this->error_array["error0"], "success", $user_info);
		}
		else 
		{
			if( $this->debug_switch ) 
			{
				resultJsonStr($this->error_array["error-1"], "没有获取到openid", array( ));
			}
			resultJsonStr($this->error_array["error-1"], "", array( ));
		}
	}
	protected function getMicTime($time1, $time2) 
	{
		$time1Arr = explode(" ", $time1);
		$time2Arr = explode(" ", $time2);
		$time = ($time2Arr[1] + $time2Arr[0]) - ($time1Arr[1] + $time1Arr[0]);
		$time = round($time, 3);
		$time = $time * 1000;
		return $time;
	}
	private function getToken($info) 
	{
		$timestamp = $_SERVER["REQUEST_TIME_FLOAT"];
		$tokenSalt = $this->token_salt;
		$key = md5($timestamp . $tokenSalt);
		$value = json_encode($info);
		$expire_in = $this->token_expire_in;
		$result = cache($key, $value, $expire_in);
		if( $result ) 
		{
			return $key;
		}
		return false;
	}
}
?>