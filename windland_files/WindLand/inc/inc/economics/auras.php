<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
##############################
#### Mod Joe. 13.04.2013 #####
##############################

## new for new.
if (isset($http->post["scroll"]))
{
	$v = $db->sqla("SELECT `id`,`index`,`type` FROM `wp` WHERE `id`='".intval($http->post["scroll"])."' and `index`='invis' and durability>0");

	$user_loc = $db->sql("SELECT * FROM `users` WHERE `online`=1 and `location` = '".$player->pers["location"]."' and `x`=".$player->pers["x"]." and `y`=".$player->pers["y"]." and invisible>0");
	$user_loc_c = $db->sqlr("SELECT COUNT(*) FROM `users` WHERE `online`=1 and `location` = '".$player->pers["location"]."' and `x`=".$player->pers["x"]." and `y`=".$player->pers["y"]." and invisible>0");

	if ($user_loc_c>0)
	{
		while($us = mysql_fetch_assoc($user_loc)) {

		$db->sql("UPDATE p_auras SET esttime=0 WHERE uid=".$us["uid"]." and special>1 and special<3 and esttime>".tme().";");
		$db->sql("UPDATE users SET invisible=0 WHERE uid=".$us["uid"]."");

		say_to_chat ('s','Вы обнаружены'.'.',1,$us["user"],'*',0);
		}


		$db->sql("UPDATE wp SET durability=durability-1 WHERE id=".$v["id"]."");
		$_RETURN .= "<font class=puns> Вы удачно использовали свиток ".$v["name"]." ".$user_loc_c."</font>";
	}
		if ($user_loc_c<=0){
		say_to_chat ('s','В этой локации нет невидимок '.$user_loc_c.''.'.',1,$player->pers["user"],'*',0);
	}
}
## exit new for new/
if (isset($http->post["zakl"]) and isset($http->post["fornickname"]))
{
	$v = $db->sqla("SELECT `id`,`index`,p_type FROM wp WHERE uidp=".$player->pers["uid"]." and weared=0 and type='zakl' and id=".intval($http->post["zakl"])."");
	$persto = $db->sqla ("SELECT uid,user,location FROM `users`	WHERE `user` = '".$http->post["fornickname"]."'");
	if ($persto["location"]==$player->pers["location"])
	{
		if ($v["p_type"]>=10 and $v["p_type"]<=12)
		{
			$special = $v["p_type"]-7;
			$all = $db->sqlr("SELECT name FROM p_auras WHERE uid=".$persto["uid"]." and special=".$special." and esttime>".tme()."");
			if ($all)
			{
				$db->sql("UPDATE p_auras SET esttime=0	WHERE uid=".$persto["uid"]." and special=".$special." and esttime>".tme()." LIMIT 1;");
				say_to_chat ('s','Персонаж <font class=user>'.$player->pers["user"].'</font> исцелил вас от травмы'	.'(<b>'.$all.'</b>).',1,$persto["user"],'*',0);
				say_to_chat ('s','Вы исцелили <font class=user>'.$persto["user"].'</font> от травмы'.'(<b>'.$all.'</b>).',1,$player->pers["user"],'*',0);
				$db->sql("UPDATE wp SET durability=durability-1 WHERE id=".$v["id"]."");
				$player->pers["kindness"] += 1/(1+mtrunc($player->pers["kindness"]));
				set_vars("kindness=".$player->pers["kindness"],$player->pers["uid"]);
			}
		}
		elseif ($v["p_type"]==50)
		{
			$all = $db->sqlr("SELECT name FROM p_auras WHERE uid=".$persto["uid"]." and special=50 and esttime>".tme()."");
			if ($all)
			{
				$db->sql("UPDATE p_auras SET esttime=0 WHERE uid=".$persto["uid"]." and special=50 and esttime>".tme()." LIMIT 1;");
				say_to_chat ('s','Персонаж <font class=user>'.$player->pers["user"].'</font> исцелил вас от травмы'	.'(<b>'.$all.'</b>).',1,$persto["user"],'*',0);
				say_to_chat ('s','Вы исцелили <font class=user>'.$persto["user"].'</font> от травмы'.'(<b>'.$all.'</b>).',1,$player->pers["user"],'*',0);
				$db->sql("UPDATE wp SET durability=durability-1 WHERE id=".$v["id"]."");
				$player->pers["kindness"] += 1/(1+mtrunc($player->pers["kindness"]));
				set_vars("kindness=".$player->pers["kindness"],$player->pers["uid"]);
			}
		}
		else
		{
			$not_error = aura_on2($v["index"],$persto["uid"]);
			if ($not_error)
			{
				say_to_chat ('s','Персонаж <font class=user>'.$player->pers["user"].'</font> накладывает на вас'.' <font class=user>'.$not_error["name"]."</font>.",1,$persto["user"],'*',0);
				$db->sql("UPDATE wp SET durability=durability-1 WHERE id=".$v["id"]."");
				if($persto["uid"] == $player->pers["uid"])
					$player->pers = catch_user($player->pers["uid"]);
			}
		}
	}
	else $_RETURN .= "<font class=puns>Нет такого персонажа<b>(".$http->post["fornickname"].")</b> в данном месте</font>";

	unset($v);
	unset($persto);
}



## Алхимические эликсиры.
if (!empty($http->post["potion"]))
{
	$db->sql("START TRANSACTION;");
	$kl=1;
	$zakl = intval($http->post["potion"]);
	$zakl = $db->sqla("SELECT `index`,`name`,`id`,`image`,`durability` FROM `wp` WHERE `id`='".$zakl."' and durability>0");
	$acount = $db->sqlr("SELECT COUNT(*) FROM p_auras WHERE uid=".$player->pers["uid"]." and `name`='".$zakl["name"]."'");
	$Ppers = $player->pers;
	$potions = $db->sql("SELECT params FROM p_auras WHERE uid=".$player->pers["uid"]." and special=13");
	while($p = mysql_fetch_assoc($potions))
	{
		$_p = explode("@",$p["params"]);
		foreach($_p as $__p)
		{
			$ep = explode("=",$__p);
//			$Ppers[$ep[0]] -= $ep[1];
		}
	}

	if ($player->pers["level"]<5) $message = 'Вы не сможете вынести таких нагрузок из-за зелья, т.к. не достигли ещё 5 уровня.';
	elseif ($zakl and $zakl["durability"]>0)
	{
		$param = explode("|",$zakl["index"]);
	if (($potionID=str_replace("potions/","",$zakl["image"]))<14 or $potionID==20)
	{
		$player->pers[$param[1]]+=$param[2];
		if ($param[1]=="s1")$sk = "Сила";
		if ($param[1]=="s2")$sk = "Реакция";
		if ($param[1]=="s3")$sk = "Удача";
		if ($param[1]=="s4")$sk = "Здоровье";
		if ($param[1]=="s6")$sk = "Cила Воли";
		if ($param[1]=="kb")$sk = "Класс брони";
		if ($param[1]=="hp")$sk = "HP";
		if ($param[1]=="ma")$sk = "MA";
		if ($param[1]=="udmax")$sk = "Удар";
		if ($param[1]=="mf1")$sk = "Сокрушение";
		if ($param[1]=="mf2")$sk = "Уловка";
		if ($param[1]=="mf3")$sk = "Точность";
		if ($param[1]=="mf4")$sk = "Стоикость";
		if ($param[1]=="mf5")$sk = "Ярость";
		$sk = $sk." ".$param[2];
		$a[$param[1]]=$param[2];
		if ($param[1]=="udmax")
		{
		$a["udmin"]=$param[2];
		$player->pers["udmin"]+=$param[2];
		}
	}else{
		if ($potionID==14)
		{
			$p = floor(0.01*$param[2]*$Ppers["udmin"]);
			if ($p>50) $p=50;
			$player->pers["udmin"]+= $p;
			$player->pers["udmax"]+= $p;
			$a["udmin"] = $p;
			$a["udmax"] = $p;
		}

		if ($potionID==15)
		{
			$p = floor(0.01*$param[2]*$Ppers["kb"]);
			if ($p>100) $p=100;
			$player->pers["kb"]+=$p;
			$a["kb"] = $p;
		}
		if ($potionID==16)
		{
			$p1 = floor(0.01*$param[2]*$Ppers["st6"]);
			$p2 = floor(0.01*($param[2]-5)*$Ppers["ma"]);
			if ($p1>12) $p1=12;
			if ($p2>250) $p2=250;
			$player->pers["s6"]+=$p1;
			$player->pers["ma"]+=$p2;
			$a["s6"] = $p1;
			$a["ma"] = $p2;
		}
		if ($potionID==17)
		{
			$p1 = floor(0.01*$param[2]*$Ppers["st1"]);
			$p2 = floor(0.01*($param[2]-5)*$Ppers["ma"]);
			if ($p1>12) $p1=12;
			if ($p2>250) $p2=250;
			$player->pers["s1"]+=$p1;
			$player->pers["hp"]+=$p2;
			$a["s1"] = $p1;
			$a["hp"] = $p2;
		}
		if ($potionID==18)
		{
			$mf1 = floor(0.01*$param[2]*$Ppers["mf1"]);
			$mf2 = floor(0.01*$param[2]*$Ppers["mf2"]);
			$p1 = floor(0.01*$param[2]*$Ppers["s2"]);
			$p2 = floor(0.01*$param[2]*$Ppers["s3"]);
			if ($mf1>200) $mf1=200;
			if ($mf2>200) $mf2=200;
			if ($p1>12) $p1=12;
			if ($p2>12) $p2=12;
			$player->pers["s2"]+=$p1;
			$player->pers["s3"]+=$p2;
			$player->pers["mf1"]+=$mf1;
			$player->pers["mf2"]+=$mf2;
			$a["s2"]=$p1;
			$a["s3"]=$p2;
			$a["mf1"]=$mf1;
			$a["mf2"]=$mf2;
		}
		## Зелье востановления усталости.
		if ($potionID==21)
		{
			$player->pers["tire"]-=floor($param[2]);
			if ($player->pers["tire"]<0) $player->pers["tire"] = 0;
		}
		if ($potionID==22)
		{
			$p = floor(0.01*$param[2]*$Ppers["udmin"]);
			if ($p>50) $p=50;
			$player->pers["udmin"]+= $p;
			$player->pers["udmax"]+= $p;
			$a["udmin"] = $p;
			$a["udmax"] = $p;
		}
		if ($potionID==19)
		{
			if ($player->pers["invisible"]<tme()+$param[0]) $player->pers["invisible"] = tme()+$param[0];
		}
	}

	if($acount>=1)
	{
		$db->sql ("UPDATE p_auras SET esttime=esttime+".$param[0]." WHERE uid=".$player->pers["uid"]." and `name`='".$zakl['name']."'");
		//quest ('Вы выпили <font class=user>'.$zakl[1]."</font>. Увеличилось только время действия.");
		say_to_chat ("s","Вы выпили <font class=user>".$zakl['name']."</font>. Увеличилось только время действия эликсира.",1,$player->pers["user"],'*',0);
        $db->sql("UPDATE wp SET durability=durability-1 WHERE id=".intval($http->post["potion"])."");
    }else{
      	## Наложение аур все элики кроме зелья усталости и невидимости. (а зачем?)..

        if ($p[0]<>'hp' and intval($p[1])!=0) {
		$a["hp"] += $a["s4"]*5;
		$player->pers["hp"]+= $a["s4"]*5;
		}
		if ($p[0]<>'ma' and intval($p[1])!=0){
		$a["ma"] += $a["s6"]*9;
		$player->pers["ma"]+= $a["s6"]*9;
		}
		$z["image"] = '86';
		$z["params"] = '';
		if ($potionID!=21 and $potionID!=19) foreach ($a as $key=>$value)
		$z["params"] .= $key.'='.$value.'@';
		$z["esttime"] = $param[0];
		$z["turn_esttime"] = 0;
		$z["name"] = $zakl['name'];
		$z["special"] = 13;
		if ($potionID!=21)
		light_aura_on($z,$player->pers["uid"]);
		set_vars(aq($player->pers),$player->pers["uid"]);
		//message_me ('Вы выпили <font class=user>'.$zakl[1]."</font>.");
		say_to_chat ("s","Вы выпили <font class=user>".$zakl['name']."</font>.",1,$player->pers["user"],'*',0);
		$db->sql("UPDATE wp SET durability=durability-1 WHERE id=".intval($http->post["potion"])."");
	}
}
	$db->sql("COMMIT;");
}






if (@$http->post["teleport"])
{
	$cell = $db->sqla("SELECT * FROM nature WHERE x=".intval($http->post["X"])." and y=".intval($http->post["Y"])."");
	$v = $db->sqla("SELECT weight,uidp,where_buy,dprice FROM wp WHERE id=".intval($http->post["teleport"])." and uidp=".$player->pers["uid"]."");
	if (isset($cell["name"]) and isset($v["uidp"]))
	{
		$db->sql("UPDATE users SET x=".intval($http->post["X"])." , y=".intval($http->post["Y"]).", curstate=2 , location='out' WHERE uid=".$player->pers["uid"]."");
		$db->sql("UPDATE wp SET durability=durability-1 WHERE id=".intval($http->post["teleport"])."");
		$player->pers["x"]=intval($http->post["X"]);
		$player->pers["y"]=intval($http->post["Y"]);
		$player->pers["curstate"]=2;
		$player->pers["location"]="out";
	}elseif (empty($cell["name"])) $_RETURN .=  "Проход на эту локацию закрыт!";
}


?>