<?php  namespace Guzzle\Service\Command;
interface RequestSerializerInterface 
{
	public function prepare(CommandInterface $command);
}
?>