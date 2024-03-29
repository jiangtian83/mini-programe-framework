<?php  namespace Guzzle\Plugin\Cache;
class DefaultCacheStorage implements CacheStorageInterface 
{
	protected $keyPrefix = NULL;
	protected $cache = NULL;
	protected $defaultTtl = NULL;
	public function __construct($cache, $keyPrefix = "", $defaultTtl = 3600) 
	{
		$this->cache = \Guzzle\Cache\CacheAdapterFactory::fromCache($cache);
		$this->defaultTtl = $defaultTtl;
		$this->keyPrefix = $keyPrefix;
	}
	public function cache(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		$currentTime = time();
		$overrideTtl = $request->getParams()->get("cache.override_ttl");
		if( $overrideTtl ) 
		{
			$ttl = $overrideTtl;
		}
		else 
		{
			$maxAge = $response->getMaxAge();
			if( $maxAge !== null ) 
			{
				$ttl = $maxAge;
			}
			else 
			{
				$ttl = $this->defaultTtl;
			}
		}
		if( $cacheControl = $response->getHeader("Cache-Control") ) 
		{
			$stale = $cacheControl->getDirective("stale-if-error");
			if( $stale === true ) 
			{
				$ttl += $ttl;
			}
			else 
			{
				if( is_numeric($stale) ) 
				{
					$ttl += $stale;
				}
			}
		}
		$key = $this->getCacheKey($request);
		$persistedRequest = $this->persistHeaders($request);
		$entries = array( );
		if( $manifest = $this->cache->fetch($key) ) 
		{
			$vary = $response->getVary();
			foreach( unserialize($manifest) as $entry ) 
			{
				if( $entry[4] < $currentTime ) 
				{
					continue;
				}
				$entry[1]["vary"] = (isset($entry[1]["vary"]) ? $entry[1]["vary"] : "");
				if( $vary != $entry[1]["vary"] || !$this->requestsMatch($vary, $entry[0], $persistedRequest) ) 
				{
					$entries[] = $entry;
				}
			}
		}
		$bodyDigest = null;
		if( $response->getBody() && 0 < $response->getBody()->getContentLength() ) 
		{
			$bodyDigest = $this->getBodyKey($request->getUrl(), $response->getBody());
			$this->cache->save($bodyDigest, (string) $response->getBody(), $ttl);
		}
		array_unshift($entries, array( $persistedRequest, $this->persistHeaders($response), $response->getStatusCode(), $bodyDigest, $currentTime + $ttl ));
		$this->cache->save($key, serialize($entries));
	}
	public function delete(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$key = $this->getCacheKey($request);
		if( $entries = $this->cache->fetch($key) ) 
		{
			foreach( unserialize($entries) as $entry ) 
			{
				if( $entry[3] ) 
				{
					$this->cache->delete($entry[3]);
				}
			}
			$this->cache->delete($key);
		}
	}
	public function purge($url) 
	{
		foreach( array( "GET", "HEAD", "POST", "PUT", "DELETE" ) as $method ) 
		{
			$this->delete(new \Guzzle\Http\Message\Request($method, $url));
		}
	}
	public function fetch(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$key = $this->getCacheKey($request);
		if( !($entries = $this->cache->fetch($key)) ) 
		{
			return null;
		}
		$match = null;
		$headers = $this->persistHeaders($request);
		$entries = unserialize($entries);
		foreach( $entries as $index => $entry ) 
		{
			if( $this->requestsMatch((isset($entry[1]["vary"]) ? $entry[1]["vary"] : ""), $headers, $entry[0]) ) 
			{
				$match = $entry;
				break;
			}
		}
		if( !$match ) 
		{
			return null;
		}
		$response = null;
		if( $match[4] < time() ) 
		{
			$response = -1;
		}
		else 
		{
			$response = new \Guzzle\Http\Message\Response($match[2], $match[1]);
			if( $match[3] ) 
			{
				if( $body = $this->cache->fetch($match[3]) ) 
				{
					$response->setBody($body);
				}
				else 
				{
					$response = -1;
				}
			}
		}
		if( $response === -1 ) 
		{
			unset($entries[$index]);
			if( $entries ) 
			{
				$this->cache->save($key, serialize($entries));
			}
			else 
			{
				$this->cache->delete($key);
			}
			return null;
		}
		return $response;
	}
	protected function getCacheKey(\Guzzle\Http\Message\RequestInterface $request) 
	{
		if( $filter = $request->getParams()->get("cache.key_filter") ) 
		{
			$url = $request->getUrl(true);
			foreach( explode(",", $filter) as $remove ) 
			{
				$url->getQuery()->remove(trim($remove));
			}
		}
		else 
		{
			$url = $request->getUrl();
		}
		return $this->keyPrefix . md5($request->getMethod() . " " . $url);
	}
	protected function getBodyKey($url, \Guzzle\Http\EntityBodyInterface $body) 
	{
		return $this->keyPrefix . md5($url) . $body->getContentMd5();
	}
	private function requestsMatch($vary, $r1, $r2) 
	{
		if( $vary ) 
		{
			foreach( explode(",", $vary) as $header ) 
			{
				$key = trim(strtolower($header));
				$v1 = (isset($r1[$key]) ? $r1[$key] : null);
				$v2 = (isset($r2[$key]) ? $r2[$key] : null);
				if( $v1 !== $v2 ) 
				{
					return false;
				}
			}
		}
		return true;
	}
	private function persistHeaders(\Guzzle\Http\Message\MessageInterface $message) 
	{
		static $noCache = array( "age" => true, "connection" => true, "keep-alive" => true, "proxy-authenticate" => true, "proxy-authorization" => true, "te" => true, "trailers" => true, "transfer-encoding" => true, "upgrade" => true, "set-cookie" => true, "set-cookie2" => true );
		$headers = $message->getHeaders()->getAll();
		$headers = array_diff_key($headers, $noCache);
		$headers = array_map(function($h) 
		{
			return (string) $h;
		}
		, $headers);
		return $headers;
	}
}
?>