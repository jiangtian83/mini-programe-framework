<?php  namespace app\base\model;
class BaseConfig extends \think\Model 
{
	protected $name = "longbing_card_config";
	protected $autoWriteTimestamp = true;
	public static function getArrRulesByDriver($driver = "", $uniacid = 0) 
	{
		if( $driver == "qiniuyun" ) 
		{
			$column = "qiniu_rules";
		}
		else 
		{
			if( $driver == "aliyun" ) 
			{
				$column = "aliyun_rules";
			}
			else 
			{
				return false;
			}
		}
		$value = self::where(array( "uniacid" => $uniacid ))->value($column);
		$rules = explode("\n", $value);
		$arr = array( );
		if( $rules ) 
		{
			foreach( $rules as $key => $money ) 
			{
				$start = mb_strpos($money, ":");
				$title = mb_substr($money, 0, $start);
				$val = mb_substr($money, $start + 1);
				$arr[$title] = $val;
			}
		}
		return $arr;
	}
}
?>