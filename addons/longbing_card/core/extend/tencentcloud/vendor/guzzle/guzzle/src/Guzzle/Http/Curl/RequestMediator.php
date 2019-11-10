<?php  namespace Guzzle\Http\Curl;
class RequestMediator 
{
	protected $request = NULL;
	protected $emitIo = NULL;
	public function __construct(\Guzzle\Http\Message\RequestInterface $request, $emitIo = false) 
	{
		$this->request = $request;
		$this->emitIo = $emitIo;
	}
	public function receiveResponseHeader($curl, $header) 
	{
		static $normalize = array( "\r", "\n" );
		$length = strlen($header);
		$header = str_replace($normalize, "", $header);
		if( strpos($header, "HTTP/") === 0 ) 
		{
			$startLine = explode(" ", $header, 3);
			$code = $startLine[1];
			$status = (isset($startLine[2]) ? $startLine[2] : "");
			if( 200 <= $code && $code < 300 ) 
			{
				$body = $this->request->getResponseBody();
			}
			else 
			{
				$body = \Guzzle\Http\EntityBody::factory();
			}
			$response = new \Guzzle\Http\Message\Response($code, null, $body);
			$response->setStatus($code, $status);
			$this->request->startResponse($response);
			$this->request->dispatch("request.receive.status_line", array( "request" => $this, "line" => $header, "status_code" => $code, "reason_phrase" => $status ));
		}
		else 
		{
			if( $pos = strpos($header, ":") ) 
			{
				$this->request->getResponse()->addHeader(trim(substr($header, 0, $pos)), trim(substr($header, $pos + 1)));
			}
		}
		return $length;
	}
	public function progress($downloadSize, $downloaded, $uploadSize, $uploaded, $handle = NULL) 
	{
		$this->request->dispatch("curl.callback.progress", array( "request" => $this->request, "handle" => $handle, "download_size" => $downloadSize, "downloaded" => $downloaded, "upload_size" => $uploadSize, "uploaded" => $uploaded ));
	}
	public function writeResponseBody($curl, $write) 
	{
		if( $this->emitIo ) 
		{
			$this->request->dispatch("curl.callback.write", array( "request" => $this->request, "write" => $write ));
		}
		if( $response = $this->request->getResponse() ) 
		{
			return $response->getBody()->write($write);
		}
		return 0;
	}
	public function readRequestBody($ch, $fd, $length) 
	{
		if( !($body = $this->request->getBody()) ) 
		{
			return "";
		}
		$read = (string) $body->read($length);
		if( $this->emitIo ) 
		{
			$this->request->dispatch("curl.callback.read", array( "request" => $this->request, "read" => $read ));
		}
		return $read;
	}
}
?>