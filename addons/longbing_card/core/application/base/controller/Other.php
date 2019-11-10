<?php  namespace app\base\controller;
class Other extends \app\common\controller\Base 
{
	public function notify() 
	{
		$xmlData = file_get_contents("php://input");
		if( empty($xmlData) ) 
		{
			$xmlData = "empty   xmlData";
		}
		$jsonxml = @json_encode(@simplexml_load_string($xmlData, "SimpleXMLElement", LIBXML_NOCDATA));
		$array = @json_decode($jsonxml, true);
		if( is_array($array) && isset($array["result_code"]) && isset($array["return_code"]) && $array["result_code"] == "SUCCESS" && $array["return_code"] == "SUCCESS" && isset($array["out_trade_no"]) && isset($array["transaction_id"]) ) 
		{
			$out_trade_no = $array["out_trade_no"];
			$transaction_id = $array["transaction_id"];
			$order = new \app\base\model\BaseOrder();
			$info = $order->get(array( "out_trade_no" => $out_trade_no ));
			if( $info ) 
			{
				$info->pay_status = 1;
				$info->transaction_id = $transaction_id;
				$result = $info->save();
				$this->editCompany($out_trade_no);
				if( $result ) 
				{
					$str = "<xml>\r\n  <return_code><![CDATA[SUCCESS]]></return_code>\r\n  <return_msg><![CDATA[OK]]></return_msg>\r\n</xml>";
					sleep(3);
					$where = array( "a.pay_status" => 1, "b.transaction_id" => "" );
					$field = "a.out_trade_no as a_out_trade_no, a.transaction_id as a_transaction_id, b.out_trade_no, b.transaction_id";
					$list = $order->alias("a")->join("lb_card_order b", "a.out_trade_no = b.out_trade_no", "left")->field($field)->where($where)->select();
					$card_order = new \app\card\model\CardOrder();
					foreach( $list as $index => $item ) 
					{
						$card_order->save(array( "transaction_id" => $item["a_transaction_id"] ), array( "out_trade_no" => $item["out_trade_no"] ));
					}
					return $str;
				}
			}
		}
	}
	protected function editCompany($out_trade_no) 
	{
		$model = new \app\card\model\CardOrder();
		$info = $model->get(array( "out_trade_no" => $out_trade_no ));
		if( !$info ) 
		{
			return false;
		}
		if( $info["pay_status"] ) 
		{
			return true;
		}
		$info->pay_status = 1;
		$info->save();
		$company_id = $info["company_id"];
		$package_id = $info["package_id"];
		$companyModel = new \app\card\model\CardCompany();
		$company = $companyModel->get($company_id);
		$packageModel = new \app\card\model\CardPackage();
		if( $package_id ) 
		{
			$package = $packageModel->get($package_id);
		}
		if( !$company ) 
		{
			return false;
		}
		if( $info["is_upgrade"] ) 
		{
			$company->staff_number = $company->staff_number + $info["number"];
		}
		else 
		{
			$time = time();
			$time = $time + $info->days * 24 * 60 * 60;
			$time = round($time);
			$company->staff_number = $info->number;
			$company->end_time = $time;
			if( $info["is_trial"] ) 
			{
				$company->trial = 1;
				$company->trial_time = $time;
			}
		}
		$company->save();
		$this->recordCompanyId($company["id"], $company["uniacid"]);
		return true;
	}
	public function getImage() 
	{
		$id = input("param.id");
		$path = input("param.path");
		if( !$id && !$path ) 
		{
			resultJsonStr(-1, "请传入参数", array( ));
		}
		if( $id ) 
		{
			$model = new BaseAttachment();
			$path = $model->getFilePath($id);
		}
		if( $path ) 
		{
			$path = $_SERVER["QUERY_STRING"];
			$position = strpos($path, "getImage/path/");
			$sub_str = substr($path, $position + 14);
			$path = urldecode($sub_str);
		}
		$type_img = getimagesize($path);
		ob_start();
		if( strpos($type_img["mime"], "jpeg") ) 
		{
			$resourch = imagecreatefromjpeg($path);
			imagejpeg($resourch);
		}
		else 
		{
			if( strpos($type_img["mime"], "png") ) 
			{
				$resourch = imagecreatefrompng($path);
				imagepng($resourch);
			}
		}
		$content = ob_get_clean();
		imagedestroy($resourch);
		return response($content, 200, array( "Content-Length" => strlen($content) ))->contentType("image/png");
	}
}
?>