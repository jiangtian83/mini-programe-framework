<?php  class WxPayNotify extends WxPayNotifyReply 
{
	final public function Handle($needSign = true) 
	{
		file_put_contents("./weixinQuery.txt", 123456, FILE_APPEND);
		$msg = "OK";
		$result = WxpayApi::notify(array( $this, "NotifyCallBack" ), $msg);
		if( $result == false ) 
		{
			file_put_contents("./weixinQuery.txt", "false", FILE_APPEND);
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
			$this->ReplyNotify(false);
		}
		else 
		{
			file_put_contents("./weixinQuery.txt", "true", FILE_APPEND);
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
			$this->ReplyNotify($needSign);
		}
	}
	public function NotifyProcess($data, &$msg) 
	{
		return true;
	}
	final public function NotifyCallBack($data) 
	{
		$msg = "OK";
		$aaa = json_encode($data);
		file_put_contents("./weixinQuery.txt", "NotifyCallBack:data---:" . $aaa, FILE_APPEND);
		$result = $this->NotifyProcess($data, $msg);
		if( $result == true ) 
		{
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
		}
		else 
		{
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
		}
		return $result;
	}
	final private function ReplyNotify($needSign = true) 
	{
		if( $needSign == true && $this->GetReturn_code($return_code) == "SUCCESS" ) 
		{
			$this->SetSign();
		}
		WxpayApi::replyNotify($this->ToXml());
	}
}
?>