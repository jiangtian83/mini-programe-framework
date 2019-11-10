<?php  namespace Guzzle\Service\Resource;
class CompositeResourceIteratorFactory implements ResourceIteratorFactoryInterface 
{
	protected $factories = NULL;
	public function __construct(array $factories) 
	{
		$this->factories = $factories;
	}
	public function build(\Guzzle\Service\Command\CommandInterface $command, array $options = array( )) 
	{
		if( !($factory = $this->getFactory($command)) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Iterator was not found for " . $command->getName());
		}
		return $factory->build($command, $options);
	}
	public function canBuild(\Guzzle\Service\Command\CommandInterface $command) 
	{
		return $this->getFactory($command) !== false;
	}
	public function addFactory(ResourceIteratorFactoryInterface $factory) 
	{
		$this->factories[] = $factory;
		return $this;
	}
	protected function getFactory(\Guzzle\Service\Command\CommandInterface $command) 
	{
		foreach( $this->factories as $factory ) 
		{
			if( $factory->canBuild($command) ) 
			{
				return $factory;
			}
		}
		return false;
	}
}
?>