<?php  namespace app\base\controller;
class User extends BaseApi 
{
	protected $token_salt = "gaoapp.com";
	protected $token_expire_in = 36000;
	public function update() 
	{
		$uid = $this->getUid();
		$uniacid = input("param.i");
		$userModel = new \app\base\model\BaseUser();
		$user_now = $userModel->get(array( "uniacid" => $uniacid, "id" => $uid ));
		if( !$user_now ) 
		{
			resultJsonStr($this->error_array["error0"], "未找到用户", array( ));
		}
		$rule = array( "nickName" => "require", "avatarUrl" => "require", "city" => "", "province" => "", "country" => "", "gender" => 0, "language" => "" );
		$params = $this->getParams($rule);
		if( !$params ) 
		{
			resultJsonStr($this->error_array["error0"], "请求参数错误", array( ));
		}
		$result = $user_now->save($params, array( "id" => $uid ));
		resultJsonStr($this->error_array["error0"], "suc", array( ));
	}
	public function save() 
	{
		$uid = $this->getUid();
		$uniacid = input("param.i");
		$pid = input("param.pid");
		$pid = intval($pid);
		if( !$pid ) 
		{
			resultJsonStr($this->error_array["error0"], "请求参数错误", array( ));
		}
		$userModel = new \app\base\model\BaseUser();
		$user_now = $userModel->get(array( "uniacid" => $uniacid, "id" => $uid ));
		if( !$user_now ) 
		{
			resultJsonStr($this->error_array["error0"], "fail", array( ));
		}
		$user_p = $userModel->get(array( "uniacid" => $uniacid, "id" => $pid ));
		if( !$user_p ) 
		{
			resultJsonStr($this->error_array["error0"], "fail", array( ));
		}
		$relationModel = new \app\base\model\BaseRelationship();
		$relationship = $relationModel->get(array( "user_id" => $uid ));
		if( $relationship ) 
		{
			resultJsonStr($this->error_array["error0"], "已经绑定上线了", array( ));
		}
		if( $pid == 0 ) 
		{
			resultJsonStr($this->error_array["error0"], "success", $user_now);
		}
		$relationModel->data(array( "user_id" => $uid, "p_id" => $pid, "uniacid" => $uniacid ));
		$relationModel->save();
		resultJsonStr($this->error_array["error0"], "success", $user_now);
	}
	public function createCard() 
	{
		$uid = $this->getUid();
		$uniacid = input("param.i");
		$userModel = new \app\base\model\BaseUser();
		$user_now = $userModel->get(array( "uniacid" => $uniacid, "id" => $uid ));
		if( !$user_now ) 
		{
			resultJsonStr($this->error_array["error-1"], "fail", array( ));
		}
		if( !$user_now["company_id"] ) 
		{
			resultJsonStr($this->error_array["error-1"], "请求参数错误", array( ));
		}
		$rule = array( "avatar" => "", "name" => "require", "job" => "require", "phone" => "", "wechat" => "", "tel" => "", "email" => "", "address" => "", "desc" => "", "voice" => "", "images" => "", "longitude" => "", "latitude" => "" );
		$params = $this->getParams($rule);
		if( !$params ) 
		{
			resultJsonStr($this->error_array["error-1"], "请求参数错误", array( ));
		}
		$params["user_id"] = $uid;
		$params["company_id"] = $user_now["company_id"];
		$params["uniacid"] = $uniacid;
		$cardModel = new MultiCard();
		$card_info = $cardModel->get(array( "user_id" => $uid, "company_id" => $user_now["company_id"], "uniacid" => $uniacid ));
		if( !$card_info ) 
		{
			$cardModel = new MultiCard();
			$cardModel->data($params);
			$result = $cardModel->save();
		}
		else 
		{
			$result = $card_info->save($params);
		}
		resultJsonStr($this->error_array["error0"], "suc", array( ));
	}
	public function card() 
	{
		$uniacid = input("param.i");
		$company_id = input("param.company_id");
		$uid = $this->getUid();
		$userModel = new \app\base\model\BaseUser();
		$user_now = $userModel->get(array( "uniacid" => $uniacid, "id" => $uid ));
		if( !$user_now ) 
		{
			resultJsonStr($this->error_array["error-1"], "未找到用户", array( ));
		}
		$where = array( "a.id" => $uid, "a.uniacid" => $uniacid, "a.status" => 1, "b.id" => $company_id, "b.company_id" => $company_id );
		$field = "a.id as user_id, a.nickName, a.avatarUrl, a.avatarUrlLocal, \r\n        b.id as card_id, b.avatar, b.name, b.job, b.phone, b.wechat, b.tel, b.email, \r\n        b.address, b.desc, b.voice, b.images, b.view, b.thumbs, b.status, c.name as company_name";
		$info = $userModel->alias("a")->join("longbing_multi_card b", "a.id = b.user_id", "left")->join("longbing_multi_company c", "b.company_id = c.id", "left")->field($field)->where($where)->find();
		if( !$info ) 
		{
			resultJsonStr($this->error_array["error-1"], "未找到名片", array( ));
		}
		$info = formatImage($info, array( "avatar", "voice", "images" ), 0, $uniacid);
		resultJsonStr($this->error_array["error0"], "suc", $info);
	}
	public function info() 
	{
		$uniacid = input("param.i");
		$uid = $this->getUid();
		$userModel = new \app\base\model\BaseUser();
		$user = $userModel->get($uid);
		$user = lb_object_to_array_z($user);
		$userCompanyModel = new \app\card\model\CardUserCompany();
		$normal_card = $userCompanyModel->alias("a")->join("lb_card_company b", "a.company_id = b.id", "left")->join("lb_card_card c", "c.company_id = b.id && a.user_id = c.user_id", "left")->field("c.id as card_id, c.company_id, c.avatar as card_avatar, c.name as card_name, a.type")->where(array( "a.status" => 1, "a.user_id" => $uid, "a.uniacid" => $uniacid, "a.type" => array( "in", array( 1, 2, 3 ) ), "b.status" => 1, "c.status" => 1 ))->select();
		$normal_card = lb_object_to_array_z($normal_card);
		$user["normal_card"] = count($normal_card);
		$info = $userCompanyModel->get(array( "user_id" => $uid, "lock" => 1, "type" => array( "in", array( 1, 2, 3 ) ) ));
		$user["company_id"] = 0;
		$user["type"] = 0;
		$user["card_id"] = 0;
		if( $info ) 
		{
			$company_id = $info["company_id"];
			$user["company_id"] = $company_id;
			$user["type"] = $info["type"];
			$cardModel = new \app\card\model\CardCard();
			$card = $cardModel->get(array( "user_id" => $uid, "company_id" => $info["company_id"] ));
			if( $card ) 
			{
				$user["card_id"] = $card["id"];
				$user["card_avatar"] = $card["avatar"];
				$user["card_name"] = $card["name"];
				$user = formatImage($user, array( "card_avatar" ), $uniacid);
			}
		}
		resultJsonStr($this->error_array["error0"], "suc", $user);
	}
	public function phone() 
	{
		$uniacid = input("param.i");
		$uid = $this->getUid();
		$rule = array( "encryptedData" => "require", "iv" => "require" );
		$params = $this->getParams($rule);
		if( !$params ) 
		{
			resultJsonStr($this->error_array["error-1"], "请求参数错误", array( ));
		}
		$configModel = new \app\base\model\BaseConfig();
		$config = $configModel->get(array( "uniacid" => $uniacid ));
		if( !$config ) 
		{
			resultJsonStr($this->error_array["error-1"], "未找到配置, 请联系管理员", array( ));
		}
		$appid = $config["appid"];
		$appsecret = $config["appsecret"];
		if( !$appid || !$appsecret ) 
		{
			resultJsonStr($this->error_array["error-1"], "appid或者appsecret未配置, 请联系管理员", array( ));
		}
		$userModel = new \app\base\model\BaseUser();
		$user = $userModel->get($uid);
		if( !$user ) 
		{
			resultJsonStr($this->error_array["error-1"], "未找到用户数据", array( ));
		}
		if( $user["phone"] ) 
		{
			resultJsonStr($this->error_array["error0"], "suc", $user);
		}
		if( !$user["session_key"] ) 
		{
			resultJsonStr($this->error_array["error-1"], "未找到用户session_key", array( ));
		}
		$session_key = $user["session_key"];
		include_once(ROOT_PATH . "/wxBizDataCrypt.php");
		$pc = new \WXBizDataCrypt($appid, $session_key);
		$data = null;
		$errCode = $pc->decryptData($params["encryptedData"], $params["iv"], $data);
		if( $errCode == 0 ) 
		{
			$data = json_decode($data, true);
		}
		else 
		{
			resultJsonStr($this->error_array["error-1"], "解密手机号失败", array( ));
		}
		$phone = $data["purePhoneNumber"];
		$user->phone = $phone;
		$result = $user->save();
		if( $result ) 
		{
			resultJsonStr($this->error_array["error0"], "suc", $user);
		}
		resultJsonStr($this->error_array["error-1"], "保存手机号失败", array( ));
	}
}
?>