<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
if (defined('PINFO')==false) exit;

include_once(ROOT.'/inc/inc/auras.php');

	# Обнуляем
	if ($player->pers['action']==-10)
	{
		$db->sql("UPDATE `p_auras` SET `esttime`=0, `turn_esttime`=0 WHERE `uid`='".$player->pers['uid']."'");	
		$player->remove_all_auras();
		remove_all_weapons();
		$db->sql ("UPDATE `users` SET `s1` =1, `s2` =1, `s3` =1, `s4` =1, `s5` =1, `s6` =1, `free_stats` =14, mf1=0, mf2=0, mf3=0, mf4=0, mf5=0, `level` =0, `udmin`=1, `udmax`=1, `hp`=5, `ma`=9, `kb`=0, `sb1`=0,`sb2`=0,`sb3`=0,`sb4`=0,`sb5`=0,`sb6`=0,`sb7`=0,`sb8`=0,`sb9`=0,`sb10`=0,`sb11`=0,`sb12`=0,`sb13`=0,`sb14`=0,`sm1`=0,`sm2`=0,`sm3`=0,`sm4`=0,`sm5`=0,`sm6`=0,`sm7`=0, `free_f_skills` =5, `free_m_skills` =5, `refr`=1, `aura`='', `action`=0 WHERE `uid`='".$player->pers['uid']."'");
		$player->pers['action'] = 0;
	}
	elseif ($player->pers['action']==-11)
	{
		$db->sql("UPDATE `p_auras` SET `esttime`=0, `turn_esttime`=0 WHERE `uid`='".$player->pers['uid']."'");	
		$player->remove_all_auras();
		remove_all_weapons ();
		$db->sql("DELETE FROM `wp` WHERE `uidp`=".$player->pers['uid']." and `dprice`=0 and `clan_sign`=''");
		$db->sql("UPDATE `wp` SET `weared`=0 WHERE `uidp`=".$player->pers['uid'].";");
		$db->sql("UPDATE `users` SET `s1` =1, `s2` =1, `s3` =1, `s4` =1, `s5` =1, `s6` =1, `free_stats` =14, mf1=0, mf2=0, mf3=0, mf4=0, mf5=0, `level` =0, `udmin`=1, `udmax`=1, `hp`=5, `ma`=9, `kb`=0, sb1=0,sb2=0,sb3=0,sb4=0,sb5=0,sb6=0,sb7=0,sb8=0,sb9=0,sb10=0,sb11=0,sb12=0,sb13=0,sb14=0,sm1=0,sm2=0,sm3=0,sm4=0,sm5=0,sm6=0,sm7=0, `free_f_skills`=5, `free_m_skills`=5, `refr`=1, `aura`='', `exp`=0, `losses`=0, `victories`=0, `peace_exp`=0, `action`=0, `money`=0 WHERE `uid`=".$player->pers['uid']."");
		if($player->pers['level']<2) 
			$db->sql("UPDATE `users` SET coins=0 WHERE `uid`=".$player->pers['uid']."");
		$db->sql("UPDATE `bank_account` SET `money`=0 WHERE `uid`=".$player->pers['uid']."");
		$player->pers['action'] = 0;
	}
	# Обновляем
	$pers = catch_user ($player->pers['uid']);

//Получение уровня
$level = $db->sqla("SELECT * FROM `exp` WHERE `level`=".($player->pers['level']+1));
$i = 0;
$free_stats = 0;
$free_f_skills = 0;
$free_p_skills = 0;
$free_m_skills = 0;
$levels = 0;


while (($player->pers['exp']+$player->pers['peace_exp'])>=$level['exp'] and $level['exp']>0) 
{
	$free_stats +=$level['stats'];
	$free_f_skills+=$level['free_f_skills'];
	$free_p_skills+=$level['free_p_skills'];
	$free_m_skills+=$level['free_m_skills'];
	$levels++;
	$level = $db->sqla("SELECT * FROM `exp` WHERE `level`=".($level['level']+1));
	$i++;
}

if ($i>0)
{
	$player->pers['level']+=$levels;
	$player->pers['free_stats']+=$free_stats;
	$player->pers['free_f_skills']+=$free_f_skills;
	$player->pers['free_p_skills']+=$free_p_skills;
	$player->pers['free_m_skills']+=$free_m_skills;
	$db->sql("UPDATE `users` SET `level`=level+".$levels.", `free_stats`=free_stats+".$free_stats.", `free_f_skills`=free_f_skills + ".$free_f_skills.", `free_m_skills`=free_m_skills+".$free_m_skills." WHERE `uid`='".$player->pers['uid']."'");
}


	$player->pers['chp'] = 5;
	$player->pers['сma'] = 9;


?>