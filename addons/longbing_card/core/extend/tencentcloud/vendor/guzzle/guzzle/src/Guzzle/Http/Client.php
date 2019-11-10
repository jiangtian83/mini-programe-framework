<?php  namespace Guzzle\Http;
class Client extends \Guzzle\Common\AbstractHasDispatcher implements ClientInterface 
{
	protected $defaultHeaders = NULL;
	protected $userAgent = NULL;
	private $config = NULL;
	private $baseUrl = NULL;
	private $curlMulti = NULL;
	private $uriTemplate = NULL;
	protected $requestFactory = NULL;
	const REQUEST_PARAMS = "request.params";
	const REQUEST_OPTIONS = "request.options";
	const CURL_OPTIONS = "curl.options";
	const SSL_CERT_AUTHORITY = "ssl.certificate_authority";
	const DISABLE_REDIRECTS = RedirectPlugin::DISABLE;
	const DEFAULT_SELECT_TIMEOUT = 1;
	const MAX_HANDLES = 3;
	public static function getAllEvents() 
	{
		return array( self::CREATE_REQUEST );
	}
	public function __construct($baseUrl = "", $config = NULL) 
	{
		if( !extension_loaded("curl") ) 
		{
			throw new \Guzzle\Common\Exception\RuntimeException("The PHP cURL extension must be installed to use Guzzle.");
		}
		$this->setConfig(($config ?: new \Guzzle\Common\Collection()));
		$this->initSsl();
		$this->setBaseUrl($baseUrl);
		$this->defaultHeaders = new \Guzzle\Common\Collection();
		$this->setRequestFactory(Message\RequestFactory::getInstance());
		$this->userAgent = $this->getDefaultUserAgent();
		if( !$this->config[self::DISABLE_REDIRECTS] ) 
		{
			$this->addSubscriber(new RedirectPlugin());
		}
	}
	final public function setConfig($config) 
	{
		if( $config instanceof \Guzzle\Common\Collection ) 
		{
			$this->config = $config;
		}
		else 
		{
			if( is_array($config) ) 
			{
				$this->config = new \Guzzle\Common\Collection($config);
			}
			else 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException("Config must be an array or Collection");
			}
		}
		return $this;
	}
	final public function getConfig($key = false) 
	{
		return ($key ? $this->config[$key] : $this->config);
	}
	public function setDefaultOption($keyOrPath, $value) 
	{
		$keyOrPath = self::REQUEST_OPTIONS . "/" . $keyOrPath;
		$this->config->setPath($keyOrPath, $value);
		return $this;
	}
	public function getDefaultOption($keyOrPath) 
	{
		$keyOrPath = self::REQUEST_OPTIONS . "/" . $keyOrPath;
		return $this->config->getPath($keyOrPath);
	}
	final public function setSslVerification($certificateAuthority = true, $verifyPeer = true, $verifyHost = 2) 
	{
		$opts = ($this->config[self::CURL_OPTIONS] ?: array( ));
		if( $certificateAuthority === true ) 
		{
			$opts[CURLOPT_CAINFO] = __DIR__ . "/Resources/cacert.pem";
			$opts[CURLOPT_SSL_VERIFYPEER] = true;
			$opts[CURLOPT_SSL_VERIFYHOST] = 2;
		}
		else 
		{
			if( $certificateAuthority === false ) 
			{
				unset($opts[CURLOPT_CAINFO]);
				$opts[CURLOPT_SSL_VERIFYPEER] = false;
				$opts[CURLOPT_SSL_VERIFYHOST] = 0;
			}
			else 
			{
				if( $verifyPeer !== true && $verifyPeer !== false && $verifyPeer !== 1 && $verifyPeer !== 0 ) 
				{
					throw new \Guzzle\Common\Exception\InvalidArgumentException("verifyPeer must be 1, 0 or boolean");
				}
				if( $verifyHost !== 0 && $verifyHost !== 1 && $verifyHost !== 2 ) 
				{
					throw new \Guzzle\Common\Exception\InvalidArgumentException("verifyHost must be 0, 1 or 2");
				}
				$opts[CURLOPT_SSL_VERIFYPEER] = $verifyPeer;
				$opts[CURLOPT_SSL_VERIFYHOST] = $verifyHost;
				if( is_file($certificateAuthority) ) 
				{
					unset($opts[CURLOPT_CAPATH]);
					$opts[CURLOPT_CAINFO] = $certificateAuthority;
				}
				else 
				{
					if( is_dir($certificateAuthority) ) 
					{
						unset($opts[CURLOPT_CAINFO]);
						$opts[CURLOPT_CAPATH] = $certificateAuthority;
					}
					else 
					{
						throw new \Guzzle\Common\Exception\RuntimeException("Invalid option passed to " . self::SSL_CERT_AUTHORITY . ": " . $certificateAuthority);
					}
				}
			}
		}
		$this->config->set(self::CURL_OPTIONS, $opts);
		return $this;
	}
	public function createRequest($method = "GET", $uri = NULL, $headers = NULL, $body = NULL, array $options = array( )) 
	{
		if( !$uri ) 
		{
			$url = $this->getBaseUrl();
		}
		else 
		{
			if( !is_array($uri) ) 
			{
				$templateVars = null;
			}
			else 
			{
				list($uri, $templateVars) = $uri;
			}
			if( strpos($uri, "://") ) 
			{
				$url = $this->expandTemplate($uri, $templateVars);
			}
			else 
			{
				$url = Url::factory($this->getBaseUrl())->combine($this->expandTemplate($uri, $templateVars));
			}
		}
		if( count($this->defaultHeaders) ) 
		{
			if( !$headers ) 
			{
				$headers = $this->defaultHeaders->toArray();
			}
			else 
			{
				if( is_array($headers) ) 
				{
					$headers += $this->defaultHeaders->toArray();
				}
				else 
				{
					if( $headers instanceof \Guzzle\Common\Collection ) 
					{
						$headers = $headers->toArray() + $this->defaultHeaders->toArray();
					}
				}
			}
		}
		return $this->prepareRequest($this->requestFactory->create($method, (string) $url, $headers, $body), $options);
	}
	public function getBaseUrl($expand = true) 
	{
		return ($expand ? $this->expandTemplate($this->baseUrl) : $this->baseUrl);
	}
	public function setBaseUrl($url) 
	{
		$this->baseUrl = $url;
		return $this;
	}
	public function setUserAgent($userAgent, $includeDefault = false) 
	{
		if( $includeDefault ) 
		{
			$userAgent .= " " . $this->getDefaultUserAgent();
		}
		$this->userAgent = $userAgent;
		return $this;
	}
	public function getDefaultUserAgent() 
	{
		return "Guzzle/" . \Guzzle\Common\Version::VERSION . " curl/" . Curl\CurlVersion::getInstance()->get("version") . " PHP/" . PHP_VERSION;
	}
	public function get($uri = NULL, $headers = NULL, $options = array( )) 
	{
		return (is_array($options) ? $this->createRequest("GET", $uri, $headers, null, $options) : $this->createRequest("GET", $uri, $headers, $options));
	}
	public function head($uri = NULL, $headers = NULL, array $options = array( )) 
	{
		return $this->createRequest("HEAD", $uri, $headers, null, $options);
	}
	public function delete($uri = NULL, $headers = NULL, $body = NULL, array $options = array( )) 
	{
		return $this->createRequest("DELETE", $uri, $headers, $body, $options);
	}
	public function put($uri = NULL, $headers = NULL, $body = NULL, array $options = array( )) 
	{
		return $this->createRequest("PUT", $uri, $headers, $body, $options);
	}
	public function patch($uri = NULL, $headers = NULL, $body = NULL, array $options = array( )) 
	{
		return $this->createRequest("PATCH", $uri, $headers, $body, $options);
	}
	public function post($uri = NULL, $headers = NULL, $postBody = NULL, array $options = array( )) 
	{
		return $this->createRequest("POST", $uri, $headers, $postBody, $options);
	}
	public function options($uri = NULL, array $options = array( )) 
	{
		return $this->createRequest("OPTIONS", $uri, $options);
	}
	public function send($requests) 
	{
		if( !$requests instanceof Message\RequestInterface ) 
		{
			return $this->sendMultiple($requests);
		}
		try 
		{
			$this->getCurlMulti()->add($requests)->send();
			return $requests->getResponse();
		}
		catch( \Guzzle\Common\Exception\ExceptionCollection $e ) 
		{
			throw $e->getFirst();
		}
	}
	public function setCurlMulti(Curl\CurlMultiInterface $curlMulti) 
	{
		$this->curlMulti = $curlMulti;
		return $this;
	}
	public function getCurlMulti() 
	{
		if( !$this->curlMulti ) 
		{
			$this->curlMulti = new Curl\CurlMultiProxy(self::MAX_HANDLES, ($this->getConfig("select_timeout") ?: self::DEFAULT_SELECT_TIMEOUT));
		}
		return $this->curlMulti;
	}
	public function setRequestFactory(Message\RequestFactoryInterface $factory) 
	{
		$this->requestFactory = $factory;
		return $this;
	}
	public function setUriTemplate(\Guzzle\Parser\UriTemplate\UriTemplateInterface $uriTemplate) 
	{
		$this->uriTemplate = $uriTemplate;
		return $this;
	}
	protected function expandTemplate($template, array $variables = NULL) 
	{
		$expansionVars = $this->getConfig()->toArray();
		if( $variables ) 
		{
			$expansionVars = $variables + $expansionVars;
		}
		return $this->getUriTemplate()->expand($template, $expansionVars);
	}
	protected function getUriTemplate() 
	{
		if( !$this->uriTemplate ) 
		{
			$this->uriTemplate = \Guzzle\Parser\ParserRegistry::getInstance()->getParser("uri_template");
		}
		return $this->uriTemplate;
	}
	protected function sendMultiple(array $requests) 
	{
		$curlMulti = $this->getCurlMulti();
		foreach( $requests as $request ) 
		{
			$curlMulti->add($request);
		}
		$curlMulti->send();
		$result = array( );
		foreach( $requests as $request ) 
		{
			$result[] = $request->getResponse();
		}
		return $result;
	}
	protected function prepareRequest(Message\RequestInterface $request, array $options = array( )) 
	{
		$request->setClient($this)->setEventDispatcher(clone $this->getEventDispatcher());
		if( $curl = $this->config[self::CURL_OPTIONS] ) 
		{
			$request->getCurlOptions()->overwriteWith(Curl\CurlHandle::parseCurlConfig($curl));
		}
		if( $params = $this->config[self::REQUEST_PARAMS] ) 
		{
			\Guzzle\Common\Version::warn("request.params is deprecated. Use request.options to add default request options.");
			$request->getParams()->overwriteWith($params);
		}
		if( $this->userAgent && !$request->hasHeader("User-Agent") ) 
		{
			$request->setHeader("User-Agent", $this->userAgent);
		}
		if( $defaults = $this->config[self::REQUEST_OPTIONS] ) 
		{
			$this->requestFactory->applyOptions($request, $defaults, Message\RequestFactoryInterface::OPTIONS_AS_DEFAULTS);
		}
		if( $options ) 
		{
			$this->requestFactory->applyOptions($request, $options);
		}
		$this->dispatch("client.create_request", array( "client" => $this, "request" => $request ));
		return $request;
	}
	protected function initSsl() 
	{
		$authority = $this->config[self::SSL_CERT_AUTHORITY];
		if( $authority === "system" ) 
		{
			return NULL;
		}
		if( $authority === null ) 
		{
			$authority = true;
		}
		if( $authority === true && substr(__FILE__, 0, 7) == "phar://" ) 
		{
			$authority = self::extractPharCacert(__DIR__ . "/Resources/cacert.pem");
		}
		$this->setSslVerification($authority);
	}
	public function getDefaultHeaders() 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Client::getDefaultHeaders" . " is deprecated. Use the request.options array to retrieve default request options");
		return $this->defaultHeaders;
	}
	public function setDefaultHeaders($headers) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Client::setDefaultHeaders" . " is deprecated. Use the request.options array to specify default request options");
		if( $headers instanceof \Guzzle\Common\Collection ) 
		{
			$this->defaultHeaders = $headers;
		}
		else 
		{
			if( is_array($headers) ) 
			{
				$this->defaultHeaders = new \Guzzle\Common\Collection($headers);
			}
			else 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException("Headers must be an array or Collection");
			}
		}
		return $this;
	}
	public function preparePharCacert($md5Check = true) 
	{
		return sys_get_temp_dir() . "/guzzle-cacert.pem";
	}
	public static function extractPharCacert($pharCacertPath) 
	{
		$certFile = sys_get_temp_dir() . "/guzzle-cacert.pem";
		if( !file_exists($pharCacertPath) ) 
		{
			throw new \RuntimeException("Could not find " . $pharCacertPath);
		}
		if( (!file_exists($certFile) || filesize($certFile) != filesize($pharCacertPath)) && !copy($pharCacertPath, $certFile) ) 
		{
			throw new \RuntimeException("Could not copy " . $pharCacertPath . " to " . $certFile . ": " . var_export(error_get_last(), true));
		}
		return $certFile;
	}
}
?>