<?php  require_once("../lib/WxPay.Api.php");
class MicroPay 
{
	public function pay($microPayInput) 
	{
		$result = WxPayApi::micropay($microPayInput, 5);
		if( !array_key_exists("return_code", $result) || !array_key_exists("out_trade_no", $result) || !array_key_exists("result_code", $result) ) 
		{
			echo "接口调用失败,请确认是否输入是否有误！";
			throw new WxPayException("接口调用失败！");
		}
		$out_trade_no = $microPayInput->GetOut_trade_no();
		if( $result["return_code"] == "SUCCESS" && $result["result_code"] == "FAIL" && $result["err_code"] != "USERPAYING" && $result["err_code"] != "SYSTEMERROR" ) 
		{
			return false;
		}
		$queryTimes = 10;
		if( 0 < $queryTimes ) 
		{
			$succResult = 0;
			$queryResult = $this->query($out_trade_no, $succResult);
			if( $succResult == 2 ) 
			{
				sleep(2);
				continue;
			}
			if( $succResult == 1 ) 
			{
				return $queryResult;
			}
			return false;
		}
		if( !$this->cancel($out_trade_no) ) 
		{
			throw new WxpayException("撤销单失败！");
		}
		return false;
	}
	public function query($out_trade_no, &$succCode) 
	{
		$queryOrderInput = new WxPayOrderQuery();
		$queryOrderInput->SetOut_trade_no($out_trade_no);
		$result = WxPayApi::orderQuery($queryOrderInput);
		if( $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS" ) 
		{
			if( $result["trade_state"] == "SUCCESS" ) 
			{
				$succCode = 1;
				return $result;
			}
			if( $result["trade_state"] == "USERPAYING" ) 
			{
				$succCode = 2;
				return false;
			}
		}
		if( $result["err_code"] == "ORDERNOTEXIST" ) 
		{
			$succCode = 0;
		}
		else 
		{
			$succCode = 2;
		}
		return false;
	}
	public function cancel($out_trade_no, $depth = 0) 
	{
		if( 10 < $depth ) 
		{
			return false;
		}
		$clostOrder = new WxPayReverse();
		$clostOrder->SetOut_trade_no($out_trade_no);
		$result = WxPayApi::reverse($clostOrder);
		if( $result["return_code"] != "SUCCESS" ) 
		{
			return false;
		}
		if( $result["result_code"] != "SUCCESS" && $result["recall"] == "N" ) 
		{
			return true;
		}
		if( $result["recall"] == "Y" ) 
		{
			return $this->cancel($out_trade_no, ++$depth);
		}
		return false;
	}
}
?>