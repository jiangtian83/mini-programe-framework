<?php  namespace Guzzle\Service\Exception;
class ValidationException extends \Guzzle\Common\Exception\RuntimeException 
{
	protected $errors = array( );
	public function setErrors(array $errors) 
	{
		$this->errors = $errors;
	}
	public function getErrors() 
	{
		return $this->errors;
	}
}
?>