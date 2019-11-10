<?php  namespace Guzzle\Plugin\ErrorResponse;
interface ErrorResponseExceptionInterface 
{
	public static function fromCommand(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\Response $response);
}
?>