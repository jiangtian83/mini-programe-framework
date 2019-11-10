<?php  namespace think\response;
class Jsonp extends \think\Response 
{
	protected $options = NULL;
	protected $contentType = "application/javascript";
	protected function output($data) 
	{
		try 
		{
			$var_jsonp_handler = \think\Request::instance()->param($this->options["var_jsonp_handler"], "");
			$handler = (!empty($var_jsonp_handler) ? $var_jsonp_handler : $this->options["default_jsonp_handler"]);
			$data = json_encode($data, $this->options["json_encode_param"]);
			if( $data === false ) 
			{
				throw new \InvalidArgumentException(json_last_error_msg());
			}
			$data = $handler . "(" . $data . ");";
			return $data;
		}
		catch( \Exception $e ) 
		{
			if( $e->getPrevious() ) 
			{
				throw $e->getPrevious();
			}
			throw $e;
		}
	}
}
?>