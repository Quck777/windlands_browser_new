<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
######################################################################################3
if ( isset($_GET['bot_agres']) and $player->jKey(1) )
{
	$bss = $db->sql('SELECT * FROM `users` WHERE `online`=1 and `location`="out" and `cfight`=0 and `apps_id`=0 ;');
	while ($bs = mysql_fetch_assoc($bss) )
	{
		$SPECIAL_pers = $bs;
		$SPECIAL_count = 6;
		include(ROOT.'/gameplay/bots/attack.php');
	}
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, приказал своему войску, атаковать жителей мира!</b>',0,'','*',0); 
	$actMess = 'Монстры атаковали персонажей!';
}

if ( isset($_GET['go_home']) and $player->jKey(1) )
{
	$db->sql('UPDATE `users` SET `location`="arena", `x`="-1", `y`="-3", `cfight`=0, `curstate`=0;');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса призвал всех жителей в город!</b>',0,'','*',0); 
	$actMess = 'ТП в город!';
}

if ( isset($_GET['go_nahui']) and $player->jKey(1) )
{
	$usr = $db->sql('SELECT `uid` FROM `users`;');
	while ($us = mysql_fetch_assoc($usr) )
	{
		$cord = $db->sqla('SELECT `x`,`y` FROM `nature` WHERE `name`<>"Метрополис" ORDER BY RAND() LIMIT 1;');
		$db->sql('UPDATE `users` SET `location`="out", `x`="'.$cord['x'].'", `y`="'.$cord['y'].'", `cfight`=0, `curstate`=0 WHERE `uid`='.$us['uid'].';');
		unset($cord);
	}
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, обозлившись на нерадивых жителей, разбросал всех по просторам мира!</b>',0,'','*',0);
	$actMess = 'Разбросано!';
}

if ( isset($_GET['go_inviz']) and $player->jKey(1) )
{
	$tm = (time()+(60*30));
	$db->sql('UPDATE `users` SET `invisible`="'.$tm.'" WHERE `invisible`<'.time().';');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, оскорбившись тщеславием жителей, сделал их безликими!</b>',0,'','*',0);
	$actMess = 'Всем невид!';
}

if ( isset($_GET['go_noinviz']) and $player->jKey(1) )
{
	$db->sql('UPDATE `users` SET `invisible`=0;');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, обнаружив, что его жители скрываются от его взора, снял вуаль невидимости!</b>',0,'','*',0); 
	$actMess = 'Снят всем невид!';
}

if ( isset($_GET['go_molch']) and $player->jKey(1) )
{
	$tm = (time()+(60*30));
	$db->sql('UPDATE `users` SET `silence`="'.$tm.'" WHERE `silence`<'.time().';');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, узнав, что злые языки его жителей оскорбляют его доброе имя, лишил всех дара речи!</b>',0,'','*',0); 
	$actMess = 'Всем молчанка!';
}

if ( isset($_GET['go_nomolch']) and $player->jKey(1) )
{
	$db->sql('UPDATE `users` SET `silence`=0;');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса смилостивился над немыми бедолагами и вернул им способность разговаривать!</b>',0,'','*',0); 
	$actMess = 'Снято всем молчанка!';
}

if ( isset($_GET['go_maxhell']) and $player->jKey(1) )
{
	$db->sql('UPDATE `users` SET `chp`=hp, `cma`=ma ;');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, пребывая в хорошем настроении, наделил всех нуждающихся жизненной и магической энергией!</b>',0,'','*',0); 
	$actMess = 'Всем отрегенено ХП и МП!';
}

if ( isset($_GET['go_nomaxhell']) and $player->jKey(1) )
{
	$db->sql('UPDATE `users` SET `chp`=0, `cma`=0 ;');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, раздражившись недокучливыми жителями, отнял у них жизненную и магическую энергию!</b>',0,'','*',0); 
	$actMess = 'Обнул ХП и МА!';
}

if ( isset($_GET['go_money']) and $player->jKey(1) )
{
	$usr = $db->sql('SELECT `user`,`level` FROM `users` WHERE `online` = 1;');
	$globln = 0;
	while ($us = mysql_fetch_assoc($usr) )
	{
		$mon = rand($us['level'],($us['level']+100));
		$db->sql('UPDATE `users` SET `money`=money+'.$mon.' WHERE `user`="'.$us['user'].'";');
		say_to_chat('s','Верховный маг Метрополиса подарил Вам <b>'.$mon.'</b> LN.',1,$us['user'],'*',0); 
		$globln = $globln+$mon;
		unset($mon);
	}
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, пребывая в хорошем настроении, одарил жителей мира золотыми монетами!</b>',0,'','*',0);
	$actMess = '<center class=user>Всего роздано LN: '.$globln.'</center>';
}

if ( isset($_GET['go_notravm']) and $player->jKey(1) )
{
	$db->sql('UPDATE `p_auras` SET `esttime`=0 WHERE `special`>2 and `special`<6 and `esttime`>'.tme().' ;');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, исцелил всех жителей, от мучивших их травм и болезней!</b>',0,'','*',0);
	$actMess = 'Вылечены травмы, кроме боевых!';
}

if ( isset($_GET['go_blago']) and $player->jKey(1) )
{
	$minut = 30;
	$usr = $db->sql('SELECT * FROM `users` WHERE `online`=1;');
	while ($us = mysql_fetch_assoc($usr) )
	{
		aura_blago_mag($us,$minut,1);
	}
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, благословил жителей мира!</b>',0,'','*',0);
	$actMess = 'Всем благословление!';
}

if ( isset($_GET['go_prokl']) and $player->jKey(1) )
{
	$minut = 30;
	$usr = $db->sql('SELECT * FROM `users` WHERE `online`=1;');
	while ($us = mysql_fetch_assoc($usr) )
	{
		aura_blago_mag($us,$minut,0);
	}
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, проклял жителей мира!</b>',0,'','*',0);
	$actMess = 'Всем проклятие!';
}

if ( isset($_GET['go_noaurabp']) and $player->jKey(1) )
{
	$db->sql('UPDATE `p_auras` SET `esttime`='.time().' WHERE `special`=77 and `esttime`>'.time().' ;');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса, отнесся к жителям равнодушно, сняв наложенные ранее заклинания!</b>',0,'','*',0);
	$actMess = 'Все снять закленания!';
}

if ( isset($_GET['notravn']) and $player->jKey(1) )
{
	$db->sql('UPDATE `p_auras` SET `esttime`='.tme().' WHERE (special>2 and special<6) or special=50 and `esttime`>'.time().' ;');
	say_to_chat('a','<b class=red>Верховный маг Метрополиса исцелил всех жителей мира!</b>',0,'','*',0);
	$actMess = 'Всех исцелил!';
}

if ( isset($_GET['go_tornados']) and $player->jKey(1) )
{
	$usr = $db->sql('SELECT `user` FROM `users` WHERE `online`=1;');
	while ($us = mysql_fetch_assoc($usr) )
	{
		begin_fight ("bot=2600|",$us['user'],"Нападение существ",100,360,1,1);
	}
	say_to_chat('s','<b class=red>Торнадос напал на Вас!</b>',0,'','*',0);
	$actMess = 'Натравили босса!';
}

if ( $http->_post('notravm') and $player->jKey(1) )
{
	$p = (int)$db->sqlr("SELECT `uid` FROM `users` WHERE `user`='".$http->_post('notravm')."'");
	if ($p)
		$db->sql("UPDATE p_auras SET esttime=0 WHERE uid=".$p." and special>2 and special<6 and esttime>".tme().";");
}


#######################################################################################################

	if (!$priv["eusers"]) exit;
	if (@$_GET["weather"])
	{
		if($db->sql("UPDATE world SET weather=".intval($_GET["weather"]).",weatherchange=0")) echo "Погода установлена";
	}
	if (@$_GET["zeroingall"])
	{
		if($db->sql("UPDATE users SET zeroing=zeroing+1")) echo "Обнуления успешно выданы";
	}
	if (@$_GET["szeroingall"])
	{
		if($db->sql("UPDATE users SET skill_zeroing=skill_zeroing+1")) echo "Обнуления успешно выданы";
	}
	if (@$_GET["zeroall"])
	{
		if($db->sql("UPDATE users SET action=-10")) echo "Все успешно обнулены.";
	}
	if (@$_GET["fullzeroall"])
	{
		if($db->sql("UPDATE users SET action=-11")) echo "Все успешно полностью обнулены.";
	}
	if (@$_GET["zero"])
	{
		if($db->sql("UPDATE users SET action=-10 WHERE user='".$_POST["zeronick"]."'")) echo "Персонаж успешно обнулен.";
	}
	if (@$_GET["fullzero"])
	{
		if($db->sql("UPDATE users SET action=-11 WHERE user='".$_POST["fullzeronick"]."'")) echo "Персонаж успешно полностью обнулен.";
	}
	if (@$_POST["nick"] or @$_GET["myself_edit"])
	{
		if(@$_GET["myself_edit"])
			$_POST["nick"] = $player->pers["user"];
		$p = $db->sqla("SELECT * FROM users WHERE user='".$_POST["nick"]."'");
		echo "<p class=inv>";
		echo "<form action=main.php?edit=".$p["uid"]." method=post><input class=login type=submit value='Сохранить'>";
		echo "<ul class=inv>";
		foreach ($p as $key=>$value)
		{
			if (is_string($key) and $key<>'uid'and $key<>'pass'and $key<>'second_pass'and $key<>'flash_pass'and $key<>'priveleged' and $key<>'user' and $key<>'smuser' and $key<>'dmoney' and $key<>'money')
			{
				if($key==name_of_skill($key) or !name_of_skill($key))
					echo "<li>".$key." : <input class=laar type=text value='".$value."' name='".$key."'></li>";
				else
					echo "<li><b>".name_of_skill($key)."</b> (".$key."): <input class=laar type=text value='".$value."' name='".$key."'></li>";
			}
		}
		echo "</ul>";
		echo "<input class=login type=submit value='Сохранить'></form>";
		echo "</p>";
	}
	if (@$_GET["edit"] and $priv["eusers"]>1)
	{
			$q = '';
			foreach($_POST as $key=>$value)
			{
				if ($key<>'uid'and $key<>'pass'and $key<>'second_pass'and $key<>'flash_pass'and $key<>'priveleged' and $key<>'user' and $key<>'smuser' and $key<>'dmoney' and $key<>'money')
				{
				$key = str_replace (" ","",$key);
				$value = str_replace("'","",$value);
				$q .= "`".$key."`='".$value."',";
				}
			}
			$q = substr($q,0,strlen($q)-1);
			if ($db->sql("UPDATE users SET ".$q." WHERE uid=".intval($_GET["edit"]).""))
			echo $_POST["user"]." успешно изменён!";
	}
	
	
function aura_blago_mag($persto,$duration,$tp=1)
{
	$wtkb = ($persto['level']<5) ? 100 : floor($persto['kb']*0.5);
	$wtudmin = ($persto['level']<5) ? 20 : floor($persto['udmin']*0.3);
	$wtudmax = ($persto['level']<5) ? 25 : floor($persto['udmax']*0.3);
	$wts1 = ($persto['level']<5) ? 5 : floor($persto['s1']*0.5);
	$wts2 = ($persto['level']<5) ? 5 : floor($persto['s2']*0.5);
	$wts3 = ($persto['level']<5) ? 5 : floor($persto['s3']*0.5);
	$wts4 = ($persto['level']<5) ? 5 : floor($persto['s4']*0.5);
	$wts5 = ($persto['level']<5) ? 5 : floor($persto['s5']*0.5);
	$wts6 = ($persto['level']<5) ? 5 : floor($persto['s6']*0.5);
	$mf1 = ($persto['level']<5) ? 50 : floor($persto['mf1']*0.5);
	$mf2 = ($persto['level']<5) ? 50 : floor($persto['mf2']*0.5);
	$mf3 = ($persto['level']<5) ? 50 : floor($persto['mf3']*0.5);
	$mf4 = ($persto['level']<5) ? 50 : floor($persto['mf4']*0.5);
	
	if ($tp==1)
	{
		$persto['kb']+=$wtkb;
		$persto['udmin']+=$wtudmin;
		$persto['udmax']+=$wtudmax;
		$persto['s1']+=$wts1;
		$persto['s2']+=$wts2;
		$persto['s3']+=$wts3;
		$persto['s4']+=$wts4;
		$persto['s5']+=$wts5;
		$persto['s6']+=$wts6;
		$persto['mf1']+=$mf1;
		$persto['mf2']+=$mf2;
		$persto['mf3']+=$mf3;
		$persto['mf4']+=$mf4;
	} else {
		$persto['kb']-=$wtkb;
		$persto['udmin']-=$wtudmin;
		$persto['udmax']-=$wtudmax;
		$persto['s1']-=$wts1;
		$persto['s2']-=$wts2;
		$persto['s3']-=$wts3;
		$persto['s4']-=$wts4;
		$persto['s5']-=$wts5;
		$persto['s6']-=$wts6;
		$persto['mf1']-=$mf1;
		$persto['mf2']-=$mf2;
		$persto['mf3']-=$mf3;
		$persto['mf4']-=$mf4;
	}
	
	$a['image'] = 'mag_blago';
	if ($tp==1)
		$a['params'] = 'kb='.$wtkb.'@udmin='.$wtudmin.'@udmax='.$wtudmax.'@s1='.$wts1.'@s2='.$wts2.'@s3='.$wts3.'@s4='.$wts4.'@s5='.$wts5.'@s6='.$wts6.'@mf1='.$mf1.'@mf2='.$mf2.'@mf3='.$mf3.'@mf4='.$mf4.'@';
	elseif ($tp==0)
		$a['params'] = 'kb=-'.$wtkb.'@udmin=-'.$wtudmin.'@udmax=-'.$wtudmax.'@s1=-'.$wts1.'@s2=-'.$wts2.'@s3=-'.$wts3.'@s4=-'.$wts4.'@s5=-'.$wts5.'@s6=-'.$wts6.'@mf1=-'.$mf1.'@mf2=-'.$mf2.'@mf3=-'.$mf3.'@mf4=-'.$mf4.'@';
	else return;
	$a['esttime'] = $duration*60;
	$a['name'] = (($tp==1) ? 'Благословление' : 'Проклятие').' Верховного Мага';
	$a['special'] = 77; ### Благословение
	
	$persto['kindness'] += 1/(1+mtrunc(-1*$persto['kindness']));
	light_aura_on($a,$persto['uid']);
	set_vars(aq($persto),$persto["uid"]);
}

?>
<center class=loc>
<?php if( !empty($actMess) ) echo $actMess.'<br />'; ?>
<br />
Введите ник персонажа для редактирования:<form action=main.php method=post><input type=text name=nick class=laar><input type=submit value=ОК class=login></form><br>

<a href=main.php?zeroingall=1 class=bga>Выдать всем обнуления</a>
<a href=main.php?szeroingall=1 class=bga>Выдать всем обнуления умения</a>
<a href=main.php?zeroall=1 class=bga>Принудительно обнулить всех</a>
<!--<a href=main.php?fullzeroall=1 class=bga>Принудительно полностью обнулить всех</a>-->
<br />

<div class="login">
<a href=main.php?bot_agres=1&<?=$player->jKey();?> class=bg>Натравить на всех ботов</a>
<a href=main.php?go_home=1&<?=$player->jKey();?> class=bg>Вернуть всех в город (на арену)</a>
<a href=main.php?go_nahui=1&<?=$player->jKey();?> class=bg>Разбросать всех жителей по локациям</a>
<a href=main.php?go_inviz=1&<?=$player->jKey();?> class=bg>Наложить на всех невидимость (30 мин)</a>
<a href=main.php?go_noinviz=1&<?=$player->jKey();?> class=bg>Снять со всех невидимость</a>
<a href=main.php?go_molch=1&<?=$player->jKey();?> class=bg>Наложить на всех молчанку (30 мин)</a>
<a href=main.php?go_nomolch=1&<?=$player->jKey();?> class=bg>Снять со всех молчанки</a>
<a href=main.php?go_maxhell=1&<?=$player->jKey();?> class=bg>Восстановить всем жизненные и магические силы</a>
<a href=main.php?go_nomaxhell=1&<?=$player->jKey();?> class=bg>Обнулить жизненные и магические силы</a>
<a href=main.php?go_money=1&<?=$player->jKey();?> class=bg>Раздать всем LN (рандомно, до 100 LN)</a>
<!--<a href=main.php?go_notravm=1 class=bg>Исцелить все травмы</a>-->
<a href=main.php?go_blago=1&<?=$player->jKey();?> class=bg>Благославить всех, кто онлайн</a>
<!--<a href=main.php?go_prokl=1 class=bg>Проклясть всех, кто онлайн</a>-->
<a href=main.php?go_noaurabp=1&<?=$player->jKey();?> class=bg>Снять эффекты заклинаний ( благословление и проклятие)</a>
<a href=main.php?notravn=1&<?=$player->jKey();?> class=bg>Вылечить ВСЕ травмы</a>
<!--<a href=main.php?go_tornados=1 class=bg>Начать бой с Торнадосом</a>-->
</div>
<br />

<a href=main.php?myself_edit=1 class=but>Редактировать себя</a><hr />
<a href=main.php?weather=1 class=timef>Погода:Ясно</a>
<a href=main.php?weather=2 class=timef>Погода:Дождь</a>
<a href=main.php?weather=3 class=timef>Погода:Ливень</a>
<a href=main.php?weather=4 class=timef>Погода:Ветер</a>
<a href=main.php?weather=5 class=timef>Погода:Шторм</a>
<a href=main.php?weather=6 class=timef>Погода:Туман</a>
<a href=main.php?weather=7 class=timef>Погода:Град</a>
<a href=main.php?weather=8 class=timef>Погода:Снег</a>
<form action=main.php?zero=1 method=post>Обнулить:<input type=text name=zeronick class=laar><input type=submit value=ОК class=login></form><br><br>
<form action=main.php?fullzero=1 method=post>Полностью Обнулить:<input type=text name=fullzeronick class=laar><input type=submit value=ОК class=login></form><br><br>
<form action="main.php?" method="post">Исцелить травмы:<input type=text name=notravm class=laar><input type=submit value=ОК class=login></form><br><br>
</center>