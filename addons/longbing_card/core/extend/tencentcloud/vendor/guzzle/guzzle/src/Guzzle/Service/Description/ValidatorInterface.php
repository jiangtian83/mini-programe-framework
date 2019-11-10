<?php  namespace Guzzle\Service\Description;
interface ValidatorInterface 
{
	public function validate(Parameter $param, &$value);
	public function getErrors();
}
?>