<?php  namespace Guzzle\Service\Command;
interface CommandInterface extends \ArrayAccess, \Guzzle\Common\ToArrayInterface 
{
	public function getName();
	public function getOperation();
	public function execute();
	public function getClient();
	public function setClient(\Guzzle\Service\ClientInterface $client);
	public function getRequest();
	public function getResponse();
	public function getResult();
	public function setResult($result);
	public function isPrepared();
	public function isExecuted();
	public function prepare();
	public function getRequestHeaders();
	public function setOnComplete($callable);
}
?>