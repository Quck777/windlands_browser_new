<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################


########## Задаем константы для удобства
define('UID', $player->pers['uid']);
define('PASS', $player->pers['pass']);
define('OPTIONS', $http->_cookie('options'));
define('USER', $player->pers['user']);
define('GD_HUMANHEAL', 1); // - мгновенное выздравление после боев с людьми

# 
/*
$tmp = sqlr("SELECT COUNT(*) FROM p_auras WHERE uid=".$player->pers["uid"]." and special=5 and esttime>".tme());
$_TRVM = ($tmp)?1:0;
$tmp = sqlr("SELECT esttime FROM p_auras WHERE uid=".$player->pers["uid"]." and special=14 and esttime>".tme());
$_MINE = mtrunc($tmp - tme());
$tmp = sqlr("SELECT esttime FROM p_auras WHERE uid=".$player->pers["uid"]." and special=15 and esttime>".tme());
$_UMINE = mtrunc($tmp - tme());
$tmp = sqlr("SELECT esttime FROM p_auras WHERE uid=".$player->pers["uid"]." and special=17 and esttime>".tme());
$_STUN = mtrunc($tmp - tme());
*/
#
$GOOD_DAY = 0;
if (date("m")==1 && date("d")<=7)	$GOOD_DAY = GD_HUMANHEAL;
if (date("m")==2 && date("d")==23)	$GOOD_DAY = GD_HUMANHEAL;
if (date("m")==3 && date("d")==8)	$GOOD_DAY = GD_HUMANHEAL;
if (date("m")==5 && date("d")==1)	$GOOD_DAY = GD_HUMANHEAL;
if (date("m")==5 && date("d")==9)	$GOOD_DAY = GD_HUMANHEAL;
if (date("m")==9 && date("d")==6)	$GOOD_DAY = GD_HUMANHEAL;
$_NG = ( (date("m")==12 and date("d")>=13) or (date("m")==1 and date("d")<=15) ) ? 1 : 0;

// замутка с погодой
$world = $db->sqla('SELECT `weather`,`weatherchange` FROM `world`', __FILE__,__LINE__,__FUNCTION__,__CLASS__);
define("WEATHER", $world["weather"]);

// хз нафига этот блок
$d = date("H");
if ($d>22 or $d<6) define("DAY_TIME",0);
elseif ($d<12) define("DAY_TIME",1);
elseif ($d<18) define("DAY_TIME",2);
else define("DAY_TIME",3);
unset($d);

## Блок который должен выполнятся только для юзера лично
if (@!$DONT_CHECK)
{
	include (ROOT. '/inc/goloc.php');
	include (ROOT. '/inc/inc/ap.php');
	include (ROOT. '/inc/inc/econom.php');
	include (ROOT. '/inc/inc/wears.php');
//	remove_all_auras();

	if ($player->pers["cfight"]>10 and $player->pers["curstate"]<>4) set_vars("cfight=0,refr=1",UID);
	if ($player->pers["cfight"]<10 and $player->pers["curstate"]==4) set_vars("curstate=2,cfight=0,refr=1",UID);
	if ($player->pers["curstate"]==4) include(ROOT.'/inc/inc/battle.php');
}
// ^ Нападения бота
//Вытаскивание из бага


$f = explode("|",$http->_cookie("filter1"));
$_FILTER["lavkatype"] = $f[0];
$_FILTER["lavkaminlevel"] = $f[1];
$_FILTER["lavkamaxlevel"] = $f[2];
$_FILTER["lavkamaxcena"] = $f[3];
$_FILTER["lavkasort"] = $f[4];
$_FILTER["sort"] = $f[5];
$_FILTER["h_zn_show"] =$f[6];
$_FILTER["show_z"]=$f[7];
$_FILTER["sorti"]=$f[8];
$_FILTER["sortp"]=$f[9];
$_FILTER["apps"]=$f[10];
$_FILTER["cat"]=$f[11];
$_FILTER["ar_loc"]=intval($f[12]);
$_FILTER["filter_f6"]=intval($f[13]);
$_FILTER["pers_sort"]=$f[14];
unset($f);

if ($player->pers["priveleged"]) $priv = $db->sqla("SELECT * FROM `priveleges` WHERE uid=".UID, __FILE__,__LINE__,__FUNCTION__,__CLASS__);

/*
########Новый год
if($player->pers["level"]>0 and $player->pers["new_year"]>1 and $player->pers["new_year"]<(tme()) and $player->pers["curstate"]!=4)
{
	$bts = sqla("SELECT * FROM bots WHERE level=".($player->pers["level"])." and special=1;");
	$bb = "bot=".$bts["id"];
	begin_fight ($player->pers["user"],$bb,"Нападение деда мороза",50,900,1,0,0,1,1);
	set_vars("new_year=0",$player->pers["uid"]);
}
*/
?>