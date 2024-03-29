<?php  namespace Guzzle\Http\Message\Header;
interface HeaderInterface extends \Guzzle\Common\ToArrayInterface, \Countable, \IteratorAggregate 
{
	public function __toString();
	public function add($value);
	public function getName();
	public function setName($name);
	public function setGlue($glue);
	public function getGlue();
	public function hasValue($searchValue);
	public function removeValue($searchValue);
	public function parseParams();
}
?>