<?php  namespace think\exception;
class DbException extends \think\Exception 
{
	public function __construct($message, array $config, $sql, $code = 10500) 
	{
		$this->message = $message;
		$this->code = $code;
		$this->setData("Database Status", array( "Error Code" => $code, "Error Message" => $message, "Error SQL" => $sql ));
		unset($config["username"]);
		unset($config["password"]);
		$this->setData("Database Config", $config);
	}
}
?>