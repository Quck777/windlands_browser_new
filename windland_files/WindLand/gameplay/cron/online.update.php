<?php
##############################
php

	## Раз в 5-10 минут.. // */05 * * * * root /usr/local/bin/php /home/www/gameplay/cron/online.update.php 
	
	define('MICROLOAD', true);
	// Загружаем файл конфига, ВАЖНЫЙ.
	require ('/home/w/windlands/public_html/configs/config.php');
	// Подключаемся к SQL базе
	$db = new MySQL(SQL_USER, SQL_PASS, SQL_BASE);
	############################## 
	
	include (ROOT.'/inc/func.php');
	
	$t = tme();
	$t1 = $t-360+microtime();
	$max = (int)$db->sqlr("SELECT `max_online` FROM `configs` LIMIT 0,1");
	$vsego = (int)$db->sqlr("SELECT COUNT(uid) FROM `users` WHERE `online`='1';");
	
	if ( $max < $vsego ) $db->sql("UPDATE `configs` SET `max_online`=".$vsego.", `time_max_online`=".$t."");
	
	$db->sql("UPDATE `users` SET `online`='0', `timeonline`=timeonline+lastom-lastvisits, `gain_time`=0 WHERE `lasto` < ".$t1." and `lastom` < ".$t1." and `online`=1;");

//	$usr = $db->sql('SELECT `user` FROM `users` WHERE `priveleged`=1 and `online`=1 ORDER BY `uid`;');
//	while ( $us = mysql_fetch_row($usr) ) say_to_chat ('m','Онлайн обновлен. Было <b>'.$vsego.'</b>.',1,$us[0],'*',0);

?>