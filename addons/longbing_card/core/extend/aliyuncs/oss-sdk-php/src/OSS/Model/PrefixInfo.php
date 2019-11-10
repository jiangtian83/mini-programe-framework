<?php  namespace OSS\Model;
class PrefixInfo 
{
	private $prefix = NULL;
	public function __construct($prefix) 
	{
		$this->prefix = $prefix;
	}
	public function getPrefix() 
	{
		return $this->prefix;
	}
}
?>