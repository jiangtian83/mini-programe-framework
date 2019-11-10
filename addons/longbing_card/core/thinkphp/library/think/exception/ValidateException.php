<?php  namespace think\exception;
class ValidateException extends \RuntimeException 
{
	protected $error = NULL;
	public function __construct($error) 
	{
		$this->error = $error;
		$this->message = (is_array($error) ? implode("\n\r", $error) : $error);
	}
	public function getError() 
	{
		return $this->error;
	}
}
?>