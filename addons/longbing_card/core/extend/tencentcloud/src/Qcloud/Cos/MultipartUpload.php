<?php  namespace Qcloud\Cos;
class MultipartUpload 
{
	private $client = NULL;
	private $source = NULL;
	private $options = NULL;
	private $partSize = NULL;
	const MIN_PART_SIZE = 5242880;
	const MAX_PART_SIZE = 5368709120;
	const MAX_PARTS = 10000;
	public function __construct($client, $source, $minPartSize, $options = array( )) 
	{
		$this->client = $client;
		$this->source = $source;
		$this->options = $options;
		$this->partSize = $this->calculatePartSize($minPartSize);
	}
	public function performUploading() 
	{
		$uploadId = $this->initiateMultipartUpload();
		$partNumber = 1;
		for( $parts = array( );
		true;
		$partNumber++ ) 
		{
			if( $this->source->isConsumed() ) 
			{
				break;
			}
			$body = new \Guzzle\Http\ReadLimitEntityBody($this->source, $this->partSize, $this->source->ftell());
			if( $body->getContentLength() == 0 ) 
			{
				break;
			}
			$result = $this->client->uploadPart(array( "Bucket" => $this->options["Bucket"], "Key" => $this->options["Key"], "Body" => $body, "UploadId" => $uploadId, "PartNumber" => $partNumber ));
			if( md5($body) != substr($result["ETag"], 1, -1) ) 
			{
				throw new Exception\CosException("ETag check inconsistency");
			}
			$part = array( "PartNumber" => $partNumber, "ETag" => $result["ETag"] );
			array_push($parts, $part);
		}
		try 
		{
			$rt = $this->client->completeMultipartUpload(array( "Bucket" => $this->options["Bucket"], "Key" => $this->options["Key"], "UploadId" => $uploadId, "Parts" => $parts ));
		}
		catch( \Exception $e ) 
		{
			throw $e;
		}
		return $rt;
	}
	public function resumeUploading() 
	{
		$uploadId = $this->options["UploadId"];
		$rt = $this->client->ListParts(array( "UploadId" => $uploadId, "Bucket" => $this->options["Bucket"], "Key" => $this->options["Key"] ));
		$parts = array( );
		$offset = $this->partSize;
		if( 0 < count($rt["Parts"]) ) 
		{
			foreach( $rt["Parts"] as $part ) 
			{
				$parts[$part["PartNumber"] - 1] = array( "PartNumber" => $part["PartNumber"], "ETag" => $part["ETag"] );
			}
		}
		$partNumber = 1;
		while( true ) 
		{
			if( $this->source->isConsumed() ) 
			{
				break;
			}
			$body = new \Guzzle\Http\ReadLimitEntityBody($this->source, $this->partSize, $this->source->ftell());
			if( $body->getContentLength() == 0 ) 
			{
				break;
			}
			if( array_key_exists($partNumber - 1, $parts) ) 
			{
				if( md5($body) != substr($parts[$partNumber - 1]["ETag"], 1, -1) ) 
				{
					throw new Exception\CosException("ETag check inconsistency");
				}
				$body->setOffset($offset);
				continue;
			}
			$result = $this->client->uploadPart(array( "Bucket" => $this->options["Bucket"], "Key" => $this->options["Key"], "Body" => $body, "UploadId" => $uploadId, "PartNumber" => $partNumber ));
			if( md5($body) != substr($result["ETag"], 1, -1) ) 
			{
				throw new Exception\CosException("ETag check inconsistency");
			}
			$parts[$partNumber - 1] = array( "PartNumber" => $partNumber, "ETag" => $result["ETag"] );
			$partNumber++;
			$offset += $body->getContentLength();
		}
		$rt = $this->client->completeMultipartUpload(array( "Bucket" => $this->options["Bucket"], "Key" => $this->options["Key"], "UploadId" => $uploadId, "Parts" => $parts ));
		return $rt;
	}
	private function calculatePartSize($minPartSize) 
	{
		$partSize = intval(ceil($this->source->getContentLength() / self::MAX_PARTS));
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
?>