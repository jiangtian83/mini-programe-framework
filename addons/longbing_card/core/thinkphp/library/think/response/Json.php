<?php  namespace think\response;
class Json extends \think\Response 
{
	protected $options = NULL;
	protected $contentType = "application/json";
	protected function output($data) 
	{
		try 
		{
			$data = json_encode($data, $this->options["json_encode_param"]);
			if( $data === false ) 
			{
				throw new \InvalidArgumentException(json_last_error_msg());
			}
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