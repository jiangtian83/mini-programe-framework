<?php  namespace think\db\exception;
class BindParamException extends \think\exception\DbException 
{
	public function __construct($message, $config, $sql, $bind, $code = 10502) 
	{
		$this->setData("Bind Param", $bind);
		parent::__construct($message, $config, $sql, $code);
	}
}
?>