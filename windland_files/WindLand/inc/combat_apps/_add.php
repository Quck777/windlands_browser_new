<?php

	if ( $http->_post("comment") ) $http->post["comment"] = str_replace(":",";",$http->post["comment"]);
	#ADD apps duel::
	if (@$http->post["travm"] and $cat==1 and !$player->pers["apps_id"])
	{
		$c1 = intval($http->post["travm"]);
		$c2 = intval($http->post["oruj"]);
		if ($weared_count) $c2 = 1;
		$c3 = intval($http->post["timeout"]);
		$c4 = str_replace("'","",$http->_post('comment'));
		if ($c4=='описание') $c4 = '[без описани€]';
		$bplace = intval($http->_post("bplace"));
		if ($bplace==0);
		elseif ($bplace==1);
		elseif ($bplace==3);
		elseif ($bplace==5);
		else $bplace=0;
		if ($player->pers["level"]<10)$bplace=0;;
		$db->sql("INSERT INTO `app_for_fight` (`uid` , `oruj` , `travm` , `timeout` , `atime` , `count1` , `count2` , `minlvl1` , `minlvl2` , `maxlvl1` , `maxlvl2` , `pl1` , `pl2` , `comment` , `type` ,`bplace`) VALUES (".$player->pers["uid"].", ".$c2.", ".$c1.", ".$c3.", ".(time()+300).", 1, 0, ".$player->pers["level"].", 0, 0, 0, 1, 0, '".$c4."', 1 ,".$bplace.");");
		$id = $db->insert_id();
		$player->pers["apps_id"] = $id;
		set_vars("apps_id=".$id.",fteam=1",$player->pers["uid"]);
		echo "da('«а€вка удачно подана!<br>');";
	}
	#ADD apps group::
	if (@$http->post["travm"] and $cat==2 and !$player->pers["apps_id"])
	{
		$c1 = intval($http->post["travm"]);
		$c2 = intval($http->post["oruj"]);
		if ($weared_count) $c2 = 1;
		$c3 = intval($http->post["timeout"]);
		$c4 = str_replace("'","",$http->post["comment"]);
		$c5 = intval($http->post["count1"]);
		$c6 = intval($http->post["count2"]);
		if ($c5<1) $c5=1;
		if ($c6<1) $c6=1;
		$c7 = intval($http->post["minlvl1"]);
		$c8 = intval($http->post["minlvl2"]);
		$c9 = intval($http->post["maxlvl1"]);
		$c10 = intval($http->post["maxlvl2"]);
		$c11 = intval($http->post["atime"]);
		if ($c11<120) $c11=120;
		if ($c4=='описание') $c4 = '[без описани€]';
		$bplace = intval($http->post["bplace"]);
		if ($player->pers["level"]<10)$bplace=0;
		if ($bplace==0);
		elseif ($bplace==1);
		elseif ($bplace==3);
		elseif ($bplace==5);
		else $bplace==0;
		if ($bplace)
		{
			if ($c5>10) $c5=10;
			if ($c6>10) $c6=10;
		}
		else
		{
			if ($c5>50) $c5=50;
			if ($c6>50) $c6=50;
		}
		$db->sql("INSERT INTO `app_for_fight` (`uid` , `oruj` , `travm` , `timeout` , `atime` , `count1` , `count2` , `minlvl1` , `minlvl2` , `maxlvl1` , `maxlvl2` , `pl1` , `pl2` , `comment` , `type` ,`bplace`) VALUES (".$player->pers["uid"].", ".$c2.", ".$c1.", ".$c3.", ".(time()+$c11).", ".$c5.", ".$c6.", ".$c7.", ".$c8.", ".$c9.", ".$c10.", 1, 0, '".$c4."', 2, ".$bplace.");");
		$id = $db->insert_id();
		$player->pers["apps_id"] = $id;
		set_vars("apps_id=".$id.",fteam=1",$player->pers["uid"]);
		echo "da('«а€вка удачно подана!<br>');";
	}
	#ADD apps haot::
	if (@$http->post["travm"] and $cat==3 and !$player->pers["apps_id"])
	{
		$c1 = intval($http->post["travm"]);
		$c2 = intval($http->post["oruj"]);
		if ($weared_count) $c2 = 1;
		$c3 = intval($http->post["timeout"]);
		$c4 = str_replace("'","",$http->post["comment"]);
		$c5 = intval($http->post["count1"]);
		if ($c5<2) $c5=2;
		$c7 = intval($http->post["minlvl1"]);
		$c9 = intval($http->post["maxlvl1"]);
		$c11 = intval($http->post["atime"]);
		if ($c11<120) $c11=120;
		if ($c4=='описание' or $c4=='') $c4 = '[без описани€]';
		$bplace = intval($http->post["bplace"]);
		if ($bplace==0);
		elseif ($bplace==1);
		elseif ($bplace==3);
		elseif ($bplace==5);
		else $bplace==0;
		if ($player->pers["level"]<10)$bplace=0;;
		if ($bplace and $c5>14) $c5=14;
		elseif ($c5>100) $c5=100;
		$db->sql("INSERT INTO `app_for_fight` (`uid` , `oruj` , `travm` , `timeout` , `atime` , `count1` , `minlvl1` , `maxlvl1` , `pl1` , `comment` , `type` ,`bplace`) VALUES (".$player->pers["uid"].", ".$c2.", ".$c1.", ".$c3.", ".(time()+$c11).", ".$c5.", ".$c7.", ".$c9.", 1, '".$c4."', 3 , ".$bplace.");");
		$id = $db->insert_id();
		$player->pers["apps_id"] = $id;
		set_vars("apps_id=".$id.",fteam=1",$player->pers["uid"]);
		echo "da('«а€вка удачно подана!<br>');";
	}
?>