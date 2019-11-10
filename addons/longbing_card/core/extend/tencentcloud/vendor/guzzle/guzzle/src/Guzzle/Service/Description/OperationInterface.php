<?php  namespace Guzzle\Service\Description;
interface OperationInterface extends \Guzzle\Common\ToArrayInterface 
{
	const TYPE_PRIMITIVE = "primitive";
	const TYPE_CLASS = "class";
	const TYPE_DOCUMENTATION = "documentation";
	const TYPE_MODEL = "model";
	public function getServiceDescription();
	public function setServiceDescription(ServiceDescriptionInterface $description);
	public function getParams();
	public function getParamNames();
	public function hasParam($name);
	public function getParam($param);
	public function getHttpMethod();
	public function getClass();
	public function getName();
	public function getSummary();
	public function getNotes();
	public function getDocumentationUrl();
	public function getResponseClass();
	public function getResponseType();
	public function getResponseNotes();
	public function getDeprecated();
	public function getUri();
	public function getErrorResponses();
	public function getData($name);
}
?>