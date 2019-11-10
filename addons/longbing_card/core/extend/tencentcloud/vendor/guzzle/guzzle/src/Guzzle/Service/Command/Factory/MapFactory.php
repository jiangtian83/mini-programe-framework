<?php  namespace Guzzle\Service\Command\Factory;
class MapFactory implements FactoryInterface 
{
	protected $map = NULL;
	public function __construct(array $map) 
	{
		$this->map = $map;
	}
	public function factory($name, array $args = array( )) 
	{
		if( isset($this->map[$name]) ) 
		{
			$class = $this->map[$name];
			return new $class($args);
		}
	}
}
?>