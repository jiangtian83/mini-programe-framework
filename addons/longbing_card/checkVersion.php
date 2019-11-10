<?php  $check_php = PHP_VERSION;
if( !$check_php ) 
{
	echo "未检查到php版本信息, 请检查是否安装php, 且php版本必须是5.6以上" . "<br>";
	$is_stop = true;
}
else 
{
	$check_php_arr = explode(".", $check_php);
	if( $check_php_arr[0] < 6 && $check_php_arr[1] < 6 ) 
	{
		echo "当前php版本为" . $check_php . ", 请使用5.6或者7.1版本的php" . "<br>";
		$is_stop = true;
	}
}
if( $is_stop ) 
{
	echo "如需帮助请联系联系售后!" . "<br>";
	exit();
}
?>