<?php  namespace think\db\exception;
class ModelNotFoundException extends \think\exception\DbException 
{
	protected $model = NULL;
	public function __construct($message, $model = "", array $config = array( )) 
	{
		$this->message = $message;
		$this->model = $model;
		$this->setData("Database Config", $config);
	}
	public function getModel() 
	{
		return $this->model;
	}
}
?>