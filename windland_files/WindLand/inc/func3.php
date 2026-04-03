<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

## Редкие функции, используемые в узкой специфике


function build_go_string($locid,$time)
{
	$str = md5(strtoupper($time.$locid.count($locid)));
	$str = "onclick=\"top.goloc('".$locid."','".$str."')\"";
	return $str;
}

function transfer_log($type,$uid,$user,$money1,$money2,$title,$ip1,$ip2)
{
	$GLOBALS['db']->sql("INSERT INTO `transfer` ( `date` , `type` , `uid` , `who` , `transfer_in` , `transfer_out` , `title` , `ip1` , `ip2`)
	VALUES (
	'".tme()."', ".$type." ,'".$uid."', '".$user."', '".$money1."', '".$money2."', '".$title."', '".$ip1."' , '".$ip2."'
	);", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
}

function catch_user($uid)
{
	return $GLOBALS['db']->sqla("SELECT * FROM `users` WHERE `uid` = ".intval($uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
}

function loges_proffesions($price, $type, $um=0, $uid)
{
	GLOBAL $db;
	/*	1 - рыба проданная
		2 - шахта, ресурсы проданные
		3 - зелья сваренные
		4 - 
	*/
	$tm = tme()-(3600*24);
	$res = (int)$db->sqlr('SELECT `id` FROM `log_proffesions` WHERE `uid`='.$uid.' and `date`>'.$tm.' and `type`='.$type.' LIMIT 1;',__FILE__,__LINE__,__FUNCTION__,__CLASS__);
	if (!$res) $db->sql('INSERT INTO `log_proffesions` (`uid`, `date`, `type`, `price`, `um`) VALUES ('.$uid.', '.tme().', '.$type.', '.$price.', "'.$um.'");',__FILE__,__LINE__,__FUNCTION__,__CLASS__);
	else $db->sql('UPDATE `log_proffesions` SET `price`=`price`+'.$price.', `upd`=upd+1 WHERE `id`='.$res.';',__FILE__,__LINE__,__FUNCTION__,__CLASS__);
}

function IsWearing($v)
{
// Одеваемая ли это вещь
	if ($v["type"]=='shlem' or $v["type"]=='orujie' or $v["type"]=='kolco' or $v["type"]=='bronya' or $v["type"]=='naruchi' or $v["type"]=='perchatki' or $v["type"]=='ojerelie' or $v["type"]=='sapogi' or $v["type"]=='poyas' or $v["type"]=='kam') return 1;
	return 0;
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

function signum($x)
{
	if ($x>0) return 1;
	if ($x==0) return 0;
	if ($x<0) return -1;
}

?>