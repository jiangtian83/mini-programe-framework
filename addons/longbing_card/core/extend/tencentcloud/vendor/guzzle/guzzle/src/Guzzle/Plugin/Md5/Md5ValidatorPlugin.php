<?php  namespace Guzzle\Plugin\Md5;
class Md5ValidatorPlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $contentLengthCutoff = NULL;
	protected $contentEncoded = NULL;
	public function __construct($contentEncoded = true, $contentLengthCutoff = false) 
	{
		$this->contentLengthCutoff = $contentLengthCutoff;
		$this->contentEncoded = $contentEncoded;
	}
	public static function getSubscribedEvents() 
	{
		return array( "request.complete" => array( "onRequestComplete", 255 ) );
	}
	public function onRequestComplete(\Guzzle\Common\Event $event) 
	{
		$response = $event["response"];
		if( !($contentMd5 = $response->getContentMd5()) ) 
		{
			return NULL;
		}
		$contentEncoding = $response->getContentEncoding();
		if( $contentEncoding && !$this->contentEncoded ) 
		{
			return false;
		}
		if( $this->contentLengthCutoff ) 
		{
			$size = ($response->getContentLength() ?: $response->getBody()->getSize());
			if( !$size || $this->contentLengthCutoff < $size ) 
			{
				return NULL;
			}
		}
		if( !$contentEncoding ) 
		{
			$hash = $response->getBody()->getContentMd5();
		}
		else 
		{
			if( $contentEncoding == "gzip" ) 
			{
				$response->getBody()->compress("zlib.deflate");
				$hash = $response->getBody()->getContentMd5();
				$response->getBody()->uncompress();
			}
			else 
			{
				if( $contentEncoding == "compress" ) 
				{
					$response->getBody()->compress("bzip2.compress");
					$hash = $response->getBody()->getContentMd5();
					$response->getBody()->uncompress();
				}
				else 
				{
					return NULL;
				}
			}
		}
		if( $contentMd5 !== $hash ) 
		{
			throw new \Guzzle\Common\Exception\UnexpectedValueException("The response entity body may have been modified over the wire.  The Content-MD5 " . "received (" . $contentMd5 . ") did not match the calculated MD5 hash (" . $hash . ").");
		}
	}
}
?>