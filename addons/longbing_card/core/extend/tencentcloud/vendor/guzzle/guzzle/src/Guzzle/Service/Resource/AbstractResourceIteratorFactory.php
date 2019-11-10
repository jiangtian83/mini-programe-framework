<?php  namespace Guzzle\Service\Resource;
abstract class AbstractResourceIteratorFactory implements ResourceIteratorFactoryInterface 
{
	public function build(\Guzzle\Service\Command\CommandInterface $command, array $options = array( )) 
	{
		if( !$this->canBuild($command) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Iterator was not found for " . $command->getName());
		}
		$className = $this->getClassName($command);
		return new $className($command, $options);
	}
	public function canBuild(\Guzzle\Service\Command\CommandInterface $command) 
	{
		return (bool) $this->getClassName($command);
	}
	abstract protected function getClassName(\Guzzle\Service\Command\CommandInterface $command);
}
?>