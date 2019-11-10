<?php  namespace traits\controller;
trait Jump 
{
	protected function success($msg = "", $url = NULL, $data = "", $wait = 3, array $header = array( )) 
	{
		if( is_null($url) && !is_null(\think\Request::instance()->server("HTTP_REFERER")) ) 
		{
			$url = \think\Request::instance()->server("HTTP_REFERER");
		}
		else 
		{
			if( "" !== $url && !strpos($url, "://") && 0 !== strpos($url, "/") ) 
			{
				$url = \think\Url::build($url);
			}
		}
		$type = $this->getResponseType();
		$result = array( "code" => 1, "msg" => $msg, "data" => $data, "url" => $url, "wait" => $wait );
		if( "html" == strtolower($type) ) 
		{
			$template = \think\Config::get("template");
			$view = \think\Config::get("view_replace_str");
			$result = \think\View::instance($template, $view)->fetch(\think\Config::get("dispatch_success_tmpl"), $result);
		}
		$response = \think\Response::create($result, $type)->header($header);
		throw new \think\exception\HttpResponseException($response);
	}
	protected function error($msg = "", $url = NULL, $data = "", $wait = 3, array $header = array( )) 
	{
		if( is_null($url) ) 
		{
			$url = (\think\Request::instance()->isAjax() ? "" : "javascript:history.back(-1);");
		}
		else 
		{
			if( "" !== $url && !strpos($url, "://") && 0 !== strpos($url, "/") ) 
			{
				$url = \think\Url::build($url);
			}
		}
		$type = $this->getResponseType();
		$result = array( "code" => 0, "msg" => $msg, "data" => $data, "url" => $url, "wait" => $wait );
		if( "html" == strtolower($type) ) 
		{
			$template = \think\Config::get("template");
			$view = \think\Config::get("view_replace_str");
			$result = \think\View::instance($template, $view)->fetch(\think\Config::get("dispatch_error_tmpl"), $result);
		}
		$response = \think\Response::create($result, $type)->header($header);
		throw new \think\exception\HttpResponseException($response);
	}
	protected function result($data, $code = 0, $msg = "", $type = "", array $header = array( )) 
	{
		$result = array( "code" => $code, "msg" => $msg, "time" => \think\Request::instance()->server("REQUEST_TIME"), "data" => $data );
		$type = ($type ?: $this->getResponseType());
		$response = \think\Response::create($result, $type)->header($header);
		throw new \think\exception\HttpResponseException($response);
	}
	protected function redirect($url, $params = array( ), $code = 302, $with = array( )) 
	{
		if( is_integer($params) ) 
		{
			$code = $params;
			$params = array( );
		}
		$response = new \think\response\Redirect($url);
		$response->code($code)->params($params)->with($with);
		throw new \think\exception\HttpResponseException($response);
	}
	protected function getResponseType() 
	{
		return (\think\Request::instance()->isAjax() ? \think\Config::get("default_ajax_return") : \think\Config::get("default_return_type"));
	}
}
?>