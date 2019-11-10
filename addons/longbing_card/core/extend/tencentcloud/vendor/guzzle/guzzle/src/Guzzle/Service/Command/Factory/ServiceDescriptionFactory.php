<?php  namespace Guzzle\Service\Command\Factory;
class ServiceDescriptionFactory implements FactoryInterface 
{
	protected $description = NULL;
	protected $inflector = NULL;
	public function __construct(\Guzzle\Service\Description\ServiceDescriptionInterface $description, \Guzzle\Inflection\InflectorInterface $inflector = NULL) 
	{
		$this->setServiceDescription($description);
		$this->inflector = $inflector;
	}
	public function setServiceDescription(\Guzzle\Service\Description\ServiceDescriptionInterface $description) 
	{
		$this->description = $description;
		return $this;
	}
	public function getServiceDescription() 
	{
		return $this->description;
	}
	public function factory($name, array $args = array( )) 
	{
		$command = $this->description->getOperation($name);
		if( !$command ) 
		{
			$command = $this->description->getOperation(ucfirst($name));
			if( !$command && $this->inflector ) 
			{
				$command = $this->description->getOperation($this->inflector->snake($name));
			}
		}
		if( $command ) 
		{
			$class = $command->getClass();
			return new $class($args, $command, $this->description);
		}
	}
}
?>