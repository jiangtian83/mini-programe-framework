<?php  namespace Guzzle\Service\Builder;
class ServiceBuilderLoader extends \Guzzle\Service\AbstractConfigLoader 
{
	protected function build($config, array $options) 
	{
		$class = (!empty($config["class"]) ? $config["class"] : "Guzzle\\Service\\Builder" . "\\ServiceBuilder");
		$services = (isset($config["services"]) ? $config["services"] : $config);
		foreach( $services as $name => &$service ) 
		{
			$service["params"] = (isset($service["params"]) ? $service["params"] : array( ));
			if( !empty($service["extends"]) ) 
			{
				if( !isset($services[$service["extends"]]) ) 
				{
					throw new \Guzzle\Service\Exception\ServiceNotFoundException((string) $name . " is trying to extend a non-existent service: " . $service["extends"]);
				}
				$extended =& $services[$service["extends"]];
				if( empty($service["class"]) ) 
				{
					$service["class"] = (isset($extended["class"]) ? $extended["class"] : "");
				}
				if( $extendsParams = (isset($extended["params"]) ? $extended["params"] : false) ) 
				{
					$service["params"] = $service["params"] + $extendsParams;
				}
			}
			if( !empty($options) ) 
			{
				$service["params"] = $options + $service["params"];
			}
			$service["class"] = (isset($service["class"]) ? $service["class"] : "");
		}
		return new $class($services);
	}
	protected function mergeData(array $a, array $b) 
	{
		$result = $b + $a;
		if( isset($a["services"]) && $b["services"] ) 
		{
			$result["services"] = $b["services"] + $a["services"];
			foreach( $result["services"] as $name => &$service ) 
			{
				if( isset($a["services"][$name]["extends"]) && isset($b["services"][$name]["extends"]) && $b["services"][$name]["extends"] == $name ) 
				{
					$service += $a["services"][$name];
					$service["extends"] = $a["services"][$name]["extends"];
					if( isset($a["services"][$name]["params"]) ) 
					{
						$service["params"] += $a["services"][$name]["params"];
					}
				}
			}
		}
		return $result;
	}
}
?>