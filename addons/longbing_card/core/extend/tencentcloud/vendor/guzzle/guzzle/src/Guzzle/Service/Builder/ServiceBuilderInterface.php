<?php  namespace Guzzle\Service\Builder;
interface ServiceBuilderInterface 
{
	public function get($name, $throwAway);
	public function set($key, $service);
}
?>