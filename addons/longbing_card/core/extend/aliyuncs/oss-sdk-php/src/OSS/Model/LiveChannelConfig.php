<?php  namespace OSS\Model;
class LiveChannelConfig implements XmlConfig 
{
	private $description = NULL;
	private $status = "enabled";
	private $type = NULL;
	private $fragDuration = 5;
	private $fragCount = 3;
	private $playListName = "playlist.m3u8";
	public function __construct($option = array( )) 
	{
		if( isset($option["description"]) ) 
		{
			$this->description = $option["description"];
		}
		if( isset($option["status"]) ) 
		{
			$this->status = $option["status"];
		}
		if( isset($option["type"]) ) 
		{
			$this->type = $option["type"];
		}
		if( isset($option["fragDuration"]) ) 
		{
			$this->fragDuration = $option["fragDuration"];
		}
		if( isset($option["fragCount"]) ) 
		{
			$this->fragCount = $option["fragCount"];
		}
		if( isset($option["playListName"]) ) 
		{
			$this->playListName = $option["playListName"];
		}
	}
	public function getDescription() 
	{
		return $this->description;
	}
	public function getStatus() 
	{
		return $this->status;
	}
	public function getType() 
	{
		return $this->type;
	}
	public function getFragDuration() 
	{
		return $this->fragDuration;
	}
	public function getFragCount() 
	{
		return $this->fragCount;
	}
	public function getPlayListName() 
	{
		return $this->playListName;
	}
	public function parseFromXml($strXml) 
	{
		$xml = simplexml_load_string($strXml);
		$this->description = strval($xml->Description);
		$this->status = strval($xml->Status);
		$target = $xml->Target;
		$this->type = strval($target->Type);
		$this->fragDuration = intval($target->FragDuration);
		$this->fragCount = intval($target->FragCount);
		$this->playListName = strval($target->PlayListName);
	}
	public function serializeToXml() 
	{
		$strXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<LiveChannelConfiguration>\r\n</LiveChannelConfiguration>";
		$xml = new \SimpleXMLElement($strXml);
		if( isset($this->description) ) 
		{
			$xml->addChild("Description", $this->description);
		}
		if( isset($this->status) ) 
		{
			$xml->addChild("Status", $this->status);
		}
		$node = $xml->addChild("Target");
		$node->addChild("Type", $this->type);
		if( isset($this->fragDuration) ) 
		{
			$node->addChild("FragDuration", $this->fragDuration);
		}
		if( isset($this->fragCount) ) 
		{
			$node->addChild("FragCount", $this->fragCount);
		}
		if( isset($this->playListName) ) 
		{
			$node->addChild("PlayListName", $this->playListName);
		}
		return $xml->asXML();
	}
	public function __toString() 
	{
		return $this->serializeToXml();
	}
}