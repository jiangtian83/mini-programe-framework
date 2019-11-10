<?php  namespace think\exception;
class Handle 
{
	protected $render = NULL;
	protected $ignoreReport = array( "\\think\\exception\\HttpException" );
	public function setRender($render) 
	{
		$this->render = $render;
	}
	public function report(\Exception $exception) 
	{
		if( !$this->isIgnoreReport($exception) ) 
		{
			if( \think\App::$debug ) 
			{
				$data = array( "file" => $exception->getFile(), "line" => $exception->getLine(), "message" => $this->getMessage($exception), "code" => $this->getCode($exception) );
				$log = "[" . $data["code"] . "]" . $data["message"] . "[" . $data["file"] . ":" . $data["line"] . "]";
			}
			else 
			{
				$data = array( "code" => $this->getCode($exception), "message" => $this->getMessage($exception) );
				$log = "[" . $data["code"] . "]" . $data["message"];
			}
			if( \think\Config::get("record_trace") ) 
			{
				$log .= "\r\n" . $exception->getTraceAsString();
			}
			\think\Log::record($log, "error");
		}
	}
	protected function isIgnoreReport(\Exception $exception) 
	{
		foreach( $this->ignoreReport as $class ) 
		{
			if( $exception instanceof $class ) 
			{
				return true;
			}
		}
		return false;
	}
	public function render(\Exception $e) 
	{
		if( $this->render && $this->render instanceof \Closure ) 
		{
			$result = call_user_func_array($this->render, array( $e ));
			if( $result ) 
			{
				return $result;
			}
		}
		if( $e instanceof HttpException ) 
		{
			return $this->renderHttpException($e);
		}
		return $this->convertExceptionToResponse($e);
	}
	public function renderForConsole(\think\console\Output $output, \Exception $e) 
	{
		if( \think\App::$debug ) 
		{
			$output->setVerbosity(\think\console\Output::VERBOSITY_DEBUG);
		}
		$output->renderException($e);
	}
	protected function renderHttpException(HttpException $e) 
	{
		$status = $e->getStatusCode();
		$template = \think\Config::get("http_exception_template");
		if( !\think\App::$debug && !empty($template[$status]) ) 
		{
			return \think\Response::create($template[$status], "view", $status)->assign(array( "e" => $e ));
		}
		return $this->convertExceptionToResponse($e);
	}
	protected function convertExceptionToResponse(\Exception $exception) 
	{
		if( \think\App::$debug ) 
		{
			$data = array( "name" => get_class($exception), "file" => $exception->getFile(), "line" => $exception->getLine(), "message" => $this->getMessage($exception), "trace" => $exception->getTrace(), "code" => $this->getCode($exception), "source" => $this->getSourceCode($exception), "datas" => $this->getExtendData($exception), "tables" => array( "GET Data" => $_GET, "POST Data" => $_POST, "Files" => $_FILES, "Cookies" => $_COOKIE, "Session" => (isset($_SESSION) ? $_SESSION : array( )), "Server/Request Data" => $_SERVER, "Environment Variables" => $_ENV, "ThinkPHP Constants" => $this->getConst() ) );
		}
		else 
		{
			$data = array( "code" => $this->getCode($exception), "message" => $this->getMessage($exception) );
			if( !\think\Config::get("show_error_msg") ) 
			{
				$data["message"] = \think\Config::get("error_message");
			}
		}
		while( 1 < ob_get_level() ) 
		{
			ob_end_clean();
		}
		$data["echo"] = ob_get_clean();
		ob_start();
		extract($data);
		include(\think\Config::get("exception_tmpl"));
		$content = ob_get_clean();
		$response = new \think\Response($content, "html");
		if( $exception instanceof HttpException ) 
		{
			$statusCode = $exception->getStatusCode();
			$response->header($exception->getHeaders());
		}
		if( !isset($statusCode) ) 
		{
			$statusCode = 500;
		}
		$response->code($statusCode);
		return $response;
	}
	protected function getCode(\Exception $exception) 
	{
		$code = $exception->getCode();
		if( !$code && $exception instanceof ErrorException ) 
		{
			$code = $exception->getSeverity();
		}
		return $code;
	}
	protected function getMessage(\Exception $exception) 
	{
		$message = $exception->getMessage();
		if( IS_CLI ) 
		{
			return $message;
		}
		if( strpos($message, ":") ) 
		{
			$name = strstr($message, ":", true);
			$message = (\think\Lang::has($name) ? \think\Lang::get($name) . strstr($message, ":") : $message);
		}
		else 
		{
			if( strpos($message, ",") ) 
			{
				$name = strstr($message, ",", true);
				$message = (\think\Lang::has($name) ? \think\Lang::get($name) . ":" . substr(strstr($message, ","), 1) : $message);
			}
			else 
			{
				if( \think\Lang::has($message) ) 
				{
					$message = \think\Lang::get($message);
				}
			}
		}
		return $message;
	}
	protected function getSourceCode(\Exception $exception) 
	{
		$line = $exception->getLine();
		$first = (0 < $line - 9 ? $line - 9 : 1);
		try 
		{
			$contents = file($exception->getFile());
			$source = array( "first" => $first, "source" => array_slice($contents, $first - 1, 19) );
		}
		catch( \Exception $e ) 
		{
			$source = array( );
		}
		return $source;
	}
	protected function getExtendData(\Exception $exception) 
	{
		$data = array( );
		if( $exception instanceof \think\Exception ) 
		{
			$data = $exception->getData();
		}
		return $data;
	}
	private static function getConst() 
	{
		return get_defined_constants(true)["user"];
	}
}
?>