<?php  namespace Guzzle\Service\Resource;
interface ResourceIteratorInterface extends \Guzzle\Common\ToArrayInterface, \Guzzle\Common\HasDispatcherInterface, \Iterator, \Countable 
{
	public function getNextToken();
	public function setLimit($limit);
	public function setPageSize($pageSize);
	public function get($key);
	public function set($key, $value);
}
?>