<?php  namespace Qcloud\Cos;
class Client extends \Guzzle\Service\Client 
{
	private $region = NULL;
	private $credentials = NULL;
	private $appId = NULL;
	private $secretId = NULL;
	private $secretKey = NULL;
	private $timeout = NULL;
	private $connect_timeout = NULL;
	private $signature = NULL;
	const VERSION = "1.3.0";
	public function __construct($config) 
	{
		$this->region = $config["region"];
		$regionmap = array( "cn-east" => "ap-shanghai", "cn-sorth" => "ap-guangzhou", "cn-north" => "ap-beijing-1", "cn-south-2" => "ap-guangzhou-2", "cn-southwest" => "ap-chengdu", "sg" => "ap-singapore", "tj" => "ap-beijing-1", "bj" => "ap-beijing", "sh" => "ap-shanghai", "gz" => "ap-guangzhou", "cd" => "ap-chengdu", "sgp" => "ap-singapore" );
		$this->region = (isset($regionmap[$this->region]) ? $regionmap[$this->region] : $this->region);
		$this->credentials = $config["credentials"];
		$this->appId = (isset($config["credentials"]["appId"]) ? $config["credentials"]["appId"] : null);
		$this->secretId = $config["credentials"]["secretId"];
		$this->secretKey = $config["credentials"]["secretKey"];
		$this->token = (isset($config["credentials"]["token"]) ? $config["credentials"]["token"] : null);
		$this->timeout = (isset($config["timeout"]) ? $config["timeout"] : 3600);
		$this->connect_timeout = (isset($config["connect_timeout"]) ? $config["connect_timeout"] : 3600);
		$this->signature = new Signature($this->secretId, $this->secretKey);
		parent::__construct("http://cos." . $this->region . ".myqcloud.com/", array( "request.options" => array( "timeout" => $this->timeout, "connect_timeout" => $this->connect_timeout ) ));
		$desc = \Guzzle\Service\Description\ServiceDescription::factory(Service::getService());
		$this->setDescription($desc);
		$this->setUserAgent("cos-php-sdk-v5." . Client::VERSION, true);
		$this->addSubscriber(new ExceptionListener());
		$this->addSubscriber(new Md5Listener($this->signature));
		$this->addSubscriber(new TokenListener($this->token));
		$this->addSubscriber(new SignatureListener($this->secretId, $this->secretKey));
		$this->addSubscriber(new BucketStyleListener($this->appId));
		$this->addSubscriber(new UploadBodyListener(array( "PutObject", "UploadPart" )));
	}
	public function set_config($config) 
	{
		$this->region = $config["region"];
		$regionmap = array( "cn-east" => "ap-shanghai", "cn-sorth" => "ap-guangzhou", "cn-north" => "ap-beijing-1", "cn-south-2" => "ap-guangzhou-2", "cn-southwest" => "ap-chengdu", "sg" => "ap-singapore", "tj" => "ap-beijing-1", "bj" => "ap-beijing", "sh" => "ap-shanghai", "gz" => "ap-guangzhou", "cd" => "ap-chengdu", "sgp" => "ap-singapore" );
		$this->region = (isset($regionmap[$this->region]) ? $regionmap[$this->region] : $this->region);
		$this->credentials = $config["credentials"];
		$this->appId = (isset($config["credentials"]["appId"]) ? $config["credentials"]["appId"] : null);
		$this->secretId = $config["credentials"]["secretId"];
		$this->secretKey = $config["credentials"]["secretKey"];
		$this->token = (isset($config["credentials"]["token"]) ? $config["credentials"]["token"] : null);
		$this->timeout = (isset($config["timeout"]) ? $config["timeout"] : 3600);
		$this->connect_timeout = (isset($config["connect_timeout"]) ? $config["connect_timeout"] : 3600);
		$this->signature = new Signature($this->secretId, $this->secretKey);
		parent::__construct("http://cos." . $this->region . ".myqcloud.com/", array( "request.options" => array( "timeout" => $this->timeout, "connect_timeout" => $this->connect_timeout ) ));
	}
	public function __destruct() 
	{
	}
	public function __call($method, $args) 
	{
		return parent::__call(ucfirst($method), $args);
	}
	public function createAuthorization(\Guzzle\Http\Message\RequestInterface $request, $expires) 
	{
		if( $request->getClient() !== $this ) 
		{
			throw new InvalidArgumentException("The request object must be associated with the client. Use the " . "\$client->get(), \$client->head(), \$client->post(), \$client->put(), etc. methods when passing in a " . "request object");
		}
		return $this->signature->createAuthorization($request, $expires);
	}
	public function createPresignedUrl(\Guzzle\Http\Message\RequestInterface $request, $expires) 
	{
		if( $request->getClient() !== $this ) 
		{
			throw new InvalidArgumentException("The request object must be associated with the client. Use the " . "\$client->get(), \$client->head(), \$client->post(), \$client->put(), etc. methods when passing in a " . "request object");
		}
		return $this->signature->createPresignedUrl($request, $expires);
	}
	public function getObjectUrl($bucket, $key, $expires = NULL, array $args = array( )) 
	{
		$command = $this->getCommand("GetObject", $args + array( "Bucket" => $bucket, "Key" => $key ));
		if( $command->hasKey("Scheme") ) 
		{
			$scheme = $command["Scheme"];
			$request = $command->remove("Scheme")->prepare()->setScheme($scheme)->setPort(null);
		}
		else 
		{
			$request = $command->prepare();
		}
		return ($expires ? $this->createPresignedUrl($request, $expires) : $request->getUrl());
	}
	public function Upload($bucket, $key, $body, $options = array( )) 
	{
		$body = \Guzzle\Http\EntityBody::factory($body);
		$options = \Guzzle\Common\Collection::fromConfig(array_change_key_case($options), array( "min_part_size" => MultipartUpload::MIN_PART_SIZE, "params" => $options ));
		if( $body->getSize() < $options["min_part_size"] ) 
		{
			$rt = $this->putObject(array( "Bucket" => $bucket, "Key" => $key, "Body" => $body ) + $options["params"]);
			$rt["Location"] = $rt["ObjectURL"];
			unset($rt["ObjectURL"]);
		}
		else 
		{
			$multipartUpload = new MultipartUpload($this, $body, $options["min_part_size"], array( "Bucket" => $bucket, "Key" => $key, "Body" => $body ) + $options["params"]);
			$rt = $multipartUpload->performUploading();
		}
		return $rt;
	}
	public function resumeUpload($bucket, $key, $body, $uploadId, $options = array( )) 
	{
		$body = \Guzzle\Http\EntityBody::factory($body);
		$options = \Guzzle\Common\Collection::fromConfig(array_change_key_case($options), array( "min_part_size" => MultipartUpload::MIN_PART_SIZE, "params" => $options ));
		$multipartUpload = new MultipartUpload($this, $body, $options["min_part_size"], array( "Bucket" => $bucket, "Key" => $key, "Body" => $body, "UploadId" => $uploadId ) + $options["params"]);
		$rt = $multipartUpload->resumeUploading();
		return $rt;
	}
	public function Copy($bucket, $key, $copysource, $options = array( )) 
	{
		$options = \Guzzle\Common\Collection::fromConfig(array_change_key_case($options), array( "min_part_size" => Copy::MIN_PART_SIZE, "params" => $options ));
		$sourcelistdot = explode(".", $copysource);
		$sourcelistline = explode("-", $sourcelistdot[0]);
		$sourceappid = array_pop($sourcelistline);
		$sourcebucket = implode("-", $sourcelistline);
		$sourceregion = $sourcelistdot[2];
		$sourcekey = substr(strstr($copysource, "/"), 1);
		$sourceversion = "";
		$cosClient = new Client(array( "region" => $sourceregion, "credentials" => array( "appId" => $sourceappid, "secretId" => $this->secretId, "secretKey" => $this->secretKey ) ));
		if( !key_exists("VersionId", $options["params"]) ) 
		{
			$sourceversion = "";
		}
		else 
		{
			$sourceversion = $options["params"]["VersionId"];
		}
		$rt = $cosClient->headObject(array( "Bucket" => $sourcebucket, "Key" => $sourcekey, "VersionId" => $sourceversion ));
		$contentlength = $rt["ContentLength"];
		if( $contentlength < $options["min_part_size"] ) 
		{
			return $this->copyObject(array( "Bucket" => $bucket, "Key" => $key, "CopySource" => $copysource . "?versionId=" . $sourceversion ) + $options["params"]);
		}
		$copy = new Copy($this, $contentlength, $copysource . "?versionId=" . $sourceversion, $options["min_part_size"], array( "Bucket" => $bucket, "Key" => $key ) + $options["params"]);
		return $copy->copy();
	}
	public function doesBucketExist($bucket, $accept403 = true, array $options = array( )) 
	{
		try 
		{
			$this->HeadBucket(array( "Bucket" => $bucket ));
			return True;
		}
		catch( \Exception $e ) 
		{
			return False;
		}
	}
	public function doesObjectExist($bucket, $key, array $options = array( )) 
	{
		try 
		{
			$this->HeadObject(array( "Bucket" => $bucket, "Key" => $key ));
			return True;
		}
		catch( \Exception $e ) 
		{
			return False;
		}
	}
	public static function encodeKey($key) 
	{
		return $key;
	}
	public static function explodeKey($key) 
	{
		return $key;
	}
}
?>