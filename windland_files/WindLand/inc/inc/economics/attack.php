<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
##############################
#### Mod Joe. 13.04.2013 #####
##############################

// Иммунка смотрам
$watch_immut = true;



## ***************** Боевое ******************* ##

## Боевое нападение, Закрытое боевое нападение
if (isset($http->post['napad_b']) and $player->pers['cfight']==0)
{
	## Считываем вещь.
	$v = $db->sqla("SELECT * FROM `wp` WHERE `id`='".intval($http->post["napad_b"])."' and durability>0");

	$zid = $db->sqlr("SELECT COUNT(*) FROM p_auras WHERE uid=".$player->pers["uid"]." and `special`='50'");

	if ($v["index"]=='b' or $v["index"]=='b_z')
	{
		## Считываем юзера.
		$perstowho = $db->sqla("SELECT * FROM `users` WHERE `user`='".$http->post['fornickname']."' and online=1");
		## Если юзер в бою, считываем бой.
		//if ($perstowho["cfight"]>10) $fight = $db->sqla("SELECT * FROM `fights` WHERE `id`='".$perstowho["cfight"]."'");

        $fight = $db->sqla("SELECT * FROM `fights` WHERE `id`='".$perstowho["cfight"]."'");
        $k = 0;
         //$_RETURN .= "<font class=puns> Вы удачно использовали свиток ".$v["name"]."</font>";
        if ($zid>=1) $_RETURN .= 'У вас боевая травма, нападение невозможно!.';
        ## Проверяем на существование логина
        elseif (!$perstowho) $_RETURN .= 'Персонаж <b>'.$login.'</b> не существует.';
        ## В игре персонаж?
        elseif ( $perstowho["online"]!=1 or $perstowho["invisible"]>tme() ) $_RETURN .= 'Персонаж <b>'.$login.'</b> не в игре.';
        ## Проверяем не передает ли он себе что - то. ( zaebl login)
        elseif ( ($login == $player->pers["user"]) or ($login == $player->pers["smuser"]) or ($perstowho["uid"] == $player->pers["uid"]))
        $_RETURN .= 'Зачем нападать на себя?';
        ## Проверяем местонахождение обоих персонажей.
        elseif (($perstowho["location"]!=$player->pers["location"]))
        $_RETURN .= 'Персонажа <b>'.$login.'</b> нет в данном месте.';
        ## Проверяем бой ( открыт, закрыт)
        elseif ($fight["closed"])
        $_RETURN .= 'Нельзя вмешаться в закрытый бой.';
        ## Проверяем бой ( открыт, закрыт)
        elseif ($fight["type"]=='f')
        $_RETURN .= 'Бой уже окончен.';
        ## Проверяем ХП.
        elseif (intval($player->pers["x"])!=intval($perstowho["x"]))
        $_RETURN .= 'Персонажа <b>'.$login.'</b> нет в данном месте.';
        ## Проверяем ХП.
        elseif (intval($player->pers["y"])!=intval($perstowho["y"]))
        $_RETURN .= 'Персонажа <b>'.$login.'</b> нет в данном месте.';
        ## Проверяем уровень персонажа.
        elseif ($perstowho["level"]<=4 and $za==0)
        $_RETURN .= 'Запрещено нападать на персонажей ниже 5-го уровня.';
        else 
		{
			## Бой не завершен. Бой существует.
			if ($fight["type"]<>'f' and $fight["id"])
			{

				## Невидимка?
				if ($player->pers["invisible"]<=tme()) $nyou = "<font color=".$colors[$player->pers["fteam"]].">".$player->pers["user"]."</font>[".$player->pers["level"]."]";
				else $nyou = "<font color=".$colors[$player->pers["fteam"]].">невидимка</font>[??]";
				
				if ($fight["type"]<>'f' and $fight["id"])
				{
					$player->pers["curstate"] = 4;
					$player->pers["cfight"] = $fight["id"];
					$db->sql ("UPDATE `fights` SET players=players+1 WHERE id=".$fight["id"]."");
					## Вмешивание в бой.
					## Если ЗА персонажа.
					if (($za==1 and $perstowho["fteam"]==1) or ($za==0 and $perstowho["fteam"]==2))
					{
						$db->sql ("UPDATE `users` SET `curstate`=4 , `cfight`='".$fight["id"]."', fteam=1 WHERE `uid`='".$player->pers["uid"]."'");
						$fteam = 1;
					}
					## Если против.
					else
					{
						$db->sql ("UPDATE `users` SET `curstate`=4 , `cfight`='".$fight["id"]."', fteam=2 WHERE `uid`='".$player->pers["uid"]."'");
						$fteam = 2;
					}
					## Если тактические бой.
					if($fight["bplace"])
					{
						$bplace = $db->sqla("SELECT * FROM battle_places WHERE id=".$fight["bplace"]);
						if($fteam==1) $xf=4; else $xf=11;
						$yf=floor(15/2)-1;
						while ($xf>0 and $xf<15)
						{
							$yf++;
							if ($yf%$maxy==0)
							{
								$yf=0;
								if($fteam==1) $xf++; else  $xf--;
							}
							$bcount = $db->sqlr("SELECT COUNT(*) FROM users WHERE cfight=".$fight["id"]." and chp>0 and xf=".$xf." and yf=".$yf);
							$bcount += $db->sqlr("SELECT COUNT(*) FROM bots_battle WHERE cfight=".$fight["id"]." and chp>0 and xf=".$xf." and yf=".$yf);
							if(!substr_count($bplace["xy"],"|".$xf."_".$yf."|") and $bcount==0)
								break;
						}
						$db->sql ("UPDATE `users` SET `yf`=".$yf." , `xf`='".$xf."' WHERE `uid`='".$player->pers["uid"]."'");
					}
					
					$k=1;
					add_flog($nyou." вмешивается в бой!",$perstowho["cfight"]);
				}
			}
			## Либо происходит нападение.
			elseif($perstowho["chp"]>0)
			{
				## Поединок завершен.
				if($fight["type"]=='f') $perstowho = end_battle($perstowho);
				if ($perstowho['sign']=='watchers' and $watch_immut)
				{
					say_to_chat('w','Персонаж <b>'.$player->pers["user"].'</b> попал в тюремное заточение за попытку нападения на власти на 10 минут.',0,'','*',0);
					set_vars ("location='prison',prison='".(time()+600)."|".htmlspecialchars("Попытка нападения на власти")."',curstate=2",$player->pers["uid"]);
				}
				elseif ($player->pers['sign']=='watchers' and $watch_immut)
				{
					say_to_chat('w','Персонаж <b>'.$player->pers["user"].'</b> исключен из Стражей Закона за попытку нападения на персонажа.',0,'','*',0);
					set_vars ("`sign`='none', `state`='' , `rank`='', `clan_state`='', `clan_prev`='', `forum_accesses`='1'", UID);
					$db->sql("INSERT INTO `clans_log` (`uid`, `who`, `type`, `date`, `sign`, `text`) VALUES (".UID.", 'Нападение на игрока', 2, ".tme().", 'watchers', '');");
				}
				elseif ($za==0)
				{
					//if ($travma<>100)
					//{
						$travma=100;
						$na='Боевое нападение';
					//}
					//else
					//$na='Кровавое кулачное нападение';
					
					$place = 0;
					## Тактический бой.
					if($v["index"]=='napad_bt') $place = rand(1,5);

					$closed = 0;
					## Закрытое нападение.
					if($v["index"]=='b_z'/* and $perstowho["sign"]!='watchers'*/)
					{
						$closed = 1;
						$na .= '[ЗАКРЫТОЕ]';
					}
					## Тип боя 1 - кулачка.
					## Я, он , нападение(тип) , травматичность , таймаут, оружие/нет, местность, тип боя, закрытый бой, специальный бой.
					begin_fight ($player->pers["user"],$perstowho["user"],$na,$travma,180,1,$place,0,$closed,10);
					$k=1;
				}
			}
		}
		
		if ($k==1) $db->sql("UPDATE wp SET durability=durability-1 WHERE id=".$v["id"]."");
		$player->pers = catch_user(UID);
	}
}

## ***************** END Боевое ******************* ##

## Кулачное Нападение, Закрытое кулачное нападение
if ( $http->_post('napad_new') and $player->pers['cfight']==0 )
{
	## Считываем вещь.
	$v = $db->sqla("SELECT * FROM `wp` WHERE `id`='".intval($http->post["napad_new"])."' and durability>0");
	if ($v["index"]=='k' or $v["index"]=='k_z')
	{
		$zid = $db->sqlr("SELECT COUNT(*) FROM p_auras WHERE uid=".$player->pers["uid"]." and `special`='50'");
		## Считываем юзера.
		$perstowho = $db->sqla("SELECT * FROM `users` WHERE `user`='".$http->post['fornickname']."' and online=1");
		## Если юзер в бою, считываем бой.
		//if ($perstowho["cfight"]>10) $fight = $db->sqla("SELECT * FROM `fights` WHERE `id`='".$perstowho["cfight"]."'");

        $fight = $db->sqla("SELECT * FROM `fights` WHERE `id`='".$perstowho["cfight"]."'");
        $k = 0;
        //$_RETURN .= "<font class=puns> Вы удачно использовали свиток ".$v["name"]."</font>";
        if ($zid>=1) $_RETURN .= 'У вас боевая травма, нападение невозможно!.';
        ## Проверяем на существование логина
        elseif (!$perstowho) $_RETURN .= 'Персонаж <b>'.$login.'</b> не существует.';
        ## В игре персонаж?
        elseif ($perstowho["online"]!=1 or $perstowho["invisible"]>tme()) $_RETURN .= 'Персонаж <b>'.$login.'</b> не в игре.';
        ## Проверяем не передает ли он себе что - то. ( zaebl login)
        elseif (($login == $player->pers["user"]) or ($login == $player->pers["smuser"]) or ($perstowho["uid"] == $player->pers["uid"])) $_RETURN .= 'Зачем нападать на себя?';
        ## Проверяем местонахождение обоих персонажей.
        elseif (($perstowho["location"]!=$player->pers["location"])) $_RETURN .= 'Персонажа <b>'.$login.'</b> нет в данном месте.';
        ## Проверяем бой ( открыт, закрыт)
        elseif ($fight["closed"]) $_RETURN .= 'Нельзя вмешаться в закрытый бой.';
        ## Проверяем бой ( открыт, закрыт)
        elseif ($fight["type"]=='f') $_RETURN .= 'Бой уже окончен.';
        ## Проверяем ХП.
        elseif (intval($player->pers["x"])!=intval($perstowho["x"])) $_RETURN .= 'Персонажа <b>'.$login.'</b> нет в данном месте.';
        ## Проверяем ХП.
        elseif (intval($player->pers["y"])!=intval($perstowho["y"])) $_RETURN .= 'Персонажа <b>'.$login.'</b> нет в данном месте.';
        ## Проверяем уровень персонажа.
        elseif ($perstowho["level"]<=4 and $za==0) $_RETURN .= 'Запрещено нападать на персонажей ниже 5-го уровня.';
        else
		{
			## Бой не завершен. Бой существует.
			if ($fight["type"]<>'f' and $fight["id"])
			{
				## Невидимка?
				if ($player->pers["invisible"]<=tme()) $nyou = "<font color=".$colors[$player->pers["fteam"]].">".$player->pers["user"]."</font>[".$player->pers["level"]."]";
				else $nyou = "<font color=".$colors[$player->pers["fteam"]].">невидимка</font>[??]";

				if ($fight["type"]<>'f' and $fight["id"])
				{
					$player->pers["curstate"] = 4;
					$player->pers["cfight"] = $fight["id"];
					$db->sql ("UPDATE `fights` SET players=players+1 WHERE id=".$fight["id"]."");
					## Вмешивание в бой.
					## Если ЗА персонажа.
					if (($za==1 and $perstowho["fteam"]==1) or ($za==0 and $perstowho["fteam"]==2))
					{
						$db->sql ("UPDATE `users` SET `curstate`=4 , `cfight`='".$fight["id"]."', fteam=1 WHERE `uid`='".$player->pers["uid"]."'");
						$fteam = 1;
					}
					## Если против.
					else
					{
						$db->sql("UPDATE `users` SET `curstate`=4 , `cfight`='".$fight["id"]."', fteam=2 WHERE `uid`='".$player->pers["uid"]."'");
						$fteam = 2;
					}
					## Если тактические бой.
					if($fight["bplace"])
					{
						$bplace = $db->sqla("SELECT * FROM battle_places WHERE id=".$fight["bplace"]);
						if($fteam==1) $xf=4; else $xf=11;
						$yf=floor(15/2)-1;
						while ($xf>0 and $xf<15)
						{
							$yf++;
							if ($yf%$maxy==0)
							{
								$yf=0;
								if($fteam==1) $xf++; else  $xf--;
							}
							$bcount = $db->sqlr("SELECT COUNT(*) FROM users WHERE cfight=".$fight["id"]." and chp>0 and xf=".$xf." and yf=".$yf);
							$bcount += $db->sqlr("SELECT COUNT(*) FROM bots_battle WHERE cfight=".$fight["id"]." and chp>0 and xf=".$xf." and yf=".$yf);

							if(!substr_count($bplace["xy"],"|".$xf."_".$yf."|") and $bcount==0)
								break;
						}
						$db->sql ("UPDATE `users` SET `yf`=".$yf." , `xf`='".$xf."' WHERE `uid`='".$player->pers["uid"]."'");
					}
					$k=1;
					add_flog($nyou." вмешивается в бой!",$perstowho["cfight"]);
				}
			}
			## Либо происходит нападение.
			elseif($perstowho["chp"]>0)
			{
				## Поединок завершен.
				if($fight["type"]=='f') $perstowho = end_battle($perstowho);
				if ($perstowho['sign']=='watchers' and $watch_immut)
				{
					say_to_chat('w','Персонаж <b>'.$player->pers["user"].'</b> попал в тюремное заточение за попытку нападения на власти на 10 минут.',0,'','*',0);
					set_vars ("location='prison',prison='".(time()+600)."|".htmlspecialchars("Попытка нападения на власти")."',curstate=2",$player->pers["uid"]);
				}
				elseif ($player->pers['sign']=='watchers' and $watch_immut)
				{
					say_to_chat('w','Персонаж <b>'.$player->pers["user"].'</b> исключен из Стражей Закона за попытку нападения на персонажа.',0,'','*',0);
					set_vars ("`sign`='none', `state`='' , `rank`='', `clan_state`='', `clan_prev`='', `forum_accesses`='1'", UID);
					$db->sql("INSERT INTO `clans_log` (`uid`, `who`, `type`, `date`, `sign`, `text`) VALUES (".UID.", 'Нападение на игрока', 2, ".tme().", 'watchers', '');");
				}
				elseif ($za==0)
				{
					//if ($travma<>100)
					//{
						$travma=100;
						$na='Кулачное нападение';
					//}
					//else
						//$na='Кровавое кулачное нападение';

					$place = 0;
					## Тактический бой.
					if($v["index"]=='napad_newt') $place = rand(1,5);

					$closed = 0;
					## Закрытое нападение.
					if($v["index"]=='k_z'/* and $perstowho["sign"]!='watchers'*/)
					{
						$closed = 1;
						$na .= '[ЗАКРЫТОЕ]';
					}
					## Тип боя 1 - кулачка.
					## Я, он , нападение(тип) , травматичность , таймаут, оружие/нет, местность, тип боя, закрытый бой, специальный бой.
					remove_all_weapons_fight($perstowho["uid"]);
					remove_all_weapons();
					
					begin_fight ($player->pers["user"],$perstowho["user"],$na,$travma,180,0,$place,1,$closed,9);
					$k=1;
				}
			}
		}
		if ($k==1) $db->sql("UPDATE wp SET durability=durability-1 WHERE id=".$v["id"]."");
		$player->pers = catch_user(UID);
	}
}


//Нападение.
if ( isset($http->post['napad']) and $player->pers['cfight']==0 and $http->post['fornickname']<>$player->pers["user"])
{
	$v = $db->sqla ("SELECT * FROM `wp` WHERE `id`='".intval($http->post['napad'])."' and uidp=".$player->pers["uid"]." and weared=0");
	$zid = $db->sqlr("SELECT COUNT(*) FROM p_auras WHERE uid=".$player->pers["uid"]." and `special`='50'");
	if ($v["type"]=='napad')
	{
		if ($v["index"]==100) $travma=100; else $travma=30;
		$za = intval($http->post['za']);
		$perstowho = $db->sqla("SELECT * FROM `users` WHERE `user`='".$http->post['fornickname']."' and online=1");
		if ($perstowho["cfight"]>10) $fight = $db->sqla("SELECT * FROM `fights` WHERE `id`='".$perstowho["cfight"]."'");
		$k = 0;
		if($fight["closed"]) $_RETURN = 'Нельзя вмешаться в закрытый бой';
		elseif ($zid>=1) $_RETURN = 'У вас боевая травма, нападение невозможно!.';
		else
		if ((($player->pers["location"]==$perstowho["location"] and $perstowho["location"]!='out') or
		$perstowho["location"]=='out' and $player->pers["x"]==$perstowho["x"] and $player->pers["y"]==$perstowho["y"])
		and $player->pers["user"]<>$perstowho["user"]
		)
		{
			if ($perstowho["cfight"]>10 && $fight["type"]!='f')
			{
				if ($player->pers["invisible"]<=tme())
				$nyou = "<font class=bnick color=".$colors[$player->pers["fteam"]].">".$player->pers["user"]."</font>[".$player->pers["level"]."]";
					else
				$nyou = "<font class=bnick color=".$colors[$player->pers["fteam"]]."><i>невидимка</i></font>[??]";

				if ($fight["type"]<>'f' and $fight["id"])
				{
					$player->pers["curstate"] = 4;
					$player->pers["cfight"] = $fight["id"];
					$db->sql ("UPDATE `fights` SET players=players+1 WHERE id=".$fight["id"]."");
					if (($za==1 and $perstowho["fteam"]==1) or ($za==0 and $perstowho["fteam"]==2))
					{
						$db->sql ("UPDATE `users` SET `curstate`=4 , `cfight`='".$fight["id"]."', fteam=1,refr=1 WHERE `uid`='".$player->pers["uid"]."'");
						$fteam = 1;
					}
					else
					{
						$db->sql ("UPDATE `users` SET `curstate`=4 , `cfight`='".$fight["id"]."', fteam=2,refr=1 WHERE `uid`='".$player->pers["uid"]."'");
						$fteam = 2;
					}
					if($fight["bplace"])
					{
						$bplace = $db->sqla("SELECT * FROM battle_places WHERE id=".$fight["bplace"]);
						if($fteam==1)
							$xf=4;
						else
							$xf=11;
						$yf=floor(15/2)-1;
						while ($xf>0 and $xf<15)
						{
							$yf++;
							if ($yf%$maxy==0)
							{
								$yf=0;
								if($fteam==1)
									$xf++;
								else
									$xf--;
							}
							$bcount = $db->sqlr("SELECT COUNT(*) FROM users WHERE cfight=".$fight["id"]." and chp>0 and xf=".$xf." and yf=".$yf);
							$bcount += $db->sqlr("SELECT COUNT(*) FROM bots_battle WHERE cfight=".$fight["id"]." and chp>0 and xf=".$xf." and yf=".$yf);

							if(!substr_count($bplace["xy"],"|".$xf."_".$yf."|") and $bcount==0)
								break;
						}
						$db->sql ("UPDATE `users` SET `yf`=".$yf." , `xf`='".$xf."' WHERE `uid`='".$player->pers["uid"]."'");
					}

					$k=1;
					add_flog($nyou." вмешивается в бой!",$perstowho["cfight"]);
				}
			}
			elseif($perstowho["chp"]>0)
			{
				if($fight["type"]=='f')
				{
					$perstowho = end_battle($perstowho);
				}
				if ($perstowho['sign']=='watchers' and $watch_immut)
				{
					say_to_chat('w','Персонаж <b>'.$player->pers["user"].'</b> попал в тюремное заточение за попытку нападения на власти на 10 минут.',0,'','*',0);
					set_vars ("location='prison',prison='".(time()+600)."|".htmlspecialchars("Попытка нападения на власти")."',curstate=2",$player->pers["uid"]);
				}
				elseif ($player->pers['sign']=='watchers' and $watch_immut)
				{
					say_to_chat('w','Персонаж <b>'.$player->pers["user"].'</b> исключен из Стражей Закона за попытку нападения на персонажа.',0,'','*',0);
					set_vars ("`sign`='none', `state`='' , `rank`='', `clan_state`='', `clan_prev`='', `forum_accesses`='1'", UID);
					$db->sql("INSERT INTO `clans_log` (`uid`, `who`, `type`, `date`, `sign`, `text`) VALUES (".UID.", 'Нападение на игрока', 2, ".tme().", 'watchers', '');");
				}
				elseif ($za==0)
				{
					if ($travma<>100)
					{
						$travma=30;
						$na='Нападение';
					}
					else
						$na='Кровавое нападение';

					$place = 0;
					if($v["stype"]=='napadt')
						$place = rand(1,5);
					$closed = 0;
					if($v["p_type"]==15)
					{
						$closed = 1;
						$na .= '[ЗАКРЫТОЕ]';
					}
					begin_fight ($player->pers["user"],$perstowho["user"],$na,$travma,180,1,$place,0,$closed);
					if($perstowho["kindness"]*signum($player->pers["kindness"])>-7)
						$player->pers["kindness"] -= 1/(1+mtrunc(-1*$player->pers["kindness"]));
					set_vars("kindness=".$player->pers["kindness"],$player->pers["uid"]);
					$k=1;
				}
			}
			else
				$_RETURN .= "<font class=hp>Цель слишком слаба</font>";
		}
		else
			$_RETURN .= "<font class=hp>Нет такого персонажа в данном месте</font>";

		if ($k==1)
		$db->sql("UPDATE wp SET durability=durability-1 WHERE id=".$v["id"]."");
		$pers = catch_user(UID);
	}
}
elseif(@$http->post['fornickname']==$player->pers["user"])
{
	$_RETURN .= "Нельзя напасть на себя.";
}
?>