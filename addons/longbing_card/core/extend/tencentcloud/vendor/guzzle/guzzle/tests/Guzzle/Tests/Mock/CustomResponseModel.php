<?php  namespace Guzzle\Tests\Mock;
class CustomResponseModel implements \Guzzle\Service\Command\ResponseClassInterface 
{
	public $command = NULL;
	public static function fromCommand(\Guzzle\Service\Command\OperationCommand $command) 
	{
		return new self($command);
	}
	public function __construct($command) 
	{
		$this->command = $command;
	}
}
?>