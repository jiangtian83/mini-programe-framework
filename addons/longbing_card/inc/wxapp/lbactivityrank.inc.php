<?php  global $_GPC;
global $_W;
$phone = $_GPC["phone"];
$uniacid = $_W["uniacid"];
$user_id = NULL;
if( $phone ) 
{
	$info = pdo_get("longbing_card_user_phone", array( "phone" => $phone, "uniacid" => $uniacid ));
	if( $info ) 
	{
		$user_id = $info["user_id"];
	}
}
$start = 1549123200;
$end = 1550591999;
$limit = array( 1, 20 );
$curr = 1;
if( isset($_GPC["page"]) ) 
{
	$limit[0] = $_GPC["page"];
	$curr = $_GPC["page"];
}
$rank = pdo_fetchall("SELECT count(a.pid) AS `count`, a.pid, b.id, b.nickName, b.avatarUrl FROM ims_longbing_card_user a \r\nLEFT JOIN ims_longbing_card_user b ON a.pid = b.id \r\nWHERE  a.pid != 0 && a.uniacid = 4 && b.id > 0 && \r\n a.create_time BETWEEN 1549123200 AND 1550591999 GROUP BY a.pid ORDER BY `count` DESC");
$ranking = 0;
$limit = 0;
$count = count($rank);
$rank = array_slice($rank, 0, 50);
$my_count = 0;
$my_name = "";
if( $user_id ) 
{
	foreach( $rank as $index => $item ) 
	{
		if( $item["id"] == $user_id ) 
		{
			if( $index != 0 ) 
			{
				$limit = $rank[$index - 1]["count"] - $rank[$index]["count"];
				$limit = ($limit == 0 ? 1 : $limit);
			}
			$ranking = $index + 1;
			$my_count = $item["count"];
			$my_name = $item["nickName"];
		}
	}
}
$user_ids = "";
foreach( $rank as $index => $item ) 
{
	$user_ids .= "," . $item["pid"];
}
$user_ids = trim($user_ids, ",");
$user_ids = "(" . $user_ids . ")";
$phones = pdo_fetchall("SELECT * FROM ims_longbing_card_user_phone WHERE user_id in " . $user_ids);
$phones_tmp = array( );
foreach( $phones as $index => $item ) 
{
	$phones_tmp[$item["user_id"]] = $item["phone"];
}
foreach( $rank as $index => $item ) 
{
	$num = (isset($phones_tmp[$item["id"]]) ? $phones_tmp[$item["id"]] : "18888888888");
	$num = ($num ? $num : "18888888888");
	$rank[$index]["phone"] = $num;
	$rank[$index]["rank_this"] = $index + 1;
}
$rank = array_slice($rank, 0, 50);
$data = array( "count" => $my_count, "ranking" => $ranking, "my_name" => $my_name, "limit" => $limit, "list" => $rank );
return $this->result(0, "suc", $data);
function Db2Eexcel($day, $tableName = "用户注册信息") 
{
	require_once("PHPExcel/PHPExcel.php");
	$excel = new PHPExcel();
	$sheet = $excel->getActiveSheet();
	$letter = array( "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N" );
	$tableheader = array( "UID", "姓名", "性别", "电话", "Email", "QQ", "层次", "专业", "页面类型", "提交页面", "所在地区", "IP", "提交时间", "备注" );
	for( $i = 0; $i < count($tableheader);
	$i++ ) 
	{
		$sheet->getCell((string) $letter[$i] . "1")->setValue((string) $tableheader[$i]);
		$sheet->getStyle((string) $letter[$i] . "1")->getAlignment()->setHorizontal("center");
		$sheet->getStyle((string) $letter[$i] . "1")->getAlignment()->setVertical("center");
		$sheet->getStyle((string) $letter[$i] . "1")->getFont()->getColor()->setRGB("b5211a");
		$sheet->getStyle((string) $letter[$i] . "1")->getFont()->setSize(12);
		$sheet->getStyle((string) $letter[$i])->getFont()->setSize(10);
		$sheet->getStyle((string) $letter[$i] . "1")->getFont()->setBold(true);
		$sheet->getColumnDimension((string) $letter[$i])->setWidth(20);
	}
	$sheet->getRowDimension("1")->setRowHeight(30);
	$sheet->getColumnDimension("A")->setWidth(10);
	$sheet->getColumnDimension("C")->setWidth(10);
	$sheet->getColumnDimension("J")->setWidth(70);
	$model = ModelFactory::M("RegisterFormModel");
	$data = $model->getAllFormByDay($day);
	$i = 2;
	for( $k = 0; $i <= count($data) + 1;
	$k++ ) 
	{
		$j = 0;
		if( !empty($data[$k]["createTime"]) ) 
		{
			$data[$k]["createTime"] = date("Y-m-d H:i:s", $data[$k]["createTime"]);
		}
		if( !empty($data[$k]["sex"]) ) 
		{
			$data[$k]["sex"] = ($data[$k]["sex"] == 1 ? "男" : "女");
		}
		foreach( $data[$i - 2] as $key => $value ) 
		{
			$sheet->getCell((string) $letter[$j] . $i)->setValue((string) $value);
			$sheet->getStyle((string) $letter[$j] . $i)->getAlignment()->setHorizontal("center");
			$j++;
		}
		$i++;
	}
	$writer = PHPExcel_IOFactory::createWriter($excel, "Excel2007");
	self::browser_export("Excel2007", $tableName . ".xlsx");
	$writer->save("php://output");
}
?>