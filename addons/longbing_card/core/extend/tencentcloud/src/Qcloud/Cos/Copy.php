<?php  namespace Qcloud\Cos;
class Copy 
{
	private $client = NULL;
	private $source = NULL;
	private $options = NULL;
	private $partSize = NULL;
	private $size = NULL;
	const MIN_PART_SIZE = 5242880;
	const MAX_PART_SIZE = 5368709120;
	const MAX_PARTS = 10000;
	public function __construct($client, $contentlength, $source, $minPartSize, $options = array( )) 
	{
		$this->client = $client;
		$this->source = $source;
		$this->options = $options;
		$this->size = $contentlength;
		$this->partSize = $this->calculatePartSize($minPartSize);
		$this->concurrency = (isset($options["concurrency"]) ? $options["concurrency"] : 10);
		$this->retry = (isset($options["retry"]) ? $options["retry"] : 5);
	}
	public function copy() 
	{
		$uploadId = $this->initiateMultipartUpload();
		$i = 0;
		while( $i < 5 ) 
		{
			$rt = $this->uploadParts($uploadId);
			if( $rt == 0 ) 
			{
				break;
			}
			sleep(1 << $i);
			$i += 1;
		}
		return $this->client->completeMultipartUpload(array( "Bucket" => $this->options["Bucket"], "Key" => $this->options["Key"], "UploadId" => $uploadId, "Parts" => $this->parts ));
	}
	public function uploadParts($uploadId) 
	{
		$commands = array( );
		$offset = 0;
		$partNumber = 1;
		$partSize = $this->partSize;
		$finishedNum = 0;
		$this->parts = array( );
		while( true ) 
		{
			if( $this->size <= $offset + $partSize ) 
			{
				$partSize = $this->size - $offset;
			}
			$params = array( "Bucket" => $this->options["Bucket"], "Key" => $this->options["Key"], "UploadId" => $uploadId, "PartNumber" => $partNumber, "CopySource" => $this->source, "CopySourceRange" => "bytes=" . (string) $offset . "-" . (string) (($offset + $partSize) - 1) );
			if( !isset($parts[$partNumber]) ) 
			{
				$commands[] = $this->client->getCommand("UploadPartCopy", $params);
			}
			if( $partNumber % $this->concurrency == 0 ) 
			{
				$this->client->execute($commands);
				$commands = array( );
			}
			$partNumber++;
			$offset += $partSize;
			if( $this->size == $offset ) 
			{
				break;
			}
		}
		if( !empty($commands) ) 
		{
			$this->client->execute($commands);
		}
		try 
		{
			$marker = 0;
			$finishedNum = 1;
			while( true ) 
			{
				$rt = $this->client->listParts(array( "Bucket" => $this->options["Bucket"], "Key" => $this->options["Key"], "PartNumberMarker" => $marker, "MaxParts" => 1000, "UploadId" => $uploadId ));
				if( !empty($rt["Parts"]) ) 
				{
					foreach( $rt["Parts"] as $part ) 
					{
						$part = array( "PartNumber" => $finishedNum, "ETag" => $part["ETag"] );
						$this->parts[$finishedNum] = $part;
						$finishedNum++;
					}
				}
				$marker = $rt["NextPartNumberMarker"];
				if( !$rt["IsTruncated"] ) 
				{
					break;
				}
			}
		}
		catch( \Exception $e ) 
		{
			echo $e;
		}
		if( $finishedNum == $partNumber ) 
		{
			return 0;
		}
		return -1;
	}
	private function calculatePartSize($minPartSize) 
	{
		$partSize = intval(ceil($this->size / self::MAX_PARTS));
		$partSize = max($minPartSize, $partSize);
		$partSize = min($partSize, self::MAX_PART_SIZE);
		$partSize = max($partSize, self::MIN_PART_SIZE);
		return $partSize;
	}
	private function initiateMultipartUpload() 
	{
		$result = $this->client->createMultipartUpload($this->options);
		return $result["UploadId"];
	}
}
function partUploadCopy($client, $params) 
{
	$rt = $client->uploadPartCopy($params);
	$rt["PartNumber"] = $params["PartNumber"];
	return $rt;
}
?>