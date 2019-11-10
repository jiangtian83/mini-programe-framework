<?php  namespace think\response;
class Xml extends \think\Response 
{
	protected $options = array( "root_node" => "think", "root_attr" => "", "item_node" => "item", "item_key" => "id", "encoding" => "utf-8" );
	protected $contentType = "text/xml";
	protected function output($data) 
	{
		return $this->xmlEncode($data, $this->options["root_node"], $this->options["item_node"], $this->options["root_attr"], $this->options["item_key"], $this->options["encoding"]);
	}
	protected function xmlEncode($data, $root, $item, $attr, $id, $encoding) 
	{
		if( is_array($attr) ) 
		{
			$array = array( );
			foreach( $attr as $key => $value ) 
			{
				$array[] = (string) $key . "=\"" . $value . "\"";
			}
			$attr = implode(" ", $array);
		}
		$attr = trim($attr);
		$attr = (empty($attr) ? "" : " " . $attr);
		$xml = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>";
		$xml .= "<" . $root . $attr . ">";
		$xml .= $this->dataToXml($data, $item, $id);
		$xml .= "</" . $root . ">";
		return $xml;
	}
	protected function dataToXml($data, $item, $id) 
	{
		$xml = $attr = "";
		if( $data instanceof \think\Collection || $data instanceof \think\Model ) 
		{
			$data = $data->toArray();
		}
		foreach( $data as $key => $val ) 
		{
			if( is_numeric($key) ) 
			{
				$id and $key = $item;
			}
			$xml .= "<" . $key . $attr . ">";
			$xml .= (is_array($val) || is_object($val) ? $this->dataToXml($val, $item, $id) : $val);
			$xml .= "</" . $key . ">";
		}
		return $xml;
	}
}