<?php  namespace OSS\Model;
class BucketListInfo 
{
	private $bucketList = array( );
	public function __construct(array $bucketList) 
	{
		$this->bucketList = $bucketList;
	}
	public function getBucketList() 
	{
		return $this->bucketList;
	}
}
?>