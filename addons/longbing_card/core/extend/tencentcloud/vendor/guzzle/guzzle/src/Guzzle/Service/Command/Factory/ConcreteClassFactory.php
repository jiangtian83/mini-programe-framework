<?php  namespace Guzzle\Service\Command\Factory;
class ConcreteClassFactory implements FactoryInterface 
{
	protected $client = NULL;
	protected $inflector = NULL;
	public function __construct(\Guzzle\Service\ClientInterface $client, \Guzzle\Inflection\InflectorInterface $inflector = NULL) 
	{
		$this->client = $client;
		$this->inflector = ($inflector ?: \Guzzle\Inflection\Inflector::getDefault());
	}
	public function factory($name, array $args = array( )) 
	{
		$prefix = $this->client->getConfig("command.prefix");
		if( !$prefix ) 
		{
			$prefix = implode("\\", array_slice(explode("\\", get_class($this->client)), 0, -1)) . "\\Command\\";
			$this->client->getConfig()->set("command.prefix", $prefix);
		}
		$class = $prefix . str_replace(" ", "\\", ucwords(str_replace(".", " ", $this->inflector->camel($name))));
		if( class_exists($class) ) 
		{
			return new $class($args);
		}
	}
}
?>