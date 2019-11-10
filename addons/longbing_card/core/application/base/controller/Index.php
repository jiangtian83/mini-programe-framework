<?php  namespace app\base\controller;
class Index extends BaseApi 
{
	public function index() 
	{
		$errno = 0;
		$message = "api/index/index";
		$data = array( );
		resultJsonStr($errno, $message, $data);
	}
	public function test() 
	{
		global $_GPC;
		global $_W;
		$errno = 1;
		$message = "api/index/test";
		$data = array( );
		resultJsonStr($errno, $message, $_GPC);
	}
	public function longbing() 
	{
		global $_GPC;
		global $_W;
		$errno = 0;
		$message = "base/index/longbing 成都龙兵科技有限公司";
		$data = array( );
		resultJsonStr($errno, $message, $_GPC);
	}
}
?>