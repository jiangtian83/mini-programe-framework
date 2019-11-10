<?php  namespace Guzzle\Http\Curl;
class CurlMulti extends \Guzzle\Common\AbstractHasDispatcher implements CurlMultiInterface 
{
	protected $multiHandle = NULL;
	protected $requests = NULL;
	protected $handles = NULL;
	protected $resourceHash = NULL;
	protected $exceptions = array( );
	protected $successful = array( );
	protected $multiErrors = NULL;
	protected $selectTimeout = NULL;
	public function __construct($selectTimeout = 1) 
	{
		$this->selectTimeout = $selectTimeout;
		$this->multiHandle = curl_multi_init();
		if( $this->multiHandle === false ) 
		{
			throw new \Guzzle\Http\Exception\CurlException("Unable to create multi handle");
		}
		$this->reset();
	}
	public function __destruct() 
	{
		if( is_resource($this->multiHandle) ) 
		{
			curl_multi_close($this->multiHandle);
		}
	}
	public function add(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->requests[] = $request;
		$this->beforeSend($request);
		$this->dispatch(self::ADD_REQUEST, array( "request" => $request ));
		return $this;
	}
	public function all() 
	{
		return $this->requests;
	}
	public function remove(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->removeHandle($request);
		if( ($index = array_search($request, $this->requests, true)) !== false ) 
		{
			$request = $this->requests[$index];
			unset($this->requests[$index]);
			$this->requests = array_values($this->requests);
			$this->dispatch(self::REMOVE_REQUEST, array( "request" => $request ));
			return true;
		}
		return false;
	}
	public function reset($hard = false) 
	{
		if( $this->requests ) 
		{
			foreach( $this->requests as $request ) 
			{
				$this->remove($request);
			}
		}
		$this->handles = new \SplObjectStorage();
		$this->requests = $this->resourceHash = $this->exceptions = $this->successful = array( );
	}
	public function send() 
	{
		$this->perform();
		$exceptions = $this->exceptions;
		$successful = $this->successful;
		$this->reset();
		if( $exceptions ) 
		{
			$this->throwMultiException($exceptions, $successful);
		}
	}
	public function count() 
	{
		return count($this->requests);
	}
	protected function throwMultiException(array $exceptions, array $successful) 
	{
		$multiException = new \Guzzle\Http\Exception\MultiTransferException("Errors during multi transfer");
		while( $e = array_shift($exceptions) ) 
		{
			$multiException->addFailedRequestWithException($e["request"], $e["exception"]);
		}
		foreach( $successful as $request ) 
		{
			if( !$multiException->containsRequest($request) ) 
			{
				$multiException->addSuccessfulRequest($request);
			}
		}
		throw $multiException;
	}
	protected function beforeSend(\Guzzle\Http\Message\RequestInterface $request) 
	{
		try 
		{
			$state = $request->setState(\Guzzle\Http\Message\RequestInterface::STATE_TRANSFER);
			if( $state == \Guzzle\Http\Message\RequestInterface::STATE_TRANSFER ) 
			{
				$this->addHandle($request);
			}
			else 
			{
				$this->remove($request);
				if( $state == \Guzzle\Http\Message\RequestInterface::STATE_COMPLETE ) 
				{
					$this->successful[] = $request;
				}
			}
		}
		catch( \Exception $e ) 
		{
			$this->removeErroredRequest($request, $e);
		}
	}
	private function addHandle(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$handle = $this->createCurlHandle($request)->getHandle();
		$this->checkCurlResult(curl_multi_add_handle($this->multiHandle, $handle));
	}
	protected function createCurlHandle(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$wrapper = CurlHandle::factory($request);
		$this->handles[$request] = $wrapper;
		$this->resourceHash[(int) $wrapper->getHandle()] = $request;
		return $wrapper;
	}
	protected function perform() 
	{
		$event = new \Guzzle\Common\Event(array( "curl_multi" => $this ));
		while( $this->requests ) 
		{
			$blocking = $total = 0;
			foreach( $this->requests as $request ) 
			{
				$total++;
				$event["request"] = $request;
				$request->getEventDispatcher()->dispatch(self::POLLING_REQUEST, $event);
				if( $request->getParams()->hasKey(self::BLOCKING) ) 
				{
					$blocking++;
				}
			}
			if( $blocking == $total ) 
			{
				usleep(500);
			}
			else 
			{
				$this->executeHandles();
			}
		}
	}
	private function executeHandles() 
	{
		$selectTimeout = 0.001;
		$active = false;
		do 
		{
			while( ($mrc = curl_multi_exec($this->multiHandle, $active)) == CURLM_CALL_MULTI_PERFORM ) 
			{
			}
			$this->checkCurlResult($mrc);
			$this->processMessages();
			if( $active && curl_multi_select($this->multiHandle, $selectTimeout) === -1 ) 
			{
				usleep(150);
			}
			$selectTimeout = $this->selectTimeout;
		}
		while( $active );
	}
	private function processMessages() 
	{
		while( $done = curl_multi_info_read($this->multiHandle) ) 
		{
			$request = $this->resourceHash[(int) $done["handle"]];
			try 
			{
				$this->processResponse($request, $this->handles[$request], $done);
				$this->successful[] = $request;
			}
			catch( \Exception $e ) 
			{
				$this->removeErroredRequest($request, $e);
			}
		}
	}
	protected function removeErroredRequest(\Guzzle\Http\Message\RequestInterface $request, \Exception $e = NULL) 
	{
		$this->exceptions[] = array( "request" => $request, "exception" => $e );
		$this->remove($request);
		$this->dispatch(self::MULTI_EXCEPTION, array( "exception" => $e, "all_exceptions" => $this->exceptions ));
	}
	protected function processResponse(\Guzzle\Http\Message\RequestInterface $request, CurlHandle $handle, array $curl) 
	{
		$handle->updateRequestFromTransfer($request);
		$curlException = $this->isCurlException($request, $handle, $curl);
		$this->removeHandle($request);
		if( !$curlException ) 
		{
			if( $this->validateResponseWasSet($request) ) 
			{
				$state = $request->setState(\Guzzle\Http\Message\RequestInterface::STATE_COMPLETE, array( "handle" => $handle ));
				if( $state != \Guzzle\Http\Message\RequestInterface::STATE_TRANSFER ) 
				{
					$this->remove($request);
				}
			}
		}
		else 
		{
			$state = $request->setState(\Guzzle\Http\Message\RequestInterface::STATE_ERROR, array( "exception" => $curlException ));
			if( $state != \Guzzle\Http\Message\RequestInterface::STATE_TRANSFER ) 
			{
				$this->remove($request);
			}
			if( $state == \Guzzle\Http\Message\RequestInterface::STATE_ERROR ) 
			{
				throw $curlException;
			}
		}
	}
	protected function removeHandle(\Guzzle\Http\Message\RequestInterface $request) 
	{
		if( isset($this->handles[$request]) ) 
		{
			$handle = $this->handles[$request];
			curl_multi_remove_handle($this->multiHandle, $handle->getHandle());
			unset($this->handles[$request]);
			unset($this->resourceHash[(int) $handle->getHandle()]);
			$handle->close();
		}
	}
	private function isCurlException(\Guzzle\Http\Message\RequestInterface $request, CurlHandle $handle, array $curl) 
	{
		if( CURLM_OK == $curl["result"] || CURLM_CALL_MULTI_PERFORM == $curl["result"] ) 
		{
			return false;
		}
		$handle->setErrorNo($curl["result"]);
		$e = new \Guzzle\Http\Exception\CurlException(sprintf("[curl] %s: %s [url] %s", $handle->getErrorNo(), $handle->getError(), $handle->getUrl()));
		$e->setCurlHandle($handle)->setRequest($request)->setCurlInfo($handle->getInfo())->setError($handle->getError(), $handle->getErrorNo());
		return $e;
	}
	private function checkCurlResult($code) 
	{
		if( $code != CURLM_OK && $code != CURLM_CALL_MULTI_PERFORM ) 
		{
			throw new \Guzzle\Http\Exception\CurlException((isset($this->multiErrors[$code]) ? "cURL error: " . $code . " (" . $this->multiErrors[$code][0] . "): cURL message: " . $this->multiErrors[$code][1] : "Unexpected cURL error: " . $code));
		}
	}
	private function validateResponseWasSet(\Guzzle\Http\Message\RequestInterface $request) 
	{
		if( $request->getResponse() ) 
		{
			return true;
		}
		$body = ($request instanceof \Guzzle\Http\Message\EntityEnclosingRequestInterface ? $request->getBody() : null);
		if( !$body ) 
		{
			$rex = new \Guzzle\Http\Exception\RequestException("No response was received for a request with no body. This" . " could mean that you are saturating your network.");
			$rex->setRequest($request);
			$this->removeErroredRequest($request, $rex);
		}
		else 
		{
			if( !$body->isSeekable() || !$body->seek(0) ) 
			{
				$rex = new \Guzzle\Http\Exception\RequestException("The connection was unexpectedly closed. The request would" . " have been retried, but attempting to rewind the" . " request body failed.");
				$rex->setRequest($request);
				$this->removeErroredRequest($request, $rex);
			}
			else 
			{
				$this->remove($request);
				$this->requests[] = $request;
				$this->addHandle($request);
			}
		}
		return false;
	}
}
?>