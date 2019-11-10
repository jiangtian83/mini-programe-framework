<?php  namespace think\exception;
class TemplateNotFoundException extends \RuntimeException 
{
	protected $template = NULL;
	public function __construct($message, $template = "") 
	{
		$this->message = $message;
		$this->template = $template;
	}
	public function getTemplate() 
	{
		return $this->template;
	}
}
?>