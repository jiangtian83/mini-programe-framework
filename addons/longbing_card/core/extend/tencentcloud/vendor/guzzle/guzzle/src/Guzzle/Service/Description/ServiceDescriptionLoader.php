<?php  namespace Guzzle\Service\Description;
class ServiceDescriptionLoader extends \Guzzle\Service\AbstractConfigLoader 
{
	protected function build($config, array $options) 
	{
		$operations = array( );
		if( !empty($config["operations"]) ) 
		{
			foreach( $config["operations"] as $name => $op ) 
			{
				$op["name"] = (isset($op["name"]) ? $op["name"] : $name);
				$name = $op["name"];
				if( !empty($op["extends"]) ) 
				{
					$this->resolveExtension($name, $op, $operations);
				}
				$op["parameters"] = (isset($op["parameters"]) ? $op["parameters"] : array( ));
				$operations[$name] = $op;
			}
		}
		return new ServiceDescription(array( "apiVersion" => (isset($config["apiVersion"]) ? $config["apiVersion"] : null), "baseUrl" => (isset($config["baseUrl"]) ? $config["baseUrl"] : null), "description" => (isset($config["description"]) ? $config["description"] : null), "operations" => $operations, "models" => (isset($config["models"]) ? $config["models"] : null) ) + $config);
	}
	protected function resolveExtension($name, array &$op, array &$operations) 
	{
		$resolved = array( );
		$original = (empty($op["parameters"]) ? false : $op["parameters"]);
		$hasClass = !empty($op["class"]);
		foreach( (array) $op["extends"] as $extendedCommand ) 
		{
			if( empty($operations[$extendedCommand]) ) 
			{
				throw new \Guzzle\Service\Exception\DescriptionBuilderException((string) $name . " extends missing operation " . $extendedCommand);
			}
			$toArray = $operations[$extendedCommand];
			$resolved = (empty($resolved) ? $toArray["parameters"] : array_merge($resolved, $toArray["parameters"]));
			$op = $op + $toArray;
			if( !$hasClass && isset($toArray["class"]) ) 
			{
				$op["class"] = $toArray["class"];
			}
		}
		$op["parameters"] = ($original ? array_merge($resolved, $original) : $resolved);
	}
}
?>