<?php  namespace app\common\behavior;
class PartnerMoney 
{
	public function run(&$params) 
	{
		$params = unserialize($params);
		$uid = $params["uid"];
		$uniacid = $params["uniacid"];
		$money = $params["money"];
		$order_type = $params["order_type"];
		$userModel = new \app\common\model\MingpianUser();
		$name = \app\common\model\MingpianCard::where(array( "uniacid" => $uniacid, "uid" => $uid ))->value("name");
		if( !$name ) 
		{
			$name = "游客_" . $uid;
		}
		lb_logOutput("支付用户name:-----" . $name, 1);
		$p_relation = \app\common\model\MingpianPartnerRelation::get(array( "child_id" => $uid, "uniacid" => $uniacid ));
		if( $p_relation ) 
		{
			$p_uid = $p_relation["pid"];
			lb_logOutput("有直接上级UID:-----" . $p_uid, 1);
			$p_partner_level = \app\common\model\MingpianUser::where(array( "uniacid" => $uniacid, "id" => $p_uid ))->value("partner_level");
			$p_level = \app\common\model\MingpianPartnerLevel::get(array( "uniacid" => $uniacid, "idn" => $p_partner_level ));
			if( $p_level ) 
			{
				lb_logOutput("有分销比例\$p_level:-----" . $p_level["proportion1"], 1);
				$balance = $userModel->where(array( "uniacid" => $uniacid, "id" => $p_uid ))->value("balance");
				if( !$balance ) 
				{
					$balance = 0;
				}
				$p_rate = $p_level["proportion1"];
				$p_money = ($money * $p_rate) / 100;
				lb_logOutput("分销金额\$p_money:-----" . $p_money, 1);
				if( 1 <= $p_money && 1 <= round($p_money) ) 
				{
					lb_logOutput("金额正确:-----" . $p_money, 1);
					$p_wallet_data = array( "uniacid" => $uniacid, "uid" => $p_uid, "child_uid" => $uid, "money" => round($p_money), "mode" => 1, "balance" => $balance + round($p_money), "child_name" => $name, "child_level" => 1, "order_type" => $order_type, "title" => "成功支付" . $money / 100 . "元" );
					lb_logOutput("直接记录数组-----" . json_encode($p_wallet_data), 1);
					$walletModel = new \app\common\model\MingpianPartnerWallet();
					$p_log_res = $walletModel->save($p_wallet_data);
					if( $p_log_res ) 
					{
						lb_logOutput("保存记录成功:-----" . $p_log_res, 1);
						$userModel->update(array( "balance" => $balance + round($p_money) ), array( "id" => $p_uid ));
					}
				}
			}
			if( $p_relation ) 
			{
				$t_relation = \app\common\model\MingpianPartnerRelation::get(array( "child_id" => $p_relation["pid"], "uniacid" => $uniacid ));
				if( $t_relation ) 
				{
					$t_uid = $t_relation["pid"];
					lb_logOutput("有间接上级UID:-----" . $t_uid, 1);
					$t_partner_level = \app\common\model\MingpianUser::where(array( "uniacid" => $uniacid, "id" => $t_uid ))->value("partner_level");
					$t_level = \app\common\model\MingpianPartnerLevel::get(array( "uniacid" => $uniacid, "idn" => $t_partner_level ));
					if( $t_level ) 
					{
						$balance = $userModel->where(array( "uniacid" => $uniacid, "id" => $t_uid ))->value("balance");
						if( !$balance ) 
						{
							$balance = 0;
						}
						$t_rate = $t_level["proportion2"];
						lb_logOutput("有间接分销比例\$t_level:-----" . $t_rate, 1);
						$t_money = ($money * $t_rate) / 100;
						if( 1 <= $t_money && 1 <= round($t_money) ) 
						{
							lb_logOutput("间接金额正确" . $t_money, 1);
							$t_wallet_data = array( "uniacid" => $uniacid, "uid" => $t_uid, "child_uid" => $uid, "money" => round($t_money), "mode" => 1, "balance" => $balance + round($t_money), "child_name" => $name, "child_level" => 2, "order_type" => $order_type, "title" => "成功支付" . $money / 100 . "元" );
							lb_logOutput("间接记录数组-----" . json_encode($t_wallet_data), 1);
							$walletModel = new \app\common\model\MingpianPartnerWallet();
							$t_log_res = $walletModel->save($t_wallet_data);
							if( $t_log_res ) 
							{
								lb_logOutput("间接记录生成成功" . $t_log_res, 1);
								$userModel->update(array( "balance" => $balance + round($t_money) ), array( "id" => $t_uid ));
							}
						}
					}
				}
			}
		}
		return true;
	}
}
?>