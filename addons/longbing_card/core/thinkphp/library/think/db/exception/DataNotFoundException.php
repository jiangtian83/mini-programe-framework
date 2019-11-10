<?php  namespace think\db\exception;
class DataNotFoundException extends \think\exception\DbException 
{
	protected $table = NULL;
	public function __construct($message, $table = "", array $config = array( )) 
	{
		$this->message = $message;
		$this->table = $table;
		$this->setData("Database Config", $config);
	}
	public function getTable() 
	{
		return $this->table;
	}
}
?>