<?php  namespace Guzzle\Service\Description;
interface ServiceDescriptionInterface extends \Serializable 
{
	public function getBaseUrl();
	public function getOperations();
	public function hasOperation($name);
	public function getOperation($name);
	public function getModel($id);
	public function getModels();
	public function hasModel($id);
	public function getApiVersion();
	public function getName();
	public function getDescription();
	public function getData($key);
	public function setData($key, $value);
}
?>