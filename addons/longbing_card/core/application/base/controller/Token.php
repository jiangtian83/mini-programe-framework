<?php  namespace app\base\controller;
class Token extends \app\common\controller\Base 
{
	protected $token = NULL;
	public function _initialize() 
	{
		$this->token = $this->request->header("token");
		$this->checkToken();
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
}
?>