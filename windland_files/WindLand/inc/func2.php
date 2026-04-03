<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

## Функции средней частоты вызова


function light_aura_on($a,$uid)
{
	if(intval($uid)==0) return;
	$GLOBALS['db']->sql('INSERT INTO `p_auras` (`uid`, `esttime`, `turn_esttime`, `name`, `image`, `params`, `special`)
		VALUES ("'.intval($uid).'", "'.(tme()+$a['esttime']).'", "'.@($persto['f_turn']+$a['turn_esttime']).'", "'.$a['name'].'", "'.$a['image'].'", "'.$a['params'].'", '.$a['special'].');');
}

function aura_on2($aid,$persto,$koef=1)
{
	GLOBAL $db;
	$a = $db->sqla("SELECT * FROM auras WHERE id=".intval($aid)."");
	if (is_scalar($persto)) $persto = catch_user($persto);
	if ($a)
	{
		$params = explode("@",$a["params"]);
		$nparams = '';
		foreach($params as $par)
		{
			if(!$par) continue;
			$p = explode("=",$par);
			if ($p[1][strlen($p[1])-1]=='%')
			{
			 $res = floor((intval($p[1])/100)*$persto[$p[0]])*$koef;
			 $persto[$p[0]] += $res;
			 $nparams .= $p[0].'='.$res.'@';
			}
			else
			{
			 $res = $p[1]*$koef;
			 $persto[$p[0]] += $res;
			 $nparams .= $p[0].'='.$res.'@';
			}
		}
		if ($a["special"]==1)
		{
			$silence = tme() + $a["esttime"];
			if ($persto["silence"]<$silence) $persto["silence"] = $silence;
		}
		if ($a["special"]==2)
		{
			$inv = tme() + $a["esttime"];
			if ($persto["invisible"]<$inv) $persto["invisible"] = $inv;
		}
		if ($persto["chp"]>$persto["hp"]) $persto["chp"]=$persto["hp"];
		if ($persto["cma"]>$persto["ma"]) $persto["cma"]=$persto["ma"];
		if ($persto["chp"]<0) $persto["chp"] = 0;
		if ($persto["cma"]<0) $persto["cma"] = 0;
		set_vars(aq($persto),$persto["uid"]);
		$db->sql("INSERT INTO `p_auras` ( `uid` , `esttime` , `turn_esttime` , `name` , `image` , `params` , `special`)
			VALUES ('".$persto["uid"]."', '".(time()+$a["esttime"])."', '".($persto["f_turn"]+$a["turn_esttime"])."', '".$a["name"]."', '".$a["image"]."', '".$nparams."', ".$a["special"].");");
	}
	return $a;
}

function hp_ma_up($chealth,$health,$cmana,$mana,$shp,$sma,$lastom,$tire=-1,$battle=0)
 {
	GLOBAL $sphp,$spma,$hp,$ma;
	$spma = (1500-$sma*10);
	$sphp = (700-$shp*3.5);
	if ($sphp<2) $sphp=2;
	if ($spma<2) $spma=2;

	if ($chealth<0) $chealth=0;
	if ($cmana<0) $cmana=0;

	$p=mtrunc(tme() - $lastom);

	$hp=$p*$health/$sphp+$chealth;
	if ($hp>$health) $hp=$health;
	$ma=$p*$mana/$spma+$cmana;
	if ($ma>$mana) $ma=$mana;
	$hp = floor($hp);
	$ma = floor($ma);

	if(!$battle) $battle = ',`refr`=0'; else $battle = '';
	$tireout = mtrunc($tire - $p/30);
	if ($tire>0) return "`chp` = '".$hp."',`cma` = '".$ma."',`tire`=".$tireout.",online=1,`lastom`=".tme()."".$battle;
	else return "`chp` = '".$hp."',`cma` = '".$ma."',online=1,`lastom`=".tme()."".$battle;
}

function plus_param($param)
{
	if ($param>0) return "+".$param;
	elseif ($param<0) return "-".abs($param);
	else return "0";
}

function all_params()
{
	$r = array ();
	for ($i=1;$i<7;$i++) $r[]='s'.$i;
	$r[]='kb';
	for ($i=1;$i<6;$i++) $r[]='mf'.$i;
	$r[]='hp';
	$r[]='ma';
	$r[]='udmin';
	$r[]='udmax';
	for ($i=1;$i<15;$i++)
	{
		$r[]='sp'.$i;
	}
	for ($i=1;$i<15;$i++)
	{
		$r[]='sb'.$i;
	}
	for ($i=1;$i<8;$i++)
	{
		$r[]='sm'.$i;
	}
	for ($i=1;$i<9;$i++)
	{
		$r[]='a'.$i;
	}
	for ($i=1;$i<6;$i++)
	{
		$r[]='m'.$i;
	}
	return $r;
}

function DecreaseDamage($pers)
{
	$kb = mtrunc($pers["kb"]+$pers["sb11"]);
	if ($kb<1) $kb = 1;
	return round(100-(pow(0.9,sqrt($kb))+0.1)*100);
}

function types()
{
	$r = Array();
	$r['orujie'] = 'Оружие';
	$r['shlem'] = 'Шлемы';
	$r['ojerelie'] = 'Ожерелья';
	$r['poyas'] = 'Пояса';
	$r['sapogi'] = 'Сапоги';
	$r['naruchi'] = 'Наручи';
	$r['perchatki'] = 'Перчатки';
	$r['kolco'] = 'Кольца';
	$r['kolchuga'] = 'Кольчуги';
	$r['bronya'] = 'Брони';
	$r['napad'] = 'Свитки нападения';
	$r['zakl'] = 'Свитки заклинаний';
	$r['teleport'] = 'Свитки телепорта';
	$r['zelie'] = 'Зелья/камни';
	$r['kam'] = 'Зелья восстановления';
	$r['potion'] = 'Зелья';
	$r['herbal'] = 'Травы';
	$r['fishing'] = 'Рыболовные снасти';
	$r['fish'] = 'Рыба';
	$r['resources'] = 'Ресурсы';
	$r['rune'] = 'Руны';
	$r['byxlo'] = 'Выпивка';
	$r['prim'] = 'Приманки';
	return $r;
}

function name_of_skill ($skill)
{
	if ($skill=='ma') return "Запас маны";
	elseif ($skill=='hp') return "Запас жизни";
	elseif ($skill=='cma') return "Мана";
	elseif ($skill=='chp') return "Жизнь";
	elseif ($skill=='kb') return "Класс брони";
	elseif ($skill=='mf1') return "Сокрушение";
	elseif ($skill=='mf2') return "Уловка";
	elseif ($skill=='mf3') return "Точность";
	elseif ($skill=='mf4') return "Стойкость";
	elseif ($skill=='mf5') return "Пробой Брони";
	elseif ($skill=='udmin') return "Минимальный удар";
	elseif ($skill=='udmax') return "Максимальный удар";
	elseif ($skill=='rank_i') return "Ранк";

	if ($skill=='stats')
	{
		$r = array ("Сила","Реакция","Удача","Здоровье","Интеллект","Сила Воли");
		return $r;
	}
	if ($skill=='skillsb')
	{
		$r = array ("Очки действия","Колкий удар","Владение ножами","Владение щитами","Владение мечами","Владение топорами","Владение булавами","Чтение книг","Усиление магии","Сопротивление Магии","Сопротивление Физическим повреждениям","Сопротивление Отравам","Сопротивление Электричеству","Сопротивление Огню","Сопротивление Холоду");
		return $r;
	}
	if ($skill=='skillsm')
	{
		$r = array ("Атлетизм","Эрудиция","Тяжеловес","Скорость","Обаяние","Регенерация жизни","Регенерация маны");
		return $r;
	}
	if ($skill=='skillsp')
	{
		$r = array ("Целитель","Темное искусство","Удар в спину","Воровство","Кузнец","Рыбак","Шахтер","Ориентирование на местности","Экономист","Охотник","Алхимик","Добыча камней","Дровосек","Выделка кожи");
		return $r;
	}
	$r = array ("Сила","Реакция","Удача","Здоровье","Интеллект","Сила Воли");
	$num = 0;
	if ($skill=='s1')$num=1;
	if ($skill=='s2')$num=2;
	if ($skill=='s3')$num=3;
	if ($skill=='s4')$num=4;
	if ($skill=='s5')$num=5;
	if ($skill=='s6')$num=6;
	if ($num<>0) return $r[$num-1];
	$r = array ("Атлетизм","Эрудиция","Тяжеловес","Скорость","Обаяние","Регенерация жизни","Регенерация маны");
	if (substr_count($skill,"sm")) return @$r[str_replace("sm","",$skill)-1];
	$r = array ("Очки действия","Колкий удар","Владение ножами","Владение щитами","Владение мечами","Владение топорами","Владение булавами","Чтение книг","Усиление магии","Сопротивление Магии","Сопротивление Физическим повреждениям","Сопротивление Отравам","Сопротивление Электричеству","Сопротивление Огню","Сопротивление Холоду");
	if (substr_count($skill,"sb"))return @$r[str_replace("sb","",$skill)-1];
	$r = array ("Целитель","Темное искусство","Удар в спину","Воровство","Кузнец","Рыбак","Шахтер","Ориентирование на местности","Экономист","Охотник","Алхимик","Добыча камней","Дровосек","Выделка кожи");
	if (substr_count($skill,"sp"))return @$r[str_replace("sp","",$skill)-1];
	$r = array ("Вертлявость","Бронебойность","Толстая кожа","Расчётливость","Быстрота","Любовник","Пиротехник","Электрик");
	if (substr_count($skill,"a") and $skill<>'ma' and $skill<>'udmax' and $skill<>'сma') return @$r[str_replace("a","",$skill)-1];

	$r = array ("Религия","Некромантия","Стихийная магия","Магия порядка","Вызовы существ");
	$num = 0;
	if ($skill=='m1')$num=1;
	if ($skill=='m2')$num=2;
	if ($skill=='m3')$num=3;
	if ($skill=='m4')$num=4;
	if ($skill=='m5')$num=5;
	if ($num<>0) return $r[$num-1];

	if ($skill=='level') return "Уровень";
	if ($skill=='colldown') return "Перезарядка(сек)";
	if ($skill=='turn_colldown') return "Перезарядка(ходы)";
	if ($skill=='esttime') return "Время действия";
	if ($skill=='manacost') return "Стоимость маны";
	if ($skill=='targets') return "Кол-во целей";
	if ($skill=='main_presents') return "Свиток опыта";
	return $skill;
}

function remove_weapon($id,$v)
{
	GLOBAL $player, $db;
	if (!is_array($v)) $v = $db->sqla("SELECT * FROM `wp` WHERE `id` = '".$id."' and weared=1 and uidp=".$player->pers["uid"]."");
	if ($v)
	{
		$cop = $player->pers;
		$r = all_params();
		foreach ($r as $a) if ( @$v[$a] ) $player->pers[$a] -= $v[$a];
		$player->pers["hp"]-=5*$v["s4"];
		$player->pers["ma"]-=9*$v["s6"];
		if ( $aq = aq($player->pers, $cop) ) $db->sql("UPDATE `users` SET ".$aq." WHERE `uid` = ".$player->pers['uid']." ;");
		$db->sql("UPDATE `wp` SET `weared` = 0 WHERE `id` = ".$v["id"]."");
		unset($cop);
	}
}

function dress_weapon($id_of_weapon,$checker=false)
{
	GLOBAL $player,$db;
	$i = 5;
	$v = $db->sqla("SELECT * FROM `wp` WHERE `id`= ".$id_of_weapon." and uidp=".$player->pers["uid"]." and weared=0");
	if (@$v["id"])
	{
		$z=1;
		if ($player->pers["level"]<$v["tlevel"]) $z=0;
		if (!$checker)
			foreach ($v as $key => $value)
			{
				if ($key[0]=='t' and $key<>'timeout')
				if ( @$player->pers[substr($key,1,strlen($key)-1)]<$value and $value>0 ) $z =0;
				if ($z==0) break;
			}
		if ($z==1)
		{
			$r = all_params();
			foreach ($r as $a)
				if ( @$v[$a] ) $player->pers[$a] += $v[$a];
			$player->pers["hp"]+=5*$v["s4"];
			$player->pers["ma"]+=9*$v["s6"];
			//Снимаем то что было
			$z=0;
			if ($v["type"]=='orujie')
			{
				$tmp = $db->sqlr("SELECT COUNT(id) FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='orujie'");
				if ($tmp>=2)
				{
					if ($v["stype"]=='noji' or $v["stype"]=='shit')
					{
						$w_for_remove = $db->sqla("SELECT * FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='orujie' and (stype='noji' or stype='shit')");
						if (@$w_for_remove["id"]) remove_weapon($w_for_remove["id"],$w_for_remove);
					}
					else
					{
						$w_for_remove = $db->sqla("SELECT * FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='orujie' and stype<>'noji' and stype<>'shit'");
						if (@$w_for_remove["id"]) remove_weapon($w_for_remove["id"],$w_for_remove);
					}
				}
				elseif ($tmp==1)
				{
					$w_for_remove = $db->sqla("SELECT * FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='orujie'");
					if ($v["stype"]<>'noji' and $v["stype"]<>'shit' and $w_for_remove["stype"]<>'noji' and $w_for_remove["stype"]<>'shit') remove_weapon ($w_for_remove["id"],$w_for_remove);
				}
			}
			elseif ($v["type"]=='kolco')
			{
				$tmp = $db->sqlr("SELECT COUNT(id) FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='kolco'");
				if ($tmp>=2)
				{
					$w_for_remove = $db->sqla("SELECT * FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='kolco'");
					if (@$w_for_remove["id"]) remove_weapon ($w_for_remove["id"],$w_for_remove);
				}
			}
			elseif ($v["type"]=='kam')
			{
				$tmp = $db->sqlr("SELECT COUNT(id) FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='kam'");
				if ($tmp==4)
				{
					$w_for_remove = $db->sqla("SELECT * FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='kam'");
					if (@$w_for_remove["id"]) remove_weapon ($w_for_remove["id"],$w_for_remove);
				}
			}
			else
			{
				$w_for_remove = $db->sqla("SELECT * FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='".$v["type"]."'");
				if (@$w_for_remove["id"]) remove_weapon ($w_for_remove["id"],$w_for_remove);
			}
			$db->sql("UPDATE wp SET weared=1 WHERE id=".$v["id"]."");
			if ($aq=aq($player->pers)) $db->sql("UPDATE `users` SET ".$aq." WHERE `uid` = ".$player->pers["uid"]." ;");
		}
	}
}

function remove_all_weapons()
{
	GLOBAL $player,$db;
	$res = $db->sql("SELECT * FROM `wp` WHERE `weared` = 1 and `uidp` = ".$player->pers['uid']);
	while($v = mysql_fetch_assoc($res))
	{
		$r = all_params();
		foreach ($r as $a)
			if ( @$v[$a] ) $player->pers[$a]-= $v[$a];
		$player->pers["hp"]-=5*$v["s4"];
		$player->pers["ma"]-=9*$v["s6"];
	}
	if ( $aq = aq($player->pers) ) $db->sql('UPDATE `users` SET '.$aq.' WHERE `uid` = '.$player->pers['uid']);
	$db->sql('UPDATE `wp` SET `weared`= 0 WHERE `uidp` = '.$player->pers['uid']);
}

function remove_all_weapons_fight($user)
{
	GLOBAL $db;
	$res = $db->sql("SELECT * FROM `wp` WHERE `weared` = 1 and uidp=".$user."");
	$personaj = $db->sqla("SELECT * FROM `users` WHERE uid=".$user."");
	while($v = mysql_fetch_array($res))
	{
		$r = all_params();
		foreach ($r as $a)
		if ($v[$a]) $personaj[$a] -= $v[$a];
		$personaj["hp"]-=5*$v["s4"];
		$personaj["ma"]-=9*$v["s6"];
	}
	if ($aq=aq($personaj))
	$db->sql("UPDATE `users` SET ".$aq." WHERE `uid` = ".$personaj["uid"]." ;");
	$db->sql("UPDATE wp SET weared=0 WHERE uidp=".$personaj["uid"]."");
}

function insert_wp($id,$uid,$durability = -1,$weared = 0 ,$user = '')
{
	GLOBAL $db;
	$uid = intval($uid);
	if(is_scalar($id)) $v = $db->sqla("SELECT * FROM weapons WHERE id='".$id."'");
	else $v = $id;
	$id = $v["id"];
	if ($durability==-1)$durability=$v["max_durability"];
	if (empty($v["id"])) return 0;
	
	$user = $db->sqlr("SELECT `user` FROM `users` WHERE `uid`=".$uid);
	$_colls = '';
	$_params = '';
	$r = all_params();
	foreach ($r as $param)
	{
		if( @$v[$param]!=0)
		{
			$_colls .= ',`'.$param.'`';
			$_params .= ",'".$v[$param]."'";
		}
		$param = 't'.$param;
		if( @$v[$param]!=0)
		{
			$_colls .= ',`'.$param.'`';
			$_params .= ",'".$v[$param]."'";
		}
	}
	$db->sql("INSERT INTO `wp` ( `id` , `uidp` , `weared` ,`id_in_w`, `price` , `dprice` , `image` , `index` , `type` , `stype` , `name` , `describe` , `weight` , `where_buy` , `max_durability` , `durability` , `present` , `clan_sign` , `clan_name` ,`radius` , `slots` ,`arrows` ,`arrows_max` ,`arrow_name` , `arrow_price` , `tlevel` ,`p_type` , `user`, `material_show`, `material` ".$_colls.")
		VALUES (0, '".$uid."', '".$weared."','".$id."','".$v["price"]."', '".$v["dprice"]."', '".$v["image"]."', '".$v["index"]."', '".$v["type"]."', '".$v["stype"]."', '".$v["name"]."', '".$v["describe"]."', '".$v["weight"]."', '".$v["where_buy"]."', '".$v["max_durability"]."', '".$durability."', '".$v["present"]."', '', '', '".$v["radius"]."', '".$v["slots"]."', '".$v["arrows"]."', '".$v["arrows_max"]."', '".$v["arrow_name"]."', '".$v["arrow_price"]."', '".$v["tlevel"]."','".$v["p_type"]."', '".$user."', '".$v["material_show"]."', '".$v["material"]."' ".$_params.");", __FILE__,__LINE__,__FUNCTION__,__CLASS__);

	return $db->insert_id();
}

function Weared_Weapons($uid = 0)
{
	GLOBAL $db;
	if(!$uid)
	{
		$uid = $GLOBALS['player']->pers['uid'];
	}
	$array = $db->sql("SELECT stype,udmin,udmax,kb FROM wp WHERE uidp=".intval($uid)." and weared=1 and type='orujie'", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	$_W["noji"] = 0;
	$_W["mech"] = 0;
	$_W["topo"] = 0;
	$_W["drob"] = 0;
	$_W["shit"] = 0;
	while($a = mysql_fetch_array($array,MYSQL_ASSOC))
	{
		@$_W[$a["stype"]] += 1;
		@$_W[$a["stype"]]["udmin"] = $a["udmin"];
		@$_W[$a["stype"]]["udmax"] = $a["udmax"];
		@$_W[$a["stype"]]["kb"] = $a["kb"];
	}
	$_W["OD"] = $_W["noji"]*1 +
				$_W["mech"]*2 +
				$_W["topo"]*3 +
				$_W["drob"]*4 +
				$_W["shit"]*1;
	return $_W;
}

function aura_on($aid,$pers,$persto,$get_mana = 1)
{
	GLOBAL $db;
	$a = $db->sqla("SELECT * FROM u_auras WHERE id=".intval($aid)."");
	if ($a and $a["manacost"]<=$pers["cma"] and $a["tlevel"]<=$pers["level"]
		and $a["ts6"]<=$pers["s6"] and $a["tm1"]<=$pers["m1"] and $a["tm2"]<=$pers["m2"] and $a["cur_colldown"]<=tme() and $a["cur_turn_colldown"]<=$pers["f_turn"])
	{
		$params = explode("@",$a["params"]);
		$nparams = '';
		foreach($params as $par)
		{
			if(!$par) continue;
			$p = explode("=",$par);
			if ($p[1][strlen($p[1])-1]=='%')
			{
			 $res = floor((intval($p[1])/100)*$persto[$p[0]]);
			 if ($res)
			 {
			  $persto[$p[0]] += $res;
			  $nparams .= $p[0].'='.$res.'@';
			 }
			}
			else
			{
			 $persto[$p[0]] += $p[1];
			 $nparams .= $p[0].'='.$p[1].'@';
			}
		}
		if ($a["special"]==1)
		{
			$silence = tme() + $a["esttime"];
			if ($persto["silence"]<$silence) $persto["silence"] = $silence;
		}
		if ($a["special"]==2)
		{
			$inv = tme() + $a["esttime"];
			if ($persto["invisible"]<$inv) $persto["invisible"] = $inv;
		}
		if ($persto["chp"]>$persto["hp"]) $persto["chp"]=$persto["hp"];
		if ($persto["cma"]>$persto["ma"]) $persto["cma"]=$persto["ma"];
		if ($persto["chp"]<0) $persto["chp"] = 0;
		if ($persto["cma"]<0) $persto["cma"] = 0;
		if ($pers["uid"]==$persto["uid"]) $pers = $persto;
		set_vars(aq($persto),$persto["uid"]);
		$db->sql("INSERT INTO `p_auras` ( `uid` , `esttime` , `turn_esttime` , `name` , `image` , `params` , `special`) VALUES ('".$persto["uid"]."','".(time()+$a["esttime"])."','".($persto["f_turn"]+$a["turn_esttime"])."','".$a["name"]."','".$a["image"]."','".$nparams."',".$a["special"].");");
		if($a["autocast"] and $pers["uid"]==$persto["uid"])
		{
			$autocast = $a["id"];
			$db->sql("INSERT INTO `p_auras` ( `uid` , `esttime` , `turn_esttime` , `name` , `image` , `params` , `autocast`) VALUES ('".$persto["uid"]."', '".(tme()+$a["colldown"]+5)."', '0', '".$a["name"]." [Автокаст]', '".$a["image"]."', '', ".$autocast.");");
		}
		if ($get_mana)
		{
		$pers["cma"] -= $a["manacost"];
		$pers["m".$a["type"]] += 1/($pers["m".$a["type"]]+1);
		set_vars("cma=".$pers["cma"].",m1=".$pers["m1"].",m2=".$pers["m2"],$pers["uid"]);
		if ($pers["curstate"]==4)
		 $cur_turn_colldown = ",cur_turn_colldown=turn_colldown+".$pers["f_turn"]."";
		else
		 $cur_turn_colldown = "";
		$db->sql ("UPDATE `u_auras` SET cur_colldown=".tme()."+colldown".$cur_turn_colldown." WHERE id=".$a["id"]);
		//echo "UPDATE `u_auras` SET cur_colldown=".tme()."+colldown".$cur_turn_colldown." WHERE id=".$a["id"];
		}
	}
	return $a;
}

function insert_blast($id,$uid)
{
	GLOBAL $db;
	$z = $db->sqla("SELECT * FROM blasts WHERE `id`=".intval($id));
	if (!$z) return false;
	$q = 'INSERT INTO `u_blasts` ( `id` , `id_in_w`';
	$v = ")VALUES ('0', '".$z["id"]."'";
	foreach($z as $key=>$value)
	{
		if (is_string($key) and $key<>"id" and $key<>"learnall")
		{
		$q .= ',`'.$key.'`';
		$v .= ",'".$value."'";
		}
	}
	$q .= ',`uidp`';
	$v .= ','.intval($uid).');';
	$db->sql($q.$v);
	return $db->insert_id();
}

function insert_aura($id,$uid)
{
	GLOBAL $db;
	$z = $db->sqla("SELECT * FROM auras WHERE `id`=".intval($id));
	if (!$z) return false;
	$q = 'INSERT INTO `u_auras` ( `id` , `id_in_w`';
	$v = ")VALUES ('0', '".$z["id"]."'";
	foreach($z as $key=>$value)
	{
		if (is_string($key) and $key<>"id" and $key<>"learnall")
		{
		$q .= ',`'.$key.'`';
		$v .= ",'".$value."'";
		}
	}
	$q .= ',`uidp`';
	$v .= ','.intval($uid).');';
	$db->sql($q.$v);
	return $db->insert_id();
}


?>