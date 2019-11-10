<?php  namespace think\exception;
class ClassNotFoundException extends \RuntimeException 
{
	protected $class = NULL;
	public function __construct($message, $class = "") 
	{
		$this->message = $message;
		$this->class = $class;
	}
	public function getClass() 
	{
		return $this->class;
	}
}
?>