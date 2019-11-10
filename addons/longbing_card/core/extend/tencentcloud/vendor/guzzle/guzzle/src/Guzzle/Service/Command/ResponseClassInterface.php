<?php  namespace Guzzle\Service\Command;
interface ResponseClassInterface 
{
	public static function fromCommand(OperationCommand $command);
}
?>