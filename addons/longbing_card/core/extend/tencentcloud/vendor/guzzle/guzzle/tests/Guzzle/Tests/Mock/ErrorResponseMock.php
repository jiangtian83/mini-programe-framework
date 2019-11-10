<?php  namespace Guzzle\Tests\Mock;
class ErrorResponseMock extends \Exception implements \Guzzle\Plugin\ErrorResponse\ErrorResponseExceptionInterface 
{
	public $command = NULL;
	public $response = NULL;
	public static function fromCommand(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\Response $response) 
	{
		return new self($command, $response);
	}
	public function __construct($command, $response) 
	{
		$this->command = $command;
		$this->response = $response;
		$this->message = "Error from " . $response;
	}
}
?>