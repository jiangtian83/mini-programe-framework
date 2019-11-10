<?php  namespace app\payqr\controller;
class Payqr extends \app\base\controller\BaseApi 
{
	protected $request = NULL;
	protected $user_id = NULL;
	public function __construct(\think\Request $request = NULL) 
	{
		$request = \think\Request::instance();
		$this->request = $request;
		$user_id = $this->user_id = $request->post("user_id");
		if( !$user_id ) 
		{
			resultJsonStr(-1, "请传入用户数据", array( "msg" => "请传入用户数据" . $user_id ));
		}
		parent::__construct($request);
	}
	public function config() 
	{
		$uniacid = $this->request->param("i");
		$to_uid = $this->request->post("to_uid");
		$user_id = $this->request->post("user_id");
		$phoneModel = new \app\base\model\CardUserPhone();
		$phone = $phoneModel->get(array( "user_id" => $user_id ));
		$userInfoModel = new \app\base\model\CardUserInfo();
		$staff = $userInfoModel->get(array( "fans_id" => $to_uid ));
		if( !$staff ) 
		{
			resultJsonStr($this->error_array["error-1"], "未找到员工信息", array( "msg" => "未找到员工信息" ));
		}
		$companyModel = new \app\base\model\CardCompany();
		$company = "";
		if( $staff["company_id"] ) 
		{
			$company = $companyModel->get($staff["company_id"]);
			if( !$company ) 
			{
				$company = $companyModel->get(array( "uniacid" => $uniacid ));
			}
		}
		else 
		{
			$company = $companyModel->get(array( "uniacid" => $uniacid ));
		}
		$configModel = new \app\payqr\model\PayqrConfig();
		$config = $configModel->get(array( "uniacid" => $uniacid ));
		if( !$config ) 
		{
			$configModel->title = "支付";
			$configModel->uniacid = $uniacid;
			$configModel->save();
			$config = $configModel->get(array( "uniacid" => $uniacid ));
		}
		$config["is_first"] = 0;
		$recordModel = new \app\payqr\model\PayqrRecord();
		$check_record = $recordModel->get(array( "user_id" => $this->user_id ));
		if( !$check_record ) 
		{
			$config["is_first"] = 1;
		}
		if( !$config["company_name"] && $company ) 
		{
			$config["company_name"] = $company["name"];
		}
		if( !$config["company_logo"] && $company ) 
		{
			$config["company_logo"] = $company["logo"];
		}
		$config["company_logo"] = tomedia($config["company_logo"]);
		$config["phone"] = "";
		if( $phone ) 
		{
			$config["phone"] = $phone["phone"];
			$config["is_first"] = 0;
		}
		resultJsonStr($this->error_array["error0"], "suc", $config);
	}
	public function payqr() 
	{
		$uniacid = $this->request->param("i");
		$user_id = $this->request->post("user_id");
		$to_uid = $this->request->post("to_uid");
		$is_first = $this->request->post("is_first");
		$money = $this->request->post("money");
		if( !$user_id || !$to_uid ) 
		{
			return $this->result(-1, "fail pra", array( ));
		}
		$user = pdo_get("longbing_card_user", array( "id" => $user_id, "uniacid" => $uniacid ));
		if( !$user ) 
		{
			resultJsonStr($this->error_array["error-1"], "未找到用户信息", array( "msg" => "未找到用户信息" ));
		}
		if( $is_first ) 
		{
			$configModel = new \app\payqr\model\PayqrConfig();
			$config = $configModel->get(array( "uniacid" => $uniacid ));
			if( $config && $config["first_full"] && $config["first_full"] <= $money ) 
			{
				$money = $money - $config["first_reduce"];
				$money = floatval($money);
				$money = sprintf("%.2f", $money);
			}
		}
		$out_trade_no = "p-" . date("Ymd") . uniqid();
		$orderPay = array( "tid" => $out_trade_no, "user" => $user["openid"], "fee" => floatval($money), "title" => "wechat" );
		$pay_params = $this->pay($orderPay);
		if( is_error($pay_params) ) 
		{
			resultJsonStr($this->error_array["error-1"], $pay_params["message"], $pay_params);
		}
		resultJsonStr($this->error_array["error0"], "suc", $pay_params);
	}
	public function payGoods() 
	{
		$uniacid = $this->request->param("i");
		$to_uid = $this->request->post("to_uid");
		$extensionModel = new \app\base\model\CardExtension();
		$extension = $extensionModel->where(array( "user_id" => $to_uid ))->select();
		if( !$extension ) 
		{
			$goodsModel = new \app\base\model\CardGoods();
			$goods = $goodsModel->where(array( "uniacid" => $uniacid, "status" => 1 ))->order(array( "top desc" ))->limit(4)->select();
		}
		else 
		{
			$ids = array( );
			foreach( $extension as $k => $v ) 
			{
				array_push($ids, $v["goods_id"]);
			}
			$ids = implode(",", $ids);
			if( 1 < count($extension) ) 
			{
				$ids = "(" . $ids . ")";
				$sql = "SELECT id,`name`,cover,price,status,unit FROM " . tablename("longbing_card_goods") . " WHERE id IN " . $ids . " && status = 1 ORDER BY top DESC";
			}
			else 
			{
				$sql = "SELECT id,`name`,cover,price,status,unit FROM " . tablename("longbing_card_goods") . " WHERE id = " . $ids . " && status = 1 ORDER BY top DESC";
			}
			$goods = \think\Db::query($sql);
		}
		foreach( $goods as $k => $v ) 
		{
			$goods[$k]["cover"] = tomedia($v["cover"]);
		}
		$carouselModel = new \app\payqr\model\PayqrCarousel();
		$carousel = $carouselModel->where(array( "uniacid" => $uniacid, "status" => 1 ))->order("top desc")->select();
		foreach( $carousel as $k => $v ) 
		{
			$carousel[$k]["img"] = tomedia($v["img"]);
		}
		$data = array( "goods" => $goods, "carousel" => $carousel );
		resultJsonStr($this->error_array["error0"], "suc", $data);
	}
}
?>