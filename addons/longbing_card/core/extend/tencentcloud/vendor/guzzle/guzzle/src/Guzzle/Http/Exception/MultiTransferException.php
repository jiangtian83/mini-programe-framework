<?php  namespace Guzzle\Http\Exception;
class MultiTransferException extends \Guzzle\Common\Exception\ExceptionCollection 
{
	protected $successfulRequests = array( );
	protected $failedRequests = array( );
	protected $exceptionForRequest = array( );
	public function getAllRequests() 
	{
		return array_merge($this->successfulRequests, $this->failedRequests);
	}
	public function addSuccessfulRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->successfulRequests[] = $request;
		return $this;
	}
	public function addFailedRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->failedRequests[] = $request;
		return $this;
	}
	public function addFailedRequestWithException(\Guzzle\Http\Message\RequestInterface $request, \Exception $exception) 
	{
		$this->add($exception)->addFailedRequest($request)->exceptionForRequest[spl_object_hash($request)] = $exception;
		return $this;
	}
	public function getExceptionForFailedRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$oid = spl_object_hash($request);
		return (isset($this->exceptionForRequest[$oid]) ? $this->exceptionForRequest[$oid] : null);
	}
	public function setSuccessfulRequests(array $requests) 
	{
		$this->successfulRequests = $requests;
		return $this;
	}
	public function setFailedRequests(array $requests) 
	{
		$this->failedRequests = $requests;
		return $this;
	}
	public function getSuccessfulRequests() 
	{
		return $this->successfulRequests;
	}
	public function getFailedRequests() 
	{
		return $this->failedRequests;
	}
	public function containsRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		return in_array($request, $this->failedRequests, true) || in_array($request, $this->successfulRequests, true);
	}
}
?>