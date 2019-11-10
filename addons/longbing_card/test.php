<?php  namespace app\longbing_auth\admin;
class Up extends Base 
{
	public function _initialize() 
	{
		parent::_initialize();
	}
	public function index() 
	{
		$map = $this->getMap();
		$order = $this->getOrder();
		$data_list = \app\longbing_auth\model\Up::where($map)->order($order)->paginate();
		return \app\common\builder\ZBuilder::make("table")->setSearch(array( "up_http" => "域名" ))->setHeight("auto")->addColumns(array( array( "id", "ID" ), array( "up_http", "域名", "text.edit" ), array( "up_version", "版本", "text" ), array( "up_card_count", "创建名片数量", "text" ), array( "up_company_count", "开小程序数量", "text" ), array( "update_time", "最后上报时间", "datetime" ) ))->addOrder(array( "id", "update_time" ))->setRowList($data_list)->fetch();
	}
}
?>