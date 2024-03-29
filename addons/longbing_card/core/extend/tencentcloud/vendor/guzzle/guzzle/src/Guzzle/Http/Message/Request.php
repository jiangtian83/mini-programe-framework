<?php  namespace Guzzle\Http\Message;
class Request extends AbstractMessage implements RequestInterface 
{
	protected $eventDispatcher = NULL;
	protected $url = NULL;
	protected $method = NULL;
	protected $client = NULL;
	protected $response = NULL;
	protected $responseBody = NULL;
	protected $state = NULL;
	protected $username = NULL;
	protected $password = NULL;
	protected $curlOptions = NULL;
	protected $isRedirect = false;
	public static function getAllEvents() 
	{
		return array( "curl.callback.read", "curl.callback.write", "curl.callback.progress", "request.clone", "request.before_send", "request.sent", "request.complete", "request.success", "request.error", "request.exception", "request.receive.status_line" );
	}
	public function __construct($method, $url, $headers = array( )) 
	{
		parent::__construct();
		$this->method = strtoupper($method);
		$this->curlOptions = new \Guzzle\Common\Collection();
		$this->setUrl($url);
		if( $headers ) 
		{
			foreach( $headers as $key => $value ) 
			{
				if( $key == "host" || $key == "Host" ) 
				{
					$this->setHeader($key, $value);
				}
				else 
				{
					if( $value instanceof Header\HeaderInterface ) 
					{
						$this->addHeader($key, $value);
					}
					else 
					{
						foreach( (array) $value as $v ) 
						{
							$this->addHeader($key, $v);
						}
					}
				}
			}
		}
		$this->setState(self::STATE_NEW);
	}
	public function __clone() 
	{
		if( $this->eventDispatcher ) 
		{
			$this->eventDispatcher = clone $this->eventDispatcher;
		}
		$this->curlOptions = clone $this->curlOptions;
		$this->params = clone $this->params;
		$this->url = clone $this->url;
		$this->response = $this->responseBody = null;
		$this->headers = clone $this->headers;
		$this->setState(RequestInterface::STATE_NEW);
		$this->dispatch("request.clone", array( "request" => $this ));
	}
	public function __toString() 
	{
		return $this->getRawHeaders() . "\r\n\r\n";
	}
	public static function onRequestError(\Guzzle\Common\Event $event) 
	{
		$e = \Guzzle\Http\Exception\BadResponseException::factory($event["request"], $event["response"]);
		$event["request"]->setState(self::STATE_ERROR, array( "exception" => $e ) + $event->toArray());
		throw $e;
	}
	public function setClient(\Guzzle\Http\ClientInterface $client) 
	{
		$this->client = $client;
		return $this;
	}
	public function getClient() 
	{
		return $this->client;
	}
	public function getRawHeaders() 
	{
		$protocolVersion = ($this->protocolVersion ?: "1.1");
		return trim($this->method . " " . $this->getResource()) . " " . strtoupper(str_replace("https", "http", $this->url->getScheme())) . "/" . $protocolVersion . "\r\n" . implode("\r\n", $this->getHeaderLines());
	}
	public function setUrl($url) 
	{
		if( $url instanceof \Guzzle\Http\Url ) 
		{
			$this->url = $url;
		}
		else 
		{
			$this->url = \Guzzle\Http\Url::factory($url);
		}
		$this->setPort($this->url->getPort());
		if( $this->url->getUsername() || $this->url->getPassword() ) 
		{
			$this->setAuth($this->url->getUsername(), $this->url->getPassword());
			$this->url->setUsername(null);
			$this->url->setPassword(null);
		}
		return $this;
	}
	public function send() 
	{
		if( !$this->client ) 
		{
			throw new \Guzzle\Common\Exception\RuntimeException("A client must be set on the request");
		}
		return $this->client->send($this);
	}
	public function getResponse() 
	{
		return $this->response;
	}
	public function getQuery($asString = false) 
	{
		return ($asString ? (string) $this->url->getQuery() : $this->url->getQuery());
	}
	public function getMethod() 
	{
		return $this->method;
	}
	public function getScheme() 
	{
		return $this->url->getScheme();
	}
	public function setScheme($scheme) 
	{
		$this->url->setScheme($scheme);
		return $this;
	}
	public function getHost() 
	{
		return $this->url->getHost();
	}
	public function setHost($host) 
	{
		$this->url->setHost($host);
		$this->setPort($this->url->getPort());
		return $this;
	}
	public function getProtocolVersion() 
	{
		return $this->protocolVersion;
	}
	public function setProtocolVersion($protocol) 
	{
		$this->protocolVersion = $protocol;
		return $this;
	}
	public function getPath() 
	{
		return "/" . ltrim($this->url->getPath(), "/");
	}
	public function setPath($path) 
	{
		$this->url->setPath($path);
		return $this;
	}
	public function getPort() 
	{
		return $this->url->getPort();
	}
	public function setPort($port) 
	{
		$this->url->setPort($port);
		$scheme = $this->url->getScheme();
		if( $port && ($scheme == "http" && $port != 80 || $scheme == "https" && $port != 443) ) 
		{
			$this->headers["host"] = $this->headerFactory->createHeader("Host", $this->url->getHost() . ":" . $port);
		}
		else 
		{
			$this->headers["host"] = $this->headerFactory->createHeader("Host", $this->url->getHost());
		}
		return $this;
	}
	public function getUsername() 
	{
		return $this->username;
	}
	public function getPassword() 
	{
		return $this->password;
	}
	public function setAuth($user, $password = "", $scheme = CURLAUTH_BASIC) 
	{
		static $authMap = NULL;
		if( !$user ) 
		{
			$this->password = $this->username = null;
			$this->removeHeader("Authorization");
			$this->getCurlOptions()->remove(CURLOPT_HTTPAUTH);
			return $this;
		}
		if( !is_numeric($scheme) ) 
		{
			$scheme = strtolower($scheme);
			if( !isset($authMap[$scheme]) ) 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException($scheme . " is not a valid authentication type");
			}
			$scheme = $authMap[$scheme];
		}
		$this->username = $user;
		$this->password = $password;
		if( $scheme == CURLAUTH_BASIC ) 
		{
			$this->getCurlOptions()->remove(CURLOPT_HTTPAUTH);
			$this->setHeader("Authorization", "Basic " . base64_encode($this->username . ":" . $this->password));
		}
		else 
		{
			$this->getCurlOptions()->set(CURLOPT_HTTPAUTH, $scheme)->set(CURLOPT_USERPWD, $this->username . ":" . $this->password);
		}
		return $this;
	}
	public function getResource() 
	{
		$resource = $this->getPath();
		if( $query = (string) $this->url->getQuery() ) 
		{
			$resource .= "?" . $query;
		}
		return $resource;
	}
	public function getUrl($asObject = false) 
	{
		return ($asObject ? clone $this->url : (string) $this->url);
	}
	public function getState() 
	{
		return $this->state;
	}
	public function setState($state, array $context = array( )) 
	{
		$oldState = $this->state;
		$this->state = $state;
		switch( $state ) 
		{
			case self::STATE_NEW: $this->response = null;
			break;
			case self::STATE_TRANSFER: if( $oldState !== $state ) 
			{
				if( $this->hasHeader("Transfer-Encoding") && $this->hasHeader("Content-Length") ) 
				{
					$this->removeHeader("Transfer-Encoding");
				}
				$this->dispatch("request.before_send", array( "request" => $this ));
			}
			break;
			case self::STATE_COMPLETE: if( $oldState !== $state ) 
			{
				$this->processResponse($context);
				$this->responseBody = null;
			}
			break;
			case self::STATE_ERROR: if( isset($context["exception"]) ) 
			{
				$this->dispatch("request.exception", array( "request" => $this, "response" => (isset($context["response"]) ? $context["response"] : $this->response), "exception" => (isset($context["exception"]) ? $context["exception"] : null) ));
			}
		}
		return $this->state;
	}
	public function getCurlOptions() 
	{
		return $this->curlOptions;
	}
	public function startResponse(Response $response) 
	{
		$this->state = self::STATE_TRANSFER;
		$response->setEffectiveUrl((string) $this->getUrl());
		$this->response = $response;
		return $this;
	}
	public function setResponse(Response $response, $queued = false) 
	{
		$response->setEffectiveUrl((string) $this->url);
		if( $queued ) 
		{
			$ed = $this->getEventDispatcher();
			$ed->addListener("request.before_send", $f = function($e) use ($response, &$f, $ed) 
			{
				$e["request"]->setResponse($response);
				$ed->removeListener("request.before_send", $f);
			}
			, -9999);
		}
		else 
		{
			$this->response = $response;
			if( $this->responseBody && !$this->responseBody->getCustomData("default") && !$response->isRedirect() ) 
			{
				$this->getResponseBody()->write((string) $this->response->getBody());
			}
			else 
			{
				$this->responseBody = $this->response->getBody();
			}
			$this->setState(self::STATE_COMPLETE);
		}
		return $this;
	}
	public function setResponseBody($body) 
	{
		if( is_string($body) && !($body = fopen($body, "w+")) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Could not open " . $body . " for writing");
		}
		$this->responseBody = \Guzzle\Http\EntityBody::factory($body);
		return $this;
	}
	public function getResponseBody() 
	{
		if( $this->responseBody === null ) 
		{
			$this->responseBody = \Guzzle\Http\EntityBody::factory()->setCustomData("default", true);
		}
		return $this->responseBody;
	}
	public function isResponseBodyRepeatable() 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\Request::isResponseBodyRepeatable" . " is deprecated. Use \$request->getResponseBody()->isRepeatable()");
		return (!$this->responseBody ? true : $this->responseBody->isRepeatable());
	}
	public function getCookies() 
	{
		if( $cookie = $this->getHeader("Cookie") ) 
		{
			$data = \Guzzle\Parser\ParserRegistry::getInstance()->getParser("cookie")->parseCookie($cookie);
			return $data["cookies"];
		}
		return array( );
	}
	public function getCookie($name) 
	{
		$cookies = $this->getCookies();
		return (isset($cookies[$name]) ? $cookies[$name] : null);
	}
	public function addCookie($name, $value) 
	{
		if( !$this->hasHeader("Cookie") ) 
		{
			$this->setHeader("Cookie", (string) $name . "=" . $value);
		}
		else 
		{
			$this->getHeader("Cookie")->add((string) $name . "=" . $value);
		}
		$this->getHeader("Cookie")->setGlue(";");
		return $this;
	}
	public function removeCookie($name) 
	{
		if( $cookie = $this->getHeader("Cookie") ) 
		{
			foreach( $cookie as $cookieValue ) 
			{
				if( strpos($cookieValue, $name . "=") === 0 ) 
				{
					$cookie->removeValue($cookieValue);
				}
			}
		}
		return $this;
	}
	public function setEventDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher) 
	{
		$this->eventDispatcher = $eventDispatcher;
		$this->eventDispatcher->addListener("request.error", array( "Guzzle\\Http\\Message\\Request", "onRequestError" ), -255);
		return $this;
	}
	public function getEventDispatcher() 
	{
		if( !$this->eventDispatcher ) 
		{
			$this->setEventDispatcher(new \Symfony\Component\EventDispatcher\EventDispatcher());
		}
		return $this->eventDispatcher;
	}
	public function dispatch($eventName, array $context = array( )) 
	{
		$context["request"] = $this;
		return $this->getEventDispatcher()->dispatch($eventName, new \Guzzle\Common\Event($context));
	}
	public function addSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber) 
	{
		$this->getEventDispatcher()->addSubscriber($subscriber);
		return $this;
	}
	protected function getEventArray() 
	{
		return array( "request" => $this, "response" => $this->response );
	}
	protected function processResponse(array $context = array( )) 
	{
		if( !$this->response ) 
		{
			$e = new \Guzzle\Http\Exception\RequestException("Error completing request");
			$e->setRequest($this);
			throw $e;
		}
		$this->state = self::STATE_COMPLETE;
		$this->dispatch("request.sent", $this->getEventArray() + $context);
		if( $this->state == RequestInterface::STATE_COMPLETE ) 
		{
			$this->dispatch("request.complete", $this->getEventArray());
			if( $this->response->isError() ) 
			{
				$event = new \Guzzle\Common\Event($this->getEventArray());
				$this->getEventDispatcher()->dispatch("request.error", $event);
				if( $event["response"] !== $this->response ) 
				{
					$this->response = $event["response"];
				}
			}
			if( $this->response->isSuccessful() ) 
			{
				$this->dispatch("request.success", $this->getEventArray());
			}
		}
	}
	public function canCache() 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\Request::canCache" . " is deprecated. Use Guzzle\\Plugin\\Cache\\DefaultCanCacheStrategy.");
		if( class_exists("Guzzle\\Plugin\\Cache\\DefaultCanCacheStrategy") ) 
		{
			$canCache = new \Guzzle\Plugin\Cache\DefaultCanCacheStrategy();
			return $canCache->canCacheRequest($this);
		}
		return false;
	}
	public function setIsRedirect($isRedirect) 
	{
		$this->isRedirect = $isRedirect;
		return $this;
	}
	public function isRedirect() 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\Request::isRedirect" . " is deprecated. Use the HistoryPlugin to track this.");
		return $this->isRedirect;
	}
}
?>