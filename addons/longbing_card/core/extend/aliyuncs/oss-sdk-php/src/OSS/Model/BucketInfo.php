<?php  namespace OSS\Model;
class BucketInfo 
{
	private $location = NULL;
	private $name = NULL;
	private $createDate = NULL;
	public function __construct($location, $name, $createDate) 
	{
		$this->location = $location;
		$this->name = $name;
		$this->createDate = $createDate;
	}
	public function getLocation() 
	{
		return $this->location;
	}
	public function getName() 
	{
		return $this->name;
	}
	public function getCreateDate() 
	{
		return $this->createDate;
	}
}
?>