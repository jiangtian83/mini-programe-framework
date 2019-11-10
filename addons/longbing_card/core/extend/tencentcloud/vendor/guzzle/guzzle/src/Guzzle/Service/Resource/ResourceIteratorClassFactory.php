<?php  namespace Guzzle\Service\Resource;
class ResourceIteratorClassFactory extends AbstractResourceIteratorFactory 
{
	protected $namespaces = NULL;
	protected $inflector = NULL;
	public function __construct($namespaces = array( ), \Guzzle\Inflection\InflectorInterface $inflector = NULL) 
	{
		$this->namespaces = (array) $namespaces;
		$this->inflector = ($inflector ?: \Guzzle\Inflection\Inflector::getDefault());
	}
	public function registerNamespace($namespace) 
	{
		array_unshift($this->namespaces, $namespace);
		return $this;
	}
	protected function getClassName(\Guzzle\Service\Command\CommandInterface $command) 
	{
		$iteratorName = $this->inflector->camel($command->getName()) . "Iterator";
		foreach( $this->namespaces as $namespace ) 
		{
			$potentialClassName = $namespace . "\\" . $iteratorName;
			if( class_exists($potentialClassName) ) 
			{
				return $potentialClassName;
			}
		}
		return false;
	}
}
?>