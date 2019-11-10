<?php  namespace Guzzle\Service\Command;
interface ResponseParserInterface 
{
	public function parse(CommandInterface $command);
}
?>