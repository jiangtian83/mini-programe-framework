<?php  namespace app\common\controller;
class Base extends \think\Controller 
{
	public $auth_company_limit = 0;
	public $auth_staff_limit = 0;
	public function _initialize() 
	{
	}
	public function test() 
	{
		echo 11;
		exit();
	}
	public function recordCompanyId($company_id, $uniacid) 
	{
		$companyModel = new \app\card\model\CardCompany();
		$company = $companyModel->get($company_id);
		if( $company ) 
		{
			$this->recordCardId($company_id, $company["create_user"], $uniacid);
		}
		$model = new \app\base\model\BasePutin();
		$count = $model->where(array( "putin" => $company_id, "uniacid" => $uniacid ))->count();
		if( $count ) 
		{
			return true;
		}
		$model->c_id = $company_id;
		$model->uniacid = $uniacid;
		$model->status = 1;
		$model->save();
		return true;
	}
	public function checkCompanyNumber($uniacid) 
	{
		if( $this->auth_company_limit ) 
		{
			$model = new \app\base\model\BasePutin();
			$count = $model->where(array( "uniacid" => $uniacid ))->count();
			if( $this->auth_company_limit <= $count ) 
			{
				return false;
			}
		}
		return true;
	}
	public function recordCardId($company_id, $user_id, $uniacid) 
	{
		$cardModel = new \app\card\model\CardCard();
		$card = $cardModel->get(array( "user_id" => $user_id, "company_id" => $company_id ));
		if( !$card ) 
		{
			return false;
		}
		$model = new \app\base\model\BaseTrump();
		$count = $model->where(array( "trump" => $user_id, "melania" => $company_id, "uniacid" => $uniacid ))->count();
		if( $count ) 
		{
			return true;
		}
		$model->trump = $user_id;
		$model->melania = $company_id;
		$model->uniacid = $uniacid;
		$model->status = 1;
		$model->save();
		return true;
	}
	public function checkCardNumber($company_id, $uniacid) 
	{
		$companyModel = new \app\card\model\CardCompany();
		$company = $companyModel->get($company_id);
		if( !$company ) 
		{
			return false;
		}
		if( $this->auth_staff_limit ) 
		{
			$model = new \app\base\model\BaseTrump();
			$count = $model->where(array( "melania" => $company_id, "uniacid" => $uniacid ))->count();
			if( $this->auth_staff_limit <= $count ) 
			{
				return false;
			}
		}
		return true;
	}
	public function clearCache($uniacid) 
	{
		cache($_SERVER["HTTP_HOST"] . "config_check_init" . $uniacid, null);
		cache($_SERVER["HTTP_HOST"] . "config" . $uniacid, null);
	}
}
?>