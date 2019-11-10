<?php  namespace think\exception;
class ErrorException extends \think\Exception 
{
	protected $severity = NULL;
	public function __construct($severity, $message, $file, $line, array $context = array( )) 
	{
		$this->severity = $severity;
		$this->message = $message;
		$this->file = $file;
		$this->line = $line;
		$this->code = 0;
		empty($context) or $this->setData("Error Context", $context);
	}
	final public function getSeverity() 
	{
		return $this->severity;
	}
}
?>