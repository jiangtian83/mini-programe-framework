<?php  namespace Guzzle\Service;
interface ClientInterface extends \Guzzle\Http\ClientInterface, \Guzzle\Common\FromConfigInterface 
{
	public function getCommand($name, array $args);
	public function execute($command);
	public function setDescription(Description\ServiceDescriptionInterface $service);
	public function getDescription();
	public function getIterator($command, array $commandOptions, array $iteratorOptions);
}
?>