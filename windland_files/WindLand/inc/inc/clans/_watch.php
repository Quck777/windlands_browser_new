<?php
if ( defined('CLANS')==false or $player->pers['sign']!='watchers' ) {echo '<center>Ошибка.</center>'; exit;}

if (isset($clan)) $c=1;
$data = '';

######## Прикрепить, Закрыть, переместить, удалить, изменить, закрепить, скрыть, удалить сообщение, изменить сообщение, создать форум, создать раздел
$forum_a = Array(2, 32, 64, 128, 256, 512, 2048, 4096, 8192, 32768, 65536);
$imglist = Array('w01','w02','w03','w04','w05','w06','w07','w08','w09','w10','w11','w12','w13','w14','w15','w16','w17','w18','w19','w20','w22','w23','w24','w25','w26','w27','w90');
$nameimg = Array('Стажёр', 'Стажёр отдела', 'Сотрудник БЧРиРВС', 'Начальник БЧРиРВС', 'Сотрудник ЮО', 'Начальник ЮО', 'Сотрудник АО', 'Начальник АО', 'Сотрудник ОРК', 'Начальник ОРК', 'Сотрудник ОРП', 'Начальник ОРП', '1', '2', 'Сотрудник ОМ', 'Начальник ОМ', '5', '6', 'Сотрудник ФО', 'Начальник ФО', '9', '10', '11', '12', 'Сотрудник СВБ', 'Начальник СВБ', 'Зам. Главы Смотрителей', '', '', '', '', '', '', '', '');
######## 			1				2				3					4					5				6				7				8				9				10				11					12

function gen_img_list()
{
	GLOBAL $imglist;
	$r = '';
	for ($i=0; $i<count($imglist); $i++) $r.= "'".$imglist[$i]."',";
	$r = substr($r,0,strlen($r)-1);
	return $r;
}

function gen_names_list()
{
	GLOBAL $nameimg;
	$r = '';
	for ($i=0; $i<count($nameimg); $i++) $r.= "'".$nameimg[$i]."',";
	$r = substr($r,0,strlen($r)-1);
	return $r;
}

function w_sostav()
{
	GLOBAL $db;
	$sstav = $db->sql("SELECT uid, user, online, location, state, level, clan_state, lastom, silence, clan_tr FROM `users` WHERE `sign`='watchers' ORDER BY `clan_state` DESC", __FILE__,__LINE__,__FUNCTION__,__CLASS__); //ASC
	$GIF = 'watchers';
	while ( $ws = mysql_fetch_assoc($sstav) )
	{
		if ($ws["uid"] == 7) $ws = j_pers($ws);
		if ($ws['online']==1)
		{
			$loc = $db->sqla_id("SELECT `name` FROM `locations` WHERE `id`='".$ws['location']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
			$loc = $loc[0];
		} else $loc = $loc = time_echo(tme()-$ws['lastom']);
		if ( !empty($ws['clan_state']) ) $GIF = $ws['clan_state'];
		if ( isset($stv) ) $stv.= ",[".$ws['online'].",'".$GIF."', '".$ws['user']."', ".$ws['level'].", '".$ws['clan_state']."', '".$ws['state']."', '".$loc."', ".$ws['uid'].", ".$ws['clan_tr']."]";
		else $stv = "[".$ws['online'].",'".$GIF."', '".$ws['user']."', ".$ws['level'].", '".$ws['clan_state']."', '".$ws['state']."', '".$loc."', ".$ws['uid'].", ".$ws['clan_tr']."]";
	}
	unset($ws);
	echo "var stv = [".$stv."];\n";
}
# Выгоняем из смотров
if( $c==1 and ($status=='wg' or $ppr[1]==true) and isset($http->get['go_out']) and $http->get['clan_act']==1 )
{
	$go_out = $db->sqla("SELECT `uid`, `user` FROM `users` WHERE `uid`=".intval($http->get['go_out'])." and `sign`='watchers' and `clan_state`<>'wg'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	if ($go_out['uid']==true)
	{
		$db->sql("UPDATE `users` SET `sign`='none', `state`='' , `rank`='', `clan_state`='', `clan_prev`='', `forum_accesses`='1' WHERE `uid`='".$go_out['uid']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		report("Персонаж <b>".$go_out['user']."</b> исключен из Смотрителей!");
		clans_log ($go_out['uid'], $player->pers['user'], 2, 'watchers');
	}
	else report("Нет такого персонажа.");
}

# Принимаем в смотры
if ( $c==1 and ($status=='wg' or $ppr[0]==true) and isset($http->post['go_in']) )
{
	$go_in = $db->sqla("SELECT `sign`, `uid`, `user` FROM `users` WHERE `user`='".$http->post['go_in']."' and `level`>4", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	if ($go_in['uid']==true and $go_in['sign']=='none') 
	{
		$db->sql("UPDATE `users` SET `sign`='watchers', `state`='Стажёр', `rank`='', `clan_state`='w01', `clan_prev`='' WHERE `uid`='".$go_in['uid']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		report("Персонаж <b>".$go_in['user']."</b> принят в Смотрители.");
		clans_log($go_in['uid'], $player->pers['user'], 1, 'watchers');
	}
	else report("Нет такого персонажа или персонаж уже находится в клане.");
	unset($go_in);
}

# Передача главенства
if ( $status=='wg' and isset($http->post['do_glav']) ) 
{
	$p = $db->sqla("SELECT `uid`, `sign`, `user` FROM `users` WHERE `smuser`=LOWER('".$http->post['do_glav']."') and `sign`='watchers'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	if ( $p['uid']==true and $p['sign']==$player->pers['sign'] and $p['user']<>$player->pers['user'] )
	{
		$db->sql("UPDATE `clans` SET `glav`='".$p['user']."' WHERE `sign`='".$player->pers['sign']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		$db->sql("UPDATE `users` SET `state`='Экс Глава Смотрителей', `clan_state`='w01', `rank`='', `clan_prev`='0|0|0|0|0|0|0|0|0|0|' WHERE `uid`='".$player->pers['uid']."' and `sign`='watchers'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		$db->sql("UPDATE `users` SET `state`='Глава Смотрителей', `clan_state`='wg', `clan_prev`='1|1|1|1|1|1|1|1|1|1|' WHERE `uid`='".$p['uid']."' and `sign`='watchers'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		$status='w01';
		$player->pers['clan_prev'] = '0|0|0|0|0|0|0|0|0|0|';
		clans_log ($p['uid'], $player->pers['user'], 6, 'watchers');
		say_to_chat('a','Приветствуем нового <img src="http://'.IMG.'/signs/watch/wg.gif">Главу Смотрителей, персонажа <b>'.$p['user'].'</b>!',0,'','*',0); 
	}
}

# Раздевание соклана
if ( isset($http->get['sn_all']) and ($status=='wg' or $ppr[6]==true) and $http->get['clan_act']==3 )
{
	$pers_tmp = $player->pers;
	$player->pers = catch_user(intval($http->get["sn_all"]));
	if ( $player->pers['sign']==$pers_tmp['sign'] and $player->pers['sign']<>'none' )	
		remove_all_weapons();
	$player->pers = $pers_tmp;
	unset($pers_tmp);
}

if ( isset($http->post['mass']) and !empty($http->post['mass']) and $ppr[9]==true)
{
	say_to_chat('w',$http->post['mass'],0,'','*',0); 
	report("Сообщение успешно отправлено.");
}

if ($c==1 and ($status=='wg' or $ppr[8]==true) and isset($http->post['editclan']))
{
	$who = $db->sqla("SELECT `rank`,`uid`,`sign`,`forum_accesses` FROM `users` WHERE `uid`='".intval($http->post['editclan'])."' and `user`<>'".$player->pers['user']."' and `clan_state`<>'wg' and `sign`='watchers'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	if ($who['uid']==true)
	{
		$who['rank']='';
		for ( $i=1; $i<70; $i++ )
			$who['rank'].= (isset($http->post['p'.$i]) and $http->post['p'.$i]==1) ? '1|' : '0|';

		$who['clan_prev']='';
		for ( $i=1; $i<11; $i++ )
			$who['clan_prev'].= ($http->post['c'.$i]==1) ? '1|' : '0|';
		
		$who['forum_accesses']='1|';
		for ( $i=1; $i<11; $i++ )
			$who['forum_accesses'].= ($http->post['f'.$i]==1) ? $forum_a[$i].'|' : '0|';
		
		$db->sql("UPDATE `users` SET `rank`='".$who['rank']."', `clan_prev`='".$who['clan_prev']."', `forum_accesses` = '".$who['forum_accesses']."' WHERE `uid`='".$who['uid']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	}
	unset($who);
}


# Меняем должность, статус
if ( $http->_get('set_params') and ($status=='wg' or $ppr[2]) and $http->_get('clan_act')==5 )
{
	$bp = $db->sqla("SELECT `uid`, `clan_state` FROM `users` WHERE `user`='".$http->get['set_params']."' and `sign`='".$player->pers['sign']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	if ( $bp and $http->_post('state') )
	{
		$st = $http->_post('state');
		$img = (isset($http->post['newimg']) and in_array($http->post['newimg'], $imglist)) ? $http->post['newimg'] : $bp['clan_state'];
		$cltr = $http->_post('clan_tr') ? '1' : '0';
		if ( $bp['clan_state']!='wg' ) 
			$db->sql('UPDATE `users` SET `state`= "'.$st.'" , `clan_state`="'.$img.'", `clan_tr` = '.$cltr.' WHERE `uid`= '.$bp['uid'].' LIMIT 1;', __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	}
	unset($bp);
}

# Кладём в казну бабло LN
if ( $http->_post('money_ln') )
{
	report("Деньги удачно выданы.");
	$m = mtrunc(intval($http->post["money_ln"]));
	if ($m>$player->pers["money"]) $m = $player->pers["money"];
	set_vars("`money`=money-".$m,UID);
	$db->sql("UPDATE `clans` SET `money`=money+".$m." WHERE `sign`='".$player->pers["sign"]."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	clans_log(UID, $m, 3, $clan['sign']);
	$clan['money']+= $m;
}
# Снимаем бабло с клана LN
if ( $http->_post('takemoney_ln') and ($status=='wg' or $ppr[4]==1) )
{
	$clan = $db->sqla("SELECT * FROM `clans` WHERE `sign`='".$player->pers['sign']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	report("Деньги удачно взяты.");
	$m = mtrunc(intval($http->post['takemoney_ln']));
	if ($m>$clan['money']) $m = $clan['money'];
	set_vars("`money`=money+".$m,UID);
	$db->sql("UPDATE `clans` SET `money`=money-".$m." WHERE `sign`='".$player->pers['sign']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	clans_log(UID, $m, 4, $clan['sign']);
	$clan['money']-= $m;
}

# Кладём в казну бабло BR
if ( $http->_post('money_br') )
{
	report("Деньги удачно выданы.");
	$m = mtrunc(intval($http->post["money_br"]));
	if ($m>$player->pers["dmoney"]) $m = $player->pers["dmoney"];
	set_vars("`dmoney`=dmoney-".$m,UID);
	$db->sql("UPDATE `clans` SET `dmoney`=dmoney+".$m." WHERE `sign`='".$player->pers["sign"]."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	clans_log(UID, $m, 8, $clan['sign']);
	$clan['dmoney']+= $m;
}
# Снимаем бабло с клана BR
if ( $http->_post('takemoney_br') and $status=='wg' )
{
	$clan = $db->sqla("SELECT * FROM `clans` WHERE `sign`='".$player->pers['sign']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	report("Деньги удачно взяты.");
	$m = mtrunc(intval($http->post['takemoney_br']));
	if ($m>$clan['dmoney']) $m = $clan['dmoney'];
	set_vars("`dmoney`=dmoney+".$m,UID);
	$db->sql("UPDATE `clans` SET `dmoney`=dmoney-".$m." WHERE `sign`='".$player->pers['sign']."'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	clans_log(UID, $m, 9, $clan['sign']);
	$clan['dmoney']-= $m;
}
?>
<SCRIPT src="/js/watch_clan.js"></SCRIPT>
<DIV id="wclsrep" style="display:none;">
<?php
function report($txt)
{
	echo "<br><center class=but2><center class=puns>".$txt."</center></center><br>";
}

if ( $c==1 and $http->_get('clan')=='edit' and $http->get['clan_act']==2 and ($status=='wg' or $ppr[8]==1) )
{
	$who = $db->sqla("SELECT `uid`, `sign`, `rank`, `user`, `clan_prev`,`forum_accesses`  FROM `users` WHERE `uid`='".intval($http->get['who'])."' and `user`<>'".$player->pers['user']."' and `clan_state`<>'wg' ", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	if ( $who['sign']==$player->pers['sign'] and $player->pers['sign']=='watchers' ) 
	{
		$rk = explode('|', $who['rank']);
		$cp = explode('|', $who['clan_prev']);
		$fu = explode('|', $who['forum_accesses']);
	//	
		echo '<div class=return_win>
		<form method="POST" action=main.php?>';
		# Клановое
		$c1 = ($cp[0]==1) ? 1 : 0; // Принимать в клан
		$c2 = ($cp[1]==1) ? 1 : 0; // Выгонять из клана
		$c3 = ($cp[2]==1) ? 1 : 0; // Менять статус соклана
		$c4 = ($cp[3]==1) ? 1 : 0; // Менять должность соклана
		$c5 = ($cp[4]==1) ? 1 : 0; // Брать деньги из казны
		$c6 = ($cp[5]==1) ? 1 : 0; // Удалять вещи из казны
		$c7 = ($cp[6]==1) ? 1 : 0; // Снимать вещи с соклана, закрывать казну
		$c8 = ($cp[7]==1) ? 1 : 0; // Управлять возможностями клана
		$c9 = ($cp[8]==1) ? 1 : 0; // Устанавливать права сокланов
		$c10 = ($cp[9]==1) ? 1 : 0; // Использовать рупор
		
		# Возможности в персонаже
		$p1 = ($rk[0]==1) ? 1 : 0; // Молчанка
		$p2 = ($rk[1]==1) ? 1 : 0; // Форумка
		$p3 = ($rk[2]==1) ? 1 : 0; // Кара смотров
		$p4 = ($rk[3]==1) ? 1 : 0; // Блок инфы
		$p5 = ($rk[4]==1) ? 1 : 0; // Трюм
		$p6 = ($rk[5]==1) ? 1 : 0; // Блок
		$p7 = ($rk[6]==1) ? 1 : 0; // Пометка
		$p8 = ($rk[7]==1) ? 1 : 0; // Женить
		$p9 = ($rk[8]==1) ? 1 : 0; // Раздевать
		$p10 = ($rk[9]==1) ? 1 : 0; // Проверять на чистоту
		$p11 = ($rk[10]==1) ? 1 : 0; // Выгонять из клана
		$p12 = ($rk[11]==1) ? 1 : 0; // Вытаскивать из бага
		$p13 = ($rk[12]==1) ? 1 : 0; // Благославлять
		$p14 = ($rk[13]==1) ? 1 : 0; // Регистрировать мульта
		
		$p15 = ($rk[14]==1) ? 1 : 0; // Просмотр IP адресов
		$p16 = ($rk[15]==1) ? 1 : 0; // Очищать лог IP адресов
		$p17 = ($rk[16]==1) ? 1 : 0; // Просматривать лог переводов
		$p18 = ($rk[17]==1) ? 1 : 0; // Просматривать лог смен пароля
		$p19 = ($rk[18]==1) ? 1 : 0; // Просматривать лог заходов с одного компа
		$p20 = ($rk[19]==1) ? 1 : 0; // Просматривать лог пометок смотрителей
		$p21 = ($rk[20]==1) ? 1 : 0; // Просматривать лог проверок на чистоту
		$p22 = ($rk[21]==1) ? 1 : 0; // Просматривать лог клановой активности
		
		$p23 = ($rk[22]==1) ? 1 : 0; // Просматривать лог Правонарушения
		$p24 = ($rk[23]==1) ? 1 : 0; // Просматривать лог Проф. активность
		$p25 = ($rk[24]==1) ? 1 : 0; // Просматривать лог Рефералы
		$p26 = ($rk[25]==1) ? 1 : 0; // Просматривать лог Бои персонажа
		
		# Возможности в Обителя закона
		$w1 = ($rk[50]==1) ? 1 : 0; // Просмотр лога модерации
		$w2 = ($rk[51]==1) ? 1 : 0; // Управлять заявками на проверку персонажей
		$w3 = ($rk[52]==1) ? 1 : 0; // Управление кланами проекта
	//	$w4 = ($rk[53]==1) ? 1 : 0; // 
	//	$w5 = ($rk[54]==1) ? 1 : 0; // 
	//	$w6 = ($rk[55]==1) ? 1 : 0; // 
		
		
		# Настройки форума
		$f1 = ( in_array(32, $fu) ) ? 1 : 0;	// Закрыть / Открыть тему
		$f2 = ( in_array(64, $fu) ) ? 1 : 0;	// Переместить тему
		$f3 = ( in_array(128, $fu) ) ? 1 : 0;	// Удалить тему
		$f4 = ( in_array(256, $fu) ) ? 1 : 0;	// Изменить тему
		$f5 = ( in_array(512, $fu) ) ? 1 : 0;	// Закрепить / Открепить тему
		$f6 = ( in_array(2048, $fu) ) ? 1 : 0;	// Открыть / Скрыть сообщение
		$f7 = ( in_array(4096, $fu) ) ? 1 : 0;	// Удалить сообщение
		$f8 = ( in_array(8192, $fu) ) ? 1 : 0;	// Изменить сообщение
		$f9 = ( in_array(32768, $fu) ) ? 1 : 0;	// Создать форум
		$f10 = ( in_array(65536, $fu) ) ? 1 : 0;// Создать категорию
		
		
		
		
		echo "<center><b>".$who['user']."</b><table width=500 class=but>";
		
		
		echo '<tr><td><b>Клановые возможности</b></td><td></td></tr>';
		
		echo "<tr><td>Принимать в клан</td>"; if($c1==1) echo'<td><input type="checkbox" name=c1 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c1 value=1></td></tr>';
		echo "<tr><td>Выгонять из клана</td>"; if($c2==1) echo'<td><input type="checkbox" name=c2 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c2 value=1></td></tr>';
		echo "<tr><td>Смена статуса</td>"; if($c3==1) echo'<td><input type="checkbox" name=c3 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c3 value=1></td></tr>';
		echo "<tr><td>Смена должности</td>"; if($c4==1) echo'<td><input type="checkbox" name=c4 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c4 value=1></td></tr>';
		echo "<tr><td>Снимать деньги с клана</td>"; if($c5==1) echo'<td><input type="checkbox" name=c5 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c5 value=1></td></tr>';
		echo "<tr><td>Удалять вещи из казны</td>"; if($c6==1) echo'<td><input type="checkbox" name=c6 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c6 value=1></td></tr>';
		echo "<tr><td>Раздевать, закрывать казну</td>"; if($c7==1) echo'<td><input type="checkbox" name=c7 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c7 value=1></td></tr>';
		echo "<tr><td>Управлять возможностями</td>"; if($c8==1) echo'<td><input type="checkbox" name=c8 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c8 value=1></td></tr>';
		echo "<tr><td>Редактировать сокланов</td>"; if($c9==1) echo'<td><input type="checkbox" name=c9 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c9 value=1></td></tr>';
		echo "<tr><td>Использовать рупор</td>"; if($c10==1) echo'<td><input type="checkbox" name=c10 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=c10 value=1></td></tr>';
		
		echo '<tr><td><b>Полномочия Смотрителя</b></td><td></td></tr>';
		
		echo "<tr><td>Молчанка</td>"; if($p1==1) echo'<td><input type="checkbox" name=p1 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p1 value=1></td></tr>';
		echo "<tr><td>Форумная молчанка</td>"; if($p2==1) echo'<td><input type="checkbox" name=p2 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p2 value=1></td></tr>';
		echo "<tr><td>Кара</td>"; if($p3==1) echo'<td><input type="checkbox" name=p3 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p3 value=1></td></tr>';
		echo "<tr><td>Блокировать информацию</td>"; if($p4==1) echo'<td><input type="checkbox" name=p4 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p4 value=1></td></tr>';
		echo "<tr><td>Тюрьма</td>"; if($p5==1) echo'<td><input type="checkbox" name=p5 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p5 value=1></td></tr>';
		echo "<tr><td>Блок</td>"; if($p6==1) echo'<td><input type="checkbox" name=p6 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p6 value=1></td></tr>';
		echo "<tr><td>Пометка в ЛД</td>"; if($p7==1) echo'<td><input type="checkbox" name=p7 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p7 value=1></td></tr>';
		echo "<tr><td>Женить</td>"; if($p8==1) echo'<td><input type="checkbox" name=p8 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p8 value=1></td></tr>';
		echo "<tr><td>Раздевать</td>"; if($p9==1) echo'<td><input type="checkbox" name=p9 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p9 value=1></td></tr>';
		echo "<tr><td>Проверять на чистоту</td>"; if($p10==1) echo'<td><input type="checkbox" name=p10 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p10 value=1></td></tr>';
		echo "<tr><td>Выгонять из клана</td>"; if($p11==1) echo'<td><input type="checkbox" name=p11 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p11 value=1></td></tr>';
		echo "<tr><td>Вытаскивать из бага</td>"; if($p12==1) echo'<td><input type="checkbox" name=p12 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p12 value=1></td></tr>';
		echo "<tr><td>Благославлять</td>"; if($p13==1) echo'<td><input type="checkbox" name=p13 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p13 value=1></td></tr>';
		echo "<tr><td>Регистрировать мульта</td>"; if($p14==1) echo'<td><input type="checkbox" name=p14 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p14 value=1></td></tr>';

		echo '<tr><td><b>Доступ ЛД</b></td><td></td></tr>';
		
		echo "<tr><td>Просмотр IP</td>"; if($p15==1) echo'<td><input type="checkbox" name=p15 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p15 value=1></td></tr>';
		echo "<tr><td>Стрерать старые IP</td>"; if($p16==1) echo'<td><input type="checkbox" name=p16 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p16 value=1></td></tr>';
		echo "<tr><td>Финансовые операции</td>"; if($p17==1) echo'<td><input type="checkbox" name=p17 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p17 value=1></td></tr>';
		echo "<tr><td>Смены пароля</td>"; if($p18==1) echo'<td><input type="checkbox" name=p18 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p18 value=1></td></tr>';
		echo "<tr><td>Заходы с одного комп.</td>"; if($p19==1) echo'<td><input type="checkbox" name=p19 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p19 value=1></td></tr>';
		echo "<tr><td>Заметки Смотрителей</td>"; if($p20==1) echo'<td><input type="checkbox" name=p20 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p20 value=1></td></tr>';
		echo "<tr><td>Проверки персонажа</td>"; if($p21==1) echo'<td><input type="checkbox" name=p21 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p21 value=1></td></tr>';
		echo "<tr><td>Клановая активность</td>"; if($p22==1) echo'<td><input type="checkbox" name=p22 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p22 value=1></td></tr>';
		echo "<tr><td>Правонарушения</td>"; if($p23==1) echo'<td><input type="checkbox" name=p23 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p23 value=1></td></tr>';
		echo "<tr><td>Проф. активность</td>"; if($p24==1) echo'<td><input type="checkbox" name=p24 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p24 value=1></td></tr>';
		echo "<tr><td>Рефералы</td>"; if($p25==1) echo'<td><input type="checkbox" name=p25 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p25 value=1></td></tr>';
		echo "<tr><td>Бои персонажа</td>"; if($p26==1) echo'<td><input type="checkbox" name=p26 value=1 CHECKED></td></tr>'; else echo'<td><input type="checkbox" name=p26 value=1></td></tr>';
		
		echo '<tr><td><b>Доступ к обителю закона</b></td><td></td></tr>';
		
		echo '<tr><td>Просмотр лога модерации</td><td><input type="checkbox" name=p51 value=1 '.(($w1==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Управление заявками на проверку</td><td><input type="checkbox" name=p52 value=1 '.(($w2==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Управление кланами</td><td><input type="checkbox" name=p53 value=1 '.(($w3==1) ? 'CHECKED' : '').'></td></tr>'; 
		
		echo '<tr><td><b>Доступ к форуму</b></td><td></td></tr>';
		
		echo '<tr><td>Закрыть / Открыть тему</td><td><input type="checkbox" name=f1 value=1 '.(($f1==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Переместить тему</td><td><input type="checkbox" name=f2 value=1 '.(($f2==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Удалить тему</td><td><input type="checkbox" name=f3 value=1 '.(($f3==1) ? 'CHECKED' : '').'></td></tr>';
		echo '<tr><td>Изменить тему</td><td><input type="checkbox" name=f4 value=1 '.(($f4==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Закрепить / Открепить тему</td><td><input type="checkbox" name=f5 value=1 '.(($f5==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Открыть / Скрыть сообщение</td><td><input type="checkbox" name=f6 value=1 '.(($f6==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Удалить сообщение</td><td><input type="checkbox" name=f7 value=1 '.(($f7==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Изменить сообщение</td><td><input type="checkbox" name=f8 value=1 '.(($f8==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Создать форум</td><td><input type="checkbox" name=f9 value=1 '.(($f9==1) ? 'CHECKED' : '').'></td></tr>'; 
		echo '<tr><td>Создать категорию</td><td><input type="checkbox" name=f10 value=1 '.(($f10==1) ? 'CHECKED' : '').'></td></tr>';  
		
		echo "</table></center>";
		
		echo '<center><input type=hidden name=editclan value="'.$who['uid'].'"><input type="submit" value="Сохранить" class=login></center></form></div>';
	}
}
if ($http->_get('clan')=='glav') include('glavs_global.php');
elseif ($http->_get('clan')=='w') include('inv.php');
elseif ($http->_get('clan')=='wall') include('wall.php');
elseif ($http->_get('clan')=='doc') include('docs.php');
?>
</DIV>
<SCRIPT language="JavaScript">
<?php
echo "imglist = [".gen_img_list()."];\n";
echo "namelist = [".gen_names_list()."];\n";
echo "watch_user('".$player->pers['clan_prev']."', ".$player->pers['uid'].", '".$player->pers['clan_state']."', '".@$http->get['clan']."');\n";
echo "var watch = ['".$clan['glav']."', '".$clan['sait']."', '".$clan['money']."', '".$clan['dmoney']."'];\n";
echo $data;
if ( empty($http->get['clan']) ) w_sostav();
?>
view_watch_clans();
</SCRIPT>