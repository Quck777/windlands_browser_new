<?php
##############################
php
if ( defined('CLANS')==false ) {echo '<center>Ошибка.</center>'; exit;}
$TIME_NO = Array( ((24*3600)*1), ((24*3600)*30) ); // 30 дней заявка, 1 день время на подтверждение
$PRICE_A = Array( 0, 20000, 50000 );
$MONEY_C = Array( 10000, 1000000 );

####### Функции
function clan_zayavka($sign,$type,$money)
{
	GLOBAL $db,$clan,$PRICE_A,$MONEY_C;
	$ptr = $db->sqla('SELECT `sign`,`name`,`align` FROM `clans` WHERE `sign` = "'.$sign.'" LIMIT 1;');
	if ( $ptr )
	{
		if ( $clan['sign'] == $ptr['sign'] ) return report("За подобные опыты можно получить блок!");
		$souz = (int)$db->sqlr('SELECT `id` FROM `clans_souz` WHERE (`sign` = "'.$clan['sign'].'" and `sign2` = "'.$ptr['sign'].'") or (`sign` = "'.$ptr['sign'].'" and `sign2` = "'.$clan['sign'].'") LIMIT 1;');
		if ( !$souz )
		{
			$type = ($type == 'war') ? 2 : 1;
			$metp = ( $type == 2 ) ? 'объявляет войну клану' : 'подает заявку на альяна с кланом';
			$stus = ( $type == 2 ) ? 1 : 0;
			$coun = ( $type == 2 ) ? 300 : 100;
			if ( ($price = $PRICE_A[$type]) > $clan['money'] ) return report("Недостаточно деняг на счету клана!");
			## Все ок, снимаем бабло и закидываем в базу данные
			$clan['money']-= $price;
			if ( $type == 2 )
			{
				$cm = ($money > $MONEY_C[1]) ? $MONEY_C[1] : $money;
				$cm = ($cm < $MONEY_C[0]) ? $MONEY_C[0] : $cm;
			} else $cm = 0;
			
			$db->sql('UPDATE `clans` SET `money` = '.$clan['money'].' WHERE `sign` = "'.$clan['sign'].'";');
			$db->sql('INSERT INTO `clans_souz` (`sign`, `name`, `sign2`, `name2`, `date`, `type`, `status`, `gonorar`, `count`) 
				VALUES ("'.$clan['sign'].'", "'.$clan['name'].'", "'.$ptr['sign'].'", "'.$ptr['name'].'", "'.tme().'", "'.$type.'", "'.$stus.'", "'.$cm.'", "'.$coun.'");');
			
			$ms = 'Клан <img src="/images/signs/align/'.$clan['align'].'.gif"><img src="/images/signs/'.$clan['sign'].'.gif"><b>'.$clan['name'].'</b> '.$metp.' <img src="/images/signs/align/'.$ptr['align'].'.gif"><img src="/images/signs/'.$ptr['sign'].'.gif"><b>'.$ptr['name'].'</b>!';
			report($ms);
			say_to_chat('s',$ms,0,'','*',0);
			if ( $type == 2 ) clans_log (UID, $ptr['name'].' ('.$cm.' LN)', 10, $clan['sign']);
			else clans_log (UID, $ptr['name'], 11, $clan['sign']);
		} else report("Вы уже установили дипломатические отношение с этим кланом!");
	} else report("Нет такого клана!");
}

function del_zayavka($r)
{
	GLOBAL $db,$clan;
	if ( $r['type']==2 )
	{
		$sk = $db->sqlr('SELECT `align` FROM `clans` WHERE `sign` = "'.$r['sign'].'" LIMIT 1;');
		$sk2 = $db->sqlr('SELECT `align` FROM `clans` WHERE `sign` = "'.$r['sign2'].'" LIMIT 1;');
		$ms = '<img src="/images/signs/align/'.$sk.'.gif"><img src="/images/signs/'.$r['sign'].'.gif"><b>'.$r['name'].'</b> и <img src="/images/signs/align/'.$sk2.'.gif"><img src="/images/signs/'.$r['sign2'].'.gif"><b>'.$r['name2'].'</b> вынуждены прекратить вражду!';
		say_to_chat('s',$ms,0,'','*',0);
	}
	$db->sql('DELETE FROM `clans_souz` WHERE `id` = '.$r['id'].' LIMIT 1;');
}

function zayavka_manipulate($id, $type)
{
	GLOBAL $db,$clan;
	$res = $db->sqla('SELECT `id`, `sign`, `sign2`, `name` FROM `clans_souz` WHERE `id` = "'.$id.'" and `status` = 0 LIMIT 1;');
	// Закрываем доступ если работать над этой строчкой нельзя
	if ( !$res or ($res['sign']!=$clan['sign'] and $res['sign2']!=$clan['sign']) ) return report("Заявка не существует!");
	if ( $res['sign']==$clan['sign'] ) return report("Подтвердить должен второй участник альянса!");
	if ( $type == 'zok' )
	{
		$db->sql('UPDATE `clans_souz` SET `status` = 1, `date` = '.tme().' WHERE `id` = '.$res['id'].' LIMIT 1;');
		$sk = $db->sqlr('SELECT `align` FROM `clans` WHERE `sign` = "'.$res['sign'].'" LIMIT 1;');
		$ms = 'Между кланами <img src="/images/signs/align/'.$clan['align'].'.gif"><img src="/images/signs/'.$clan['sign'].'.gif"><b>'.$clan['name'].'</b> и <img src="/images/signs/align/'.$sk.'.gif"><img src="/images/signs/'.$res['sign'].'.gif"><b>'.$res['name'].'</b> заключен альянс!';
		report($ms);
		say_to_chat('s',$ms,0,'','*',0);
		clans_log (UID, $res['name'], 12, $clan['sign']);
	} else {
		$db->sql('DELETE FROM `clans_souz` WHERE `id` = '.$res['id'].' LIMIT 1;');
		report("Вы отклонили альянс!");
	}
}

function zayavka_delete($id)
{
	GLOBAL $db, $clan;
	$dep = $db->sqla('SELECT * FROM `clans_souz` WHERE `id` = "'.$id.'" and `status` = 1 LIMIT 1;');
	if ( !$dep ) return report("Не найдено!");
	if ( $dep['sign'] != $clan['sign'] and $dep['sign2'] != $clan['sign']) return report("Можно управлять только своим кланом!");
	## Обрабатываем
	if ( $dep['type']==1 ) {
		$m = 'расторгают альянс';
		clans_log (UID, $dep['name'], 14, $clan['sign']);
	} else {
		$m = 'прекращают войну';
		if ( $dep['gonorar'] > $clan['money'] ) return report("Недостаточно деняг на счету клана для выплаты контрибуции!");
		$clan['money']-= $dep['gonorar'];
		$sg = ($dep['sign'] == $clan['sign']) ? $dep['sign2'] : $dep['sign'];
		$db->sql('UPDATE `clans` SET `money` = '.$clan['money'].' WHERE `sign` = "'.$clan['sign'].'" LIMIT 1;');
		$db->sql('UPDATE `clans` SET `money` = money+'.$dep['gonorar'].' WHERE `sign` = "'.$sg.'" LIMIT 1;');
		clans_log (UID, (($dep['sign'] != $clan['sign']) ? $dep['name'] : $dep['name2']).' ('.$dep['gonorar'].' LN)', 13, $clan['sign']);
	}
	$sk = $db->sqlr('SELECT `align` FROM `clans` WHERE `sign` = "'.$dep['sign'].'" LIMIT 1;');
	$sk2 = $db->sqlr('SELECT `align` FROM `clans` WHERE `sign` = "'.$dep['sign2'].'" LIMIT 1;');
	$ms = '<img src="/images/signs/align/'.$sk.'.gif"><img src="/images/signs/'.$dep['sign'].'.gif"><b>'.$dep['name'].'</b> и <img src="/images/signs/align/'.$sk2.'.gif"><img src="/images/signs/'.$dep['sign2'].'.gif"><b>'.$dep['name2'].'</b> '.$m.'.';
	say_to_chat('s',$ms,0,'','*',0);
	report($ms);
	$db->sql('DELETE FROM `clans_souz` WHERE `id` = '.$dep['id'].' LIMIT 1;');
}

function war_attak($name)
{
	GLOBAL $db,$clan,$player;
	$vrag = $db->sqla('SELECT * FROM `users` WHERE `user` = "'.$name.'" and `online` = 1 LIMIT 1;');
	if ( !$vrag or $player->pers['uid']==$vrag['uid'] or $vrag['invisible']>tme() ) return report("Персонаж не найден!");
	if ( empty($vrag['sign']) or $vrag['sign'] == 'none' ) return report("Персонаж не состоит в клане!");
	$aliance = $db->sqla('SELECT `id`,`count` FROM `clans_souz` WHERE `status` = 1 and `type` = 2 and ((`sign` = "'.$clan['sign'].'" and `sign2` = "'.$vrag['sign'].'") or (`sign` = "'.$vrag['sign'].'" and `sign2` = "'.$clan['sign'].'")) LIMIT 1;');
	if ( !$aliance or !$aliance['count'] ) return report("Войны нет либо недостаточно ресурсов.!");
	if ( $player->pers['location'] != $vrag['location'] or $player->pers['x'] != $vrag['x'] or $player->pers['y'] != $vrag['y'] ) return report("Для атаки необходимо находится на одной локации с персонажем!");
	## Процесс атаки
	$fight = $db->sqla('SELECT * FROM `fights` WHERE `id`= '.$vrag['cfight'].' and `type` != "f";');
	if ( $fight and $fight["type"]<>'f' )
	{
		if ( $fight['closed'] ) return report("Персонаж находится в закрытом бою!");
		// невид не
		$nyou = ( $player->pers['invisible'] > tme() ) ? 'невидимка[??]' : ($player->pers['user'].'['.$player->pers['level'].']');
		
		$player->pers['curstate'] = 4;
		$player->pers['cfight'] = $fight['id'];
		$db->sql('UPDATE `fights` SET `players` = players+1 WHERE `id` = '.$fight['id'].';');
		
		## Если тактические бой.
		if($fight["bplace"])
		{
			$bplace = $db->sqla('SELECT `xy` FROM `battle_places` WHERE `id` = '.$fight['bplace']);
			$xf=11;
			$yf=floor(15/2)-1;
			while ($xf>0 and $xf<15)
			{
				$yf++;
				if ( $yf%$maxy==0 ) { $yf=0; $xf--; }
				$bcount = $db->sqlr('SELECT COUNT(uid) FROM `users` WHERE `cfight` = '.$fight['id'].' and `chp` > 0 and `xf` = "'.$xf.'" and `yf` = "'.$yf.'";');
				$bcount+= $db->sqlr('SELECT COUNT(id) FROM `bots_battle` WHERE `cfight` = '.$fight['id'].' and `chp` > 0 and `xf` = "'.$xf.'" and `yf` = "'.$yf.'";');
				if( !substr_count($bplace['xy'],'|'.$xf.'_'.$yf.'|') and $bcount==0 ) break;
			}
			$db->sql('UPDATE `users` SET `yf`='.$yf.', `xf`="'.$xf.'" WHERE `uid`="'.$player->pers["uid"].'";');
		}
		add_flog($nyou.' вмешивается в бой!', $vrag['cfight']);
	} else {
		$trl = Array(30, 100);
		## Поединок завершен.
		if($fight['type']=='f') $vrag = end_battle($vrag);
		$travma = $trl[rand(0, count($trl)-1)];
		$na='Война';
		$place = rand(0,5);
		$closed = rand(0,1);
		if ( $closed ) $na .= '[ЗАКРЫТОЕ]';
		## Я, он , нападение(тип) , травматичность , таймаут, оружие/нет, местность, тип боя, закрытый бой, специальный бой.
		begin_fight($player->pers['user'],$vrag['user'],$na,$travma,180,1,$place,0,$closed,10);
	}
	$db->sql('UPDATE `clans_souz` SET `count` = count-1 WHERE `id` = '.$aliance['id'].' LIMIT 1;');
	report("Нападение на врага прошло успешно!<script>location='/main.php?';</script>");
}

function souz_medic($name)
{
	GLOBAL $db, $clan,$player;
	$souznic = $db->sqla('SELECT `uid`,`sign`,`location`,`x`,`y` FROM `users` WHERE `user` = "'.$name.'" and `online` = 1 LIMIT 1;');
	if ( !$souznic or $player->pers['uid']==$souznic['uid'] ) return report("Персонаж не найден!");
	if ( empty($souznic['sign']) or $souznic['sign'] == 'none' ) return report("Персонаж не состоит в клане!");
	$aliance = $db->sqla('SELECT `id`,`count` FROM `clans_souz` WHERE `status` = 1 and `type` = 1 and ((`sign` = "'.$clan['sign'].'" and `sign2` = "'.$souznic['sign'].'") or (`sign` = "'.$souznic['sign'].'" and `sign2` = "'.$clan['sign'].'")) LIMIT 1;');
	if ( !$aliance or !$aliance['count'] ) return report("Альянс не заключен либо недостаточно ресурсов.!");
	if ( $player->pers['location'] != $souznic['location'] or $player->pers['x'] != $souznic['x'] or $player->pers['y'] != $souznic['y'] ) return report("Для применения лечения необходимо находится на одной локации с персонажем!");
	## Применяем 
	$count = rand(1,3);
	$db->sql('UPDATE `clans_souz` SET `count` = count-1 WHERE `id` = '.$aliance['id'].' LIMIT 1;');
	$db->sql('UPDATE `p_auras` SET `esttime` = 0 WHERE `uid` = '.$souznic['uid'].' and ((`special` > 2 and `special` < 6) or `special` = 50) and `esttime` > '.tme().' ORDER BY RAND() LIMIT '.$count.';');
	report("Излечение союзника успешно применено!");
}

function noclan($pass)
{
	GLOBAL $db, $player;
	if ( md5($pass) != $player->pers['pass'] ) return report("Неверный пароль от персонажа!");
	if ( $player->pers['clan_state']=='a' ) return report("Глава клана не может покупить клан!");
	$bd = $db->sqlr('SELECT COUNT(id) FROM `clans_souz` WHERE `type` = 2 and (`sign` = "'.$player->pers['sign'].'" or `sign2` = "'.$player->pers['sign'].'");');
	if ( $bd ) return report("Ваш клан участвует в боевых действиях, вы не можете покинуть клан самостоятельно!");
	$db->sql ("UPDATE `users` SET `sign`='none', `state`='', `rank`='', `clan_state`='', `clan_prev`='', `curstate` = 0 WHERE `uid` = '".$player->pers['uid']."';");
	clans_log($player->pers['uid'], $player->pers['user'], 2, $player->pers['sign']);
	return report("Вы покинули клан!<script>location='/main.php?';</script>");
}


####### Обработка пользовательский функций
if ( $status=='a' ) // Действия от имени главы
{
	// Подача заявки
	if ( $http->_post('clan_zayavka') and $http->_post('clan_zayavka') != 'none' ) clan_zayavka( $http->_post('clan_zayavka'), $http->_post('type'), round((int)$http->_post('cmoney')) );
	// Подтверждаем или отклоняем заявку на альянс
	if ( $http->_get('act') and ( $http->_get('act')=='zok' or $http->_get('act')=='zno' ) ) zayavka_manipulate($http->_get('id'), $http->_get('act'));
	// Расторгаем альянс или выплачиваем контрибуцию
	if ( $http->_get('act') and $http->_get('act')=='del' ) zayavka_delete($http->_get('id'));
}
// Нападаем на врага клана
if ( $http->_post('napad_pers') ) war_attak( $http->_post('napad_pers') );
// Лечим союзника
if ( $http->_post('med_pers') ) souz_medic( $http->_post('med_pers') );
// Покинуть клан
if ( $http->_post('noclan_pass') and $status!='a' ) noclan( $http->_post('noclan_pass') );

####### Начинаем обработку клиентской части
$e_res = ''; $clan_obj = Array();
$res = $db->sql('SELECT * FROM `clans_souz` WHERE `sign` = "'.$player->pers['sign'].'" or `sign2` = "'.$player->pers['sign'].'" ORDER BY `date`;');
while ( $r = mysql_fetch_assoc($res) )
{
	$conets = ($r['date']+$TIME_NO[$r['status']]) - tme();
	if ( $conets < 0 )
	{
		del_zayavka($r);
		continue;
	}
	$clan_obj[$r['sign']] = true; $clan_obj[$r['sign2']] = true;
	$e_res.= (empty($e_res) ? '' : ',').'["'.$r['id'].'","'.$r['sign'].'","'.$r['name'].'","'.$r['sign2'].'","'.$r['name2'].'","'.tp($conets).'",'.$r['type'].','.$r['status'].','.$r['gonorar'].','.$r['count'].']';
}

$c_list = '';
if ( $status=='a' )
{
	$clans = $db->sql('SELECT `sign`, `name` FROM `clans` WHERE `sign` != "watchers" and `sign` != "'.$clan['sign'].'" ORDER BY `name` ASC;');
	while ( $c = mysql_fetch_row($clans) ) { if ( $clan_obj[$c[0]] ) continue; $c_list.= (empty($c_list) ? '' : ',').'["'.$c[0].'","'.$c[1].'"]'; }
}

//echo '<center>Тестовый режим.</center>';
?>
<script>
var data = [<?=$e_res;?>];
var clan_list = [<?=$c_list;?>];
var tarif = [<?php echo $PRICE_A[1].','.$PRICE_A[2].','.$MONEY_C[0].','.$MONEY_C[1]; ?>];
</script>
