<?php  namespace Guzzle\Http\Message;
abstract class AbstractMessage implements MessageInterface 
{
	protected $headers = NULL;
	protected $headerFactory = NULL;
	protected $params = NULL;
	protected $protocol = "HTTP";
	protected $protocolVersion = "1.1";
	public function __construct() 
	{
		$this->params = new \Guzzle\Common\Collection();
		$this->headerFactory = new Header\HeaderFactory();
		$this->headers = new Header\HeaderCollection();
	}
	public function setHeaderFactory(Header\HeaderFactoryInterface $factory) 
	{
		$this->headerFactory = $factory;
		return $this;
	}
	public function getParams() 
	{
		return $this->params;
	}
	public function addHeader($header, $value) 
	{
		if( isset($this->headers[$header]) ) 
		{
			$this->headers[$header]->add($value);
		}
		else 
		{
			if( $value instanceof Header\HeaderInterface ) 
			{
				$this->headers[$header] = $value;
			}
			else 
			{
				$this->headers[$header] = $this->headerFactory->createHeader($header, $value);
			}
		}
		return $this;
	}
	public function addHeaders(array $headers) 
	{
		foreach( $headers as $key => $value ) 
		{
			$this->addHeader($key, $value);
		}
		return $this;
	}
	public function getHeader($header) 
	{
		return $this->headers[$header];
	}
	public function getHeaders() 
	{
		return $this->headers;
	}
	public function getHeaderLines() 
	{
		$headers = array( );
		foreach( $this->headers as $value ) 
		{
			$headers[] = $value->getName() . ": " . $value;
		}
		return $headers;
	}
	public function setHeader($header, $value) 
	{
		unset($this->headers[$header]);
		$this->addHeader($header, $value);
		return $this;
	}
	public function setHeaders(array $headers) 
	{
		$this->headers->clear();
		foreach( $headers as $key => $value ) 
		{
			$this->addHeader($key, $value);
		}
		return $this;
	}
	public function hasHeader($header) 
	{
		return isset($this->headers[$header]);
	}
	public function removeHeader($header) 
	{
		unset($this->headers[$header]);
		return $this;
	}
	public function getTokenizedHeader($header, $token = ";") 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\AbstractMessage::getTokenizedHeader" . " is deprecated. Use \$message->getHeader()->parseParams()");
		if( $this->hasHeader($header) ) 
		{
			$data = new \Guzzle\Common\Collection();
			foreach( $this->getHeader($header)->parseParams() as $values ) 
			{
				foreach( $values as $key => $value ) 
				{
					if( $value === "" ) 
					{
						$data->set($data->count(), $key);
					}
					else 
					{
						$data->add($key, $value);
					}
				}
			}
			return $data;
		}
	}
	public function setTokenizedHeader($header, $data, $token = ";") 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\AbstractMessage::setTokenizedHeader" . " is deprecated.");
		return $this;
	}
	public function getCacheControlDirective($directive) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\AbstractMessage::getCacheControlDirective" . " is deprecated. Use \$message->getHeader('Cache-Control')->getDirective()");
		if( !($header = $this->getHeader("Cache-Control")) ) 
		{
			return null;
		}
		return $header->getDirective($directive);
	}
	public function hasCacheControlDirective($directive) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\AbstractMessage::hasCacheControlDirective" . " is deprecated. Use \$message->getHeader('Cache-Control')->hasDirective()");
		if( $header = $this->getHeader("Cache-Control") ) 
		{
			return $header->hasDirective($directive);
		}
		return false;
	}
	public function addCacheControlDirective($directive, $value = true) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\AbstractMessage::addCacheControlDirective" . " is deprecated. Use \$message->getHeader('Cache-Control')->addDirective()");
		if( !($header = $this->getHeader("Cache-Control")) ) 
		{
			$this->addHeader("Cache-Control", "");
			$header = $this->getHeader("Cache-Control");
		}
		$header->addDirective($directive, $value);
		return $this;
	}
	public function removeCacheControlDirective($directive) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\AbstractMessage::removeCacheControlDirective" . " is deprecated. Use \$message->getHeader('Cache-Control')->removeDirective()");
		if( $header = $this->getHeader("Cache-Control") ) 
		{
			$header->removeDirective($directive);
		}
		return $this;
	}
}
?>