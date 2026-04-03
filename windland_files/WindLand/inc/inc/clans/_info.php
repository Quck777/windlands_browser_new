<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
if ( defined('CLANS')==false ) {echo '<center>Ошибка.</center>'; exit;}

if (isset($clan)) $c=1;
$status = $player->pers['clan_state'];

if ($player->pers['user']==$clan['glav'] and $status!='a') 
{
	$player->pers['clan_state'] = "a";
	$db->sql("UPDATE `users` SET `clan_state`='a' WHERE `uid`=".$player->pers['uid']."");
	$status='a';
}


# Смена сайта
if ( isset($http->post['site']) and !empty($http->post['site']) )
{
	report("Сайт удачно изменён.");
	$site = str_replace("http://","",$http->post['site']);
	$site = str_replace("'","",$site);
	$site = str_replace("/","",$site);
	$site = str_replace("\\","",$site);
	$db->sql("UPDATE `clans` SET `sait`='".$site."' WHERE `sign`='".$player->pers['sign']."'");
	$clan['sait'] = $site;
	clans_log (UID, $site, 7, $clan['sign']);
}
# Пересчет казны
if ( $status=='a' and isset($http->get['tzero']) )
{
	$tr = $db->sqlr("SELECT COUNT(*) FROM `wp` WHERE `clan_sign`='".$player->pers["sign"]."'");
	$db->sql("UPDATE `clans` SET `treasury`=".$tr." WHERE `sign`='".$player->pers["sign"]."'");
}
# Пъём
if (isset($http->get['well']))
{
	$clan = $db->sqla("SELECT * FROM `clans` WHERE `sign`='".$player->pers['sign']."'");
	if($clan['time_well']<=tme())
	{
		$player->pers['chp'] = $player->pers['chp']+$clan['well'];
		if ($player->pers['chp']>$player->pers["hp"]) $player->pers['chp'] = $player->pers['hp'];
		$player->pers['cma'] = $player->pers['cma']+$clan['well'];
		if ($player->pers['cma']>$player->pers['ma']) $player->pers['cma'] = $player->pers['ma'];
		set_vars("chp=".$player->pers['chp'].",cma=".$player->pers['cma'],UID);
		$db->sql("UPDATE `clans` SET `time_well`=".(tme()+600)." WHERE `sign`='".$player->pers['sign']."'");
	}
}
# Распределение параметров клана
if (isset($http->get['up_um']) and $status=='a')
{
	$clan = $db->sqla ("SELECT * FROM `clans` WHERE `sign`='".$player->pers['sign']."'");
	if ($clan['freestats']>0)
	{
		if ($http->get['up_um']==1)
			$db->sql("UPDATE `clans` SET `maxtreasury`=maxtreasury+15, `freestats`=freestats-1 WHERE `sign`='".$player->pers['sign']."'");
		if ($http->get['up_um']==2)
			$db->sql("UPDATE `clans` SET `maxpl`=maxpl+3, `freestats`=freestats-1 WHERE `sign`='".$player->pers['sign']."'");
		if ($http->get['up_um']==3)
			$db->sql("UPDATE `clans` SET `well`=well+10, `freestats`=freestats-1 WHERE `sign`='".$player->pers['sign']."'");
	}
}
# Обнуляем клан
if (isset($http->get['zero']) and $status=='a')
{
	$clan = $db->sqla ("SELECT * FROM `clans` WHERE `sign`='".$player->pers['sign']."'");
	if ($clan['money']>=500)
	$db->sql("UPDATE `clans` SET `maxtreasury`=0, `maxpl`=0, `well`=0, `freestats`=level, `money`=money-500 WHERE `sign`='".$player->pers['sign']."'");
}
# Выганяем из клана
if($c==1 and ($status=='a' or $status=='b') and isset($http->get['go_out']))
{
	$player->pers['money']-=200;
	$go_out=$db->sqla("SELECT `uid`, `user` FROM `users` WHERE `smuser`=LOWER('".$http->get['go_out']."') and `sign`='".$player->pers['sign']."' and `clan_state`<>'a'");
	if ($go_out['uid']==true)
	{
		$db->sql ("UPDATE `users` SET `sign`='none', `state`='' , `rank`='', `clan_state`='', `clan_prev`='' WHERE `uid`='".$go_out['uid']."'");
		$db->sql ("UPDATE `users` SET `money`='".$player->pers["money"]."' WHERE `uid`='".$player->pers['uid']."'");
		report("Персонаж <b>".$go_out['user']."</b> выгнан из клана! С вашего счёта списано 200 LN");
		clans_log ($go_out['uid'], $player->pers['user'], 2, $clan['sign']);
	}
	else report("Нет такого персонажа.");
}
# Раздевание соклана
if ( isset($http->get['sn_all']) and ($status=='a' or $status=='b' or $status=='d') )
{
	$pers_tmp = $player->pers;
	$player->pers = catch_user(intval($http->get["sn_all"]));
	if ( $player->pers['sign']==$pers_tmp['sign'] and $player->pers['sign']<>'none' )	
		remove_all_weapons();
	$player->pers = $pers_tmp;
	unset($pers_tmp);
}
# Принимаем в клан
if ($c==1 and ($status=='a' or $status=='b' or $status=='e') and $http->post['go_in'] and $allpers<($clan['maxpl']+10))
{
	$go_in = $db->sqla("SELECT `sign`, `uid`, `user` FROM `users` WHERE `user`='".$http->post['go_in']."' and `level`>4");
	if ($go_in['uid']==true and $go_in['sign']=='none') 
	{
		$verif = $db->sqlr('SELECT count(*) FROM `watch_verification` WHERE `uid`='.$go_in['uid'].' and `date`>'.(tme()-432000).'');
		if ($verif==true)
		{
			$player->pers['money']-=200;
			$db->sql("UPDATE `users` SET `sign`='".$clan['sign']."', `state`='', `rank`='', `clan_prev`='', `clan_state`='f' WHERE `uid`='".$go_in['uid']."'");
			$db->sql("UPDATE `users` SET `money`='".$player->pers['money']."' WHERE `uid`='".$player->pers['uid']."'");
			report("Персонаж <b>".$go_in['user']."</b> принят в клан! С вашего счёта списано 200 LN");
			clans_log ($go_in['uid'], $player->pers['user'], 1, $clan['sign']);
		} else report("Персонаж <b>".$go_in['user']."</b> не прошел проверку на чистоту, либо проходил ее слишком давно.");
	}
	else report("Нет такого персонажа или персонаж уже находится в клане.");
	unset($go_in);
}
# Меняем должность, статус
if ( $http->get['set_params'] and ($status=='a' or $status=='b') )
{
	$bp = $db->sqla("SELECT `uid`, `clan_state` FROM `users` WHERE `user`='".$http->get['set_params']."' and `sign`='".$player->pers['sign']."'");
	if ( ($bp['clan_state']=='a' and $status=='a') or $bp['clan_state']!='a' )
	{
		if ( $bp['uid'] and $http->_post('state'))
		{
			$state = $http->_post('state');
			$http->post['clan_tr'] = intval($http->post['clan_tr']);
			if ($bp['clan_state']=='a') $http->post['clan_tr']=1;
			if ( $bp['clan_state']=='a' or $http->post['clan_state']==false or $http->post['clan_state']=='a')
				$a = $db->sql('UPDATE `users` SET `state` = "'.$http->post['state'].'", `clan_tr` = '.$http->post['clan_tr'].' WHERE `uid` = '.$bp['uid'].';');
			else
				$a = $db->sql("UPDATE `users` SET `state`='".$http->post['state']."', `clan_state`='".$http->post['clan_state']."', `clan_tr`='".$http->post['clan_tr']."' WHERE `uid`='".$bp['uid']."'");
		//	if ( $a ) report("Настройки сохранены.");
		}
	}
}
# Передача главенства
if ($status=='a' and isset($http->post['do_glav'])) 
{
	$p = $db->sqla("SELECT `uid`, `sign`, `user` FROM `users` WHERE `smuser`=LOWER('".$http->post['do_glav']."')");
	if ( $p['uid']==true and $p['sign']==$player->pers['sign'] and $p['user']<>$player->pers['user'] )
	{
		$db->sql("UPDATE `clans` SET `glav`='".$p['user']."' WHERE `sign`='".$player->pers['sign']."'");
		$db->sql("UPDATE `users` SET `state`='', `clan_state`='f', `clan_prev`='0|0|0|0|0|0|0|0|0|' WHERE `uid`='".$player->pers['uid']."'");
		$db->sql("UPDATE `users` SET `state`='', `clan_state`='a', `clan_prev`='1|1|1|1|1|1|1|1|1|' WHERE `uid`='".$p['uid']."'");
		$status='f';
		clans_log ($p['uid'], $player->pers['user'], 6, $clan['sign']);
		report("Вы передали полномочия главы.");
	}
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
if ( $http->_post('takemoney_ln') and ($status=='a' or $status=='d') )
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
if ( $http->_post('takemoney_br') and $status=='a' )
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
<SCRIPT src="/js/clan_v2.js"></SCRIPT>
<DIV id="clsrep" style="display:none;">
<?php
function report($txt)
{
	echo "<br><center class=but2><center class=puns>".$txt."</center></center><br>";
}
	
	$uplvltxt = '';
	$lvlup = 0;
	$online = 0;
	$maxrank = 0;
	$dye = $clan['dmoney'];
	$money = $clan['money'];
	$avglvl = 0;
	$allpers = 0;

if ($clan['name']!=false and empty($http->get['clan']) and $c==1) 
{
/*
	if (($clan['level']%6)==0) $uplvltxt = "<i>Для получения <b>".($clan['level']+1)."</b> уровня клан должен иметь не менее ".floor(($clan['level']/2+1)*4)." членов.</i>";
	if (($clan['level']%6)==1) $uplvltxt = "<i>Для получения <b>".($clan['level']+1)."</b> уровня клан должен иметь не менее ".floor(($clan['level']/2+1)*3)." членов онлайн.</i>";
	if (($clan['level']%6)==2) $uplvltxt = "<i>Для получения <b>".($clan['level']+1)."</b> уровня клан должен иметь наиболее сильного персонажа с ранком не менее ".floor(($clan['level']/3+1)*200).".</i>";	
	if (($clan['level']%6)==3) $uplvltxt = "<i>Для получения <b>".($clan['level']+1)."</b> уровня клан должен иметь не менее ".floor(($clan['level']+1)*5000)." LN в казне</i>";	
	if (($clan['level']%6)==4) $uplvltxt = "<i>Для получения <b>".($clan['level']+1)."</b> уровня клан должен иметь не менее ".floor(($clan['level']+1)*50)." Бр в казне</i>";		
	if (($clan['level']%6)==5) $uplvltxt = "<i>Для получения <b>".($clan['level']+1)."</b> уровня средний уровень клана должен быть не менее ".floor(($clan['level']+8)).".</i>";	
*/
	$caslevel = $db->sqla("SELECT * FROM `exp_clan` WHERE `level`=".($clan["level"]+1));
	$clansexp = $caslevel['exp'] - $clan['exp'];
	$uplvltxt = 'До следующего уровня необходимо '.$clansexp.' опыта.';
	
	$sostav = $db->sql("SELECT uid, user, online, location, state, level, rank_i, clan_state, lastom, silence, clan_tr FROM `users` WHERE `sign`='".$clan['sign']."' ORDER BY `clan_state` ASC;");
	while ( $prs = mysql_fetch_assoc($sostav) ) 
	{
		if ($prs["uid"] == 7) $prs = j_pers($prs);
		$online += $prs['online'];
		if ($prs['rank_i']>$maxrank) $maxrank=$prs['rank_i'];
		$avglvl += $prs['level'];
		$allpers ++;
		if ($prs['online']==1)
		{
			$loc = $db->sqla_id("SELECT `name` FROM `locations` WHERE `id`='".$prs['location']."';");
			$loc = $loc[0];
		} else $loc = $loc = time_echo(tme()-$prs['lastom']);
		if ( isset($stv) ) $stv .= ",['".$prs['user']."', ".$prs['level'].", ".$prs['online'].", '".$prs['clan_state']."', '".$prs['state']."', ".$prs['uid'].", ".$prs['clan_tr'].", '".$loc."']";
		else $stv = "['".$prs['user']."', ".$prs['level'].", ".$prs['online'].", '".$prs['clan_state']."', '".$prs['state']."', ".$prs['uid'].", ".$prs['clan_tr'].", '".$loc."']";
	}
/*	
	if ((($clan['level']%6)==0 and $allpers>=floor(($clan['level']/2+1)*4)) or
		(($clan['level']%6)==1 and $online>=floor(($clan['level']/2+1)*3)) or
		(($clan['level']%6)==2 and $maxrank>=floor(($clan['level']/3+1)*200))or 
		(($clan['level']%6)==3 and $money>=floor(($clan['level']+1)*5000)) or
		(($clan['level']%6)==4 and $dye>=floor(($clan['level']+1)*50)) or 
		(($clan['level']%6)==5 and $avglvl>=floor(($clan['level']+8)))	)
	{
		if (isset($http->get['lvlup']) and $http->get['lvlup']==1)
		{
			$db->sql("UPDATE `clans` SET `level`=level+1, `freestats`=freestats+1 WHERE `sign`='".$player->pers['sign']."'");
			$clan['level']++;
			set_vars("refr=1",UID);
		} else $lvlup = 1;
	}
*/
	$data = "var data = [".$stv."];\n";
	unset($prs);
}
elseif ($http->get['clan']=='glav') include('glavs_v2.php');
elseif ($http->get['clan']=='w') include('inv.php');
elseif ($http->get['clan']=='wall') include('wall.php');
elseif ($http->get['clan']=='doc') include('docs.php');

$page = $http->get['clan'] ? @$http->_get('clan') : 0;

?>
</DIV>
<SCRIPT language="JavaScript">
<?php 
echo "var pinfo = ['".$player->pers['user']."', ".$player->pers['level'].", '".$player->pers['sign']."', 'none', '".$player->pers['clan_state']."', ".$player->pers['uid'].", '".$page."'];\n";
echo "var iclan = ['".$clan['name']."', ".$clan['level'].", '".$clan['glav']."', '".$clan['sait']."', ".$clan['money'].", ".$clan['dmoney'].", ".$clan['freestats'].", ".$clan['treasury'].", ".($clan['maxtreasury']+30).", ".($clan['maxpl']+10).", ".(($clan['time_well']>tme())?1:0).", ".($clan['well']+10).", '".$uplvltxt."', '".$allpers."', '".$online."', '".$maxrank."', '".@floor($avglvl/$allpers)."', '".$lvlup."'];\n";
echo $data;
?>
view_clans();
</SCRIPT>