<?php
##############################
php
##############################
#### Mod Joe. 13.04.2013 #####
##############################
	Error_Reporting(0);
	include ($_SERVER['DOCUMENT_ROOT'].'/configs/config.php');
	$db = new MySQL(SQL_USER, SQL_PASS, SQL_BASE);
	
	// Кодировка уже установлена
	
	$sql = $db->sql('SELECT `address` FROM `images` WHERE `stype`="'.addslashes($_GET['type']).'" ');
	
	$check = 1;
	while($s = mysql_fetch_row($sql) and $check++)
		echo $s[0].'|';
	if ($check==1) echo 'none';
	
	mysql_close($main_conn);
?>