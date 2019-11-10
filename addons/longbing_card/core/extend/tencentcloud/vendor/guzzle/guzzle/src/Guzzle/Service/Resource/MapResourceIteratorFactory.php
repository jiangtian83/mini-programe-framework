<?php  namespace Guzzle\Service\Resource;
class MapResourceIteratorFactory extends AbstractResourceIteratorFactory 
{
	protected $map = NULL;
	public function __construct(array $map) 
	{
		$this->map = $map;
	}
	public function getClassName(\Guzzle\Service\Command\CommandInterface $command) 
	{
		$className = $command->getName();
		if( isset($this->map[$className]) ) 
		{
			return $this->map[$className];
		}
		if( isset($this->map["*"]) ) 
		{
			return $this->map["*"];
		}
		return null;
	}
}
?>