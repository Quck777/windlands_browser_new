<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

$sql_queries_counter=0;
$sql_queries_timer=0;
$sql_longest_query_t=0;
$sql_longest_query='';
$last_say_to_chat=0;
$sql_all[0]= '';
$GLOBAL_TIME = time();
$battle_log = '';

foreach ($_POST as $key=>$value) $_POST[$key] = filter($value);
foreach ($_GET  as $key=>$value) $_GET[$key]  = filter($value);
foreach ($_COOKIE  as $key=>$value) $_COOKIE[$key]  = filter($value);
function tme()
{
 GLOBAL $GLOBAL_TIME;
 return $GLOBAL_TIME;
}
function filter($v)
{
	return str_replace("'","",str_replace("\\","",htmlspecialchars($v)));
}


// Боевые




















function _StateByIndex($a)
{
	if ($a=='g') return 'Глава клана';
	if ($a=='z') return 'Заместитель главы';
	if ($a=='c') return 'Казначей';
	if ($a=='k') return 'Отдел кадров';
	if ($a=='b') return 'Боевой отдел';
	if ($a=='p') return 'Производственный отдел';
	return 'Член клана';
}


function str_once_delete($sub,$str) {
$p = strpos (" ".$str,$sub);
if ($p>0) {
 $p--;
 $sl = strlen($sub);$sl_str = strlen($str);
 $part1 = substr ($str,0,$sl+$p);
 $part2 = substr ($str,$sl+$p,$sl_str-($sl+$p));
 $part1 = str_replace ($sub,"",$part1);
 $str = $part1.$part2;
 }
return $str;
 }
 function str_once_replace($sub,$sub_replacement,$str) {
$p = strpos (" ".$str,$sub);
if ($p>0) {
 $p--;
 $sl = strlen($sub);$sl_str = strlen($str);
 $part1 = substr ($str,0,$sl+$p);
 $part2 = substr ($str,$sl+$p,$sl_str-($sl+$p));
 $part1 = str_replace ($sub,$sub_replacement,$part1);
 $str = $part1.$part2;
 }
return $str;
 }







function sqla($q)
{
	return mysql_fetch_array(sql($q));
}

function sqlr($q,$count=0)
{
	if (empty($count)) return @mysql_result(sql($q),0);
	else
	return @mysql_result(sql($q),$count);
}

function sql($q)
{
	GLOBAL $sql_queries_counter,$sql_queries_timer,$sql_longest_query_t,$sql_longest_query,$sql_all,$_ECHO_OFF;
	$t = time()+round(microtime(),3);
	$r = $db->sql($q);
	$t = time()+round(microtime(),3) - $t;
	//if($t>0.2)
	//	say_to_chat ("a",'['.str_replace("'","",$q).'] Время работы: '.$t.'',1,'sL','*');
	$error = mysql_error();
	if ($error and $_COOKIE["uid"]==5 and !$_ECHO_OFF)echo "<font class=hp><b> ОШИБКА MySQL!!! : ".$error." <i>[".$q."]</i></b></font>";
	/*elseif ($error)
	 {
		$sql_errors = mysql_fetch_array($db->sql("SELECT sql_errors FROM configs"));
		if (!substr_count($sql_errors[0],"<".$error." [".$q."]>")) $db->sql("UPDATE configs SET sql_errors='".$sql_errors[0]."<".addslashes($error)." [".addslashes($q)."]>"."'");
	 }*/
	$sql_queries_counter++;
	$sql_queries_timer+=abs($t);
	if (abs($t)>$sql_longest_query_t)
	{
		$sql_longest_query_t=abs($t);
		$sql_longest_query = $q." &nbsp;<i>".$_SERVER['PHP_SELF']."</i>";
	}
		$sql_all[] = $q.";<b class=red>".$error."</b>";
	return $r;
}

function show_ip()
{
if($ip_address=getenv("HTTP_CLIENT_IP"));
elseif($ip_address=getenv("HTTP_X_FORWARDED_FOR"));
else $ip_address=getenv("REMOTE_ADDR");
return $ip_address;
}

function mod_st_start($name,$string)
{
GLOBAL $module_statisticks,$module_statisticks_counter,$sql_queries_counter,$sql_queries_timer;
$i = $module_statisticks_counter+1;
$module_statisticks[$i]["name"]=$name;
$module_statisticks[$i]["strings"]=$string;
$module_statisticks[$i]["sql_queries"]=$sql_queries_counter;
$module_statisticks[$i]["sql_time"]=$sql_queries_timer;
$module_statisticks[$i]["all_exec_time"]=time()+microtime();
}
function mod_st_fin()
{
GLOBAL $module_statisticks,$module_statisticks_counter,$sql_queries_counter,$sql_queries_timer;
$i = $module_statisticks_counter+1;
$module_statisticks[$i]["sql_queries"]=$sql_queries_counter-
$module_statisticks[$i]["sql_queries"];
$module_statisticks[$i]["sql_time"]=$sql_queries_timer-
$module_statisticks[$i]["sql_time"];
$module_statisticks[$i]["all_exec_time"]=time()+microtime()-
$module_statisticks[$i]["all_exec_time"];
$module_statisticks_counter++;
}



function insert_wp_new($uid,$teta,$user='')
{
	$v = sqla("SELECT * FROM wp WHERE ".$teta." LIMIT 1;");
	if (!$v["id"]) return false;
		$_colls = '';
	$_params = '';
	$r = all_params();
	foreach ($r as $param)
	{
		if($v[$param]!=0)
		{
			$_colls .= ',`'.$param.'`';
			$_params .= ",'".$v[$param]."'";
		}
		$param = 't'.$param;
		if($v[$param]!=0)
		{
			$_colls .= ',`'.$param.'`';
			$_params .= ",'".$v[$param]."'";
		}
	}
	GLOBAL $main_conn;
	$user = sqlr("SELECT user FROM users WHERE uid=".$uid);
	sql("INSERT INTO `wp` ( `id` , `uidp` , `weared` ,`id_in_w`, `price` , `dprice` , `image` , `index` , `type` , `stype` , `name` , `describe` , `weight` , `where_buy` , `max_durability` , `durability` , `present` , `clan_sign` , `clan_name` ,`radius` , `slots` ,`arrows` ,`arrows_max` ,`arrow_name` , `arrow_price` , `tlevel` ,`p_type`,`timeout` , `user`,`material_show`,`material` ".$_colls.")
	VALUES (0, '".$uid."', 0,'".$v["id_in_w"]."','".$v["price"]."', '".$v["dprice"]."', '".$v["image"]."', '".$v["index"]."', '".$v["type"]."', '".$v["stype"]."', '".$v["name"]."', '".$v["describe"]."', '".$v["weight"]."', '".$v["where_buy"]."', '".$v["max_durability"]."', '".$v["durability"]."', '".$v["present"]."', '', '', '".$v["radius"]."', '".$v["slots"]."', '".$v["arrows"]."', '".$v["arrows_max"]."', '".$v["arrow_name"]."', '".$v["arrow_price"]."', '".$v["tlevel"]."','".$v["p_type"]."','".$v["timeout"]."', '".$v["user"]."', '".$v["material_show"]."', '".$v["material"]."' ".$_params.");");

	$v["id"] = mysql_insert_id($main_conn);
	$v["uidp"] = $uid;
	return $v;
}

function insert_blast($id,$uid)
{
	$z = sqla ("SELECT * FROM blasts WHERE `id`=".intval($id));
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
	sql($q.$v);
	GLOBAL $main_conn;
	return mysql_insert_id($main_conn);
}

function insert_aura($id,$uid)
{
	$z = sqla ("SELECT * FROM auras WHERE `id`=".intval($id));
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
	sql($q.$v);
	GLOBAL $main_conn;
	return mysql_insert_id($main_conn);
}



///// made by china

function remove_weapon_orden($v,$uid)
 {
	//GLOBAL $pers;
	$pers = sqla("SELECT * FROM `users` WHERE `uid`='".$uid."' LIMIT 1");
	if (!is_array($v)) $v = sqla ("SELECT * FROM `wp` WHERE `id` = '".$v."' and `weared`=1 and `uidp`='".$pers["uid"]."' LIMIT 1");
	if ($v){
	$r = all_params();
	foreach ($r as $a)
	if ($v[$a]) $pers[$a] -= $v[$a];
	$pers["hp"]-=5*$v["s4"];
	$pers["ma"]-=9*$v["s6"];
	if ($aq=aq($pers))
	sql ("UPDATE `users` SET ".$aq." WHERE `uid` = '".$uid."' ;");
	sql ("UPDATE wp SET weared=0 WHERE id=".$v["id"]."");
	}
 }


/////
function remove_all_weapons ()
 {
	GLOBAL $pers;
	$res = sql ("SELECT * FROM `wp` WHERE `weared` = 1 and uidp=".$pers["uid"]."");
	while($v = mysql_fetch_array ($res))
	{
	$r = all_params();
	foreach ($r as $a)
	if ($v[$a]) $pers[$a] -= $v[$a];
	$pers["hp"]-=5*$v["s4"];
	$pers["ma"]-=9*$v["s6"];
	}
	if ($aq=aq($pers))
	sql ("UPDATE `users` SET ".$aq." WHERE `uid` = ".$pers["uid"]." ;");
	sql	("UPDATE wp SET weared=0 WHERE uidp=".$pers["uid"]."");
 }

 ## new for new. :DD
 function remove_all_weapons_fight($user)
 {
	//GLOBAL $pers;
	$res = sql ("SELECT * FROM `wp` WHERE `weared` = 1 and uidp=".$user."");
	$personaj = sqla ("SELECT * FROM `users` WHERE uid=".$user."");
	while($v = mysql_fetch_array ($res))
	{
	$r = all_params();
	foreach ($r as $a)
	if ($v[$a]) $personaj[$a] -= $v[$a];
	$personaj["hp"]-=5*$v["s4"];
	$personaj["ma"]-=9*$v["s6"];
	}
	if ($aq=aq($personaj))
	sql ("UPDATE `users` SET ".$aq." WHERE `uid` = ".$personaj["uid"]." ;");
	sql	("UPDATE wp SET weared=0 WHERE uidp=".$personaj["uid"]."");
 }





function signum($x)
{
	if ($x>0) return 1;
	if ($x==0) return 0;
	if ($x<0) return -1;
}

function uncrypt($value)
{
	$a=0;
	$key = 754;
	for($i=0;$i<strlen($value);$i++)
	$a += (ord($value[$i])<<(($i+23)>>1)<<1)^($key^9+$i);
	$a %= 10000;
	$a = abs($a);
	if ($a<1000) $a+=2343;
	return $a;
}






function HIWORD($a)
{
	return $a>>16;
}
function LOWORD($a)
{
	return ($a<<16)>>16;
}
function TOHIWORD($a)
{
	return $a<<16;
}

function EqualValueOfSkill($skill)
{
// Для статов = 1, для мф = 10, для хп = 10, для кб = 10, для маны = 12, для умений = 1, для мирных умений = 5 , для удара  = 3
	if ($skill[0]=='s' and strlen($skill)==2) return 1;
	if ($skill[0]=='m' and strlen($skill)==3) return 10;
	if ($skill=='kb') return 10;
	if ($skill=='hp') return 10;
	if ($skill=='ma') return 12;
	if ($skill[0]=='s' and ($skill[1]=='b' or $skill[1]=='m') and strlen($skill)==3) return 1;
	if ($skill[0]=='s' and $skill[1]=='p' and strlen($skill)==3) return 5;
	if ($skill=='udmin' or $skill=='udmax') return 3;
	return 0;
}

function IsWearing($v)
{
// Одеваемая ли это вещь
	if ($v["type"]=='shlem' or $v["type"]=='orujie' or $v["type"]=='kolco' or $v["type"]=='bronya' or $v["type"]=='naruchi' or $v["type"]=='perchatki' or $v["type"]=='ojerelie' or $v["type"]=='sapogi' or $v["type"]=='poyas' or $v["type"]=='kam') return 1;
	return 0;
}
function _UserByUid($uid=0)
{
	if ($uid)
		return sqlr("SELECT user FROM users WHERE uid=".intval($uid));
	else
		return false;
}
function _UidByUser($user='')
{
	if ($user)
		{
			$user = str_replace("'","",$user);
			$user = str_replace("\\","",$user);
			return sqlr("SELECT uid FROM users WHERE smuser=LOWER('".$user."')");
		}
	else
		return false;
}





function type_names($tp)
{
	$r = types();
	return $r[$tp];
}



function sqla_id( $q )
{
    if (isset($q))
        return mysql_fetch_row(sql($q));
    else return false;
}

function sign_img($pers)
{
	if ( $pers['sign']=='watchers' ) return 'watch/'.$pers['clan_state'];
	else return $pers['sign'];
}

function pers_pack($p)
{// Ник|Уровень|Значек клана
	return $p['user'].'|'.$p['level'].'|'.sign_img($p);
}

?>