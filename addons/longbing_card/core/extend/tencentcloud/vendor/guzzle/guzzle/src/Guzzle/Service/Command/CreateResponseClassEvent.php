<?php  namespace Guzzle\Service\Command;
class CreateResponseClassEvent extends \Guzzle\Common\Event 
{
	public function setResult($result) 
	{
		$this["result"] = $result;
		$this->stopPropagation();
	}
	public function getResult() 
	{
		return $this["result"];
	}
}
?>