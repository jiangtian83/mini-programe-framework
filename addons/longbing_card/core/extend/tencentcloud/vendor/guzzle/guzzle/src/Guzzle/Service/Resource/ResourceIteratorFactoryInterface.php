<?php  namespace Guzzle\Service\Resource;
interface ResourceIteratorFactoryInterface 
{
	public function build(\Guzzle\Service\Command\CommandInterface $command, array $options);
	public function canBuild(\Guzzle\Service\Command\CommandInterface $command);
}
?>