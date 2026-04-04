<?php
	## Раз в час
	define('MICROLOAD', true);
	// Загружаем файл конфига, ВАЖНЫЙ.
	include ($_SERVER['DOCUMENT_ROOT'].'/configs/config.php');
	// Подключаемся к SQL базе
	$db = new MySQL(SQL_USER, SQL_PASS, SQL_BASE);
	############################## 
	
	include (ROOT.'/inc/func.php');
	include (ROOT.'/inc/quest/all_param.php'); 
	
	
	$t1 = $db->sqla("SELECT * FROM `quest` WHERE id = ".TOUR1."");
	$t2 = $db->sqla("SELECT * FROM `quest` WHERE id = ".TOUR2."");
	$t3 = $db->sqla("SELECT * FROM `quest` WHERE id = ".TOUR3."");
	if ( $t1['finished'] and $t1['time']<tme() ) //Начинаем турнир
	{
		$db->sql("UPDATE `quest` SET `finished`=0, `time`=".tme().", `type`=0 WHERE `id` = ".TOUR1);
		say_to_chat ("a","На арене начался Турнир №1. Приглашаются все персонажи 5-10 уровня.",0,'','*',0);
	}
	if ( $t2['finished'] and $t2['time']<tme() ) //Начинаем турнир
	{
		$db->sql("UPDATE `quest` SET `finished`=0, `time`=".tme().", `type`=0 WHERE `id` = ".TOUR2);
		say_to_chat ("a","На арене начался Турнир №2. Приглашаются все персонажи 10-15 уровня.",0,'','*',0);
	}
	if ( $t3['finished'] and $t3['time']<tme() ) //Начинаем турнир
	{
		$db->sql("UPDATE `quest` SET `finished`=0, `time`=".tme().", `type`=0 WHERE `id` = ".TOUR3);
		say_to_chat ("a","На арене начался Турнир №3. Приглашаются все персонажи 15-50 уровня.",0,'','*',0);
	}
	
	#### Для отладки
	/*
	$t = 3600;
	sql("UPDATE `quest` SET `finished`=1, `time`=".(tme()+$t*1).", `type`=0 WHERE `id` = ".TOUR1);
	sql("UPDATE `quest` SET `finished`=1, `time`=".(tme()+$t*2).", `type`=0 WHERE `id` = ".TOUR2);
	sql("UPDATE `quest` SET `finished`=1, `time`=".(tme()+$t*3).", `type`=0 WHERE `id` = ".TOUR3);
	*/
?>