<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

include ($_SERVER['DOCUMENT_ROOT'].'/configs/config.php');
$db = new MySQL(SQL_USER, SQL_PASS, SQL_BASE);
include (ROOT.'/inc/class/http_check_v2.php');
$http = new Jhttp;
##############################

include (ROOT.'/inc/class/Player.php');
$player = @$http->_post('pass') ? new Player(false, false, false, true) : new Player;

if ( !$player->pers ) exit;

// Логирование входа с разных аккаунтов
if ( $http->_cookie('uid')!=$player->pers['uid'] and $http->_cookie('uid')!=0 and intval($player->pers['uid']) ) {
    $db->sql("INSERT INTO `logs_one_comp_logins` (`uid1`,`uid2`,`time`) VALUES (".intval($http->_cookie('uid')).", ".intval($player->pers['uid']).", '".tme()."');");
}

// Установка кук
$http->setCook('uid', $player->pers['uid'], true);
$http->setCook('hashcode', $player->pers['pass'], true);
$http->setCook('nick', $player->pers['user']);
$http->setCook('options', $player->pers['options']);
if ( $player->pers['uid']==7 ) $http->setCook('AdminJoe', 1);

// Подключаем функции
include (ROOT.'/inc/func.php');

// Логирование IP
$db->sql("INSERT INTO `logs_ips_in` ( `uid` , `ip` , `date`, `brouser`) VALUES (".$player->pers['uid'].",'".$http->is_ip()."',".tme().", '".$http->is_br(true)."');");

$chlast = intval($db->sqlr("SELECT MAX(id) FROM `chat`", 0));
$db->sql("UPDATE `users` SET `lastip` = '".$http->is_ip()."', `lastvisit`='".date("d.m.Y H:i", tme())."', `lastvisits`=".(tme()).", `lasto`='".(tme())."',`online`=1, `chat_last_id`=".$chlast." WHERE `uid`='".$player->pers['uid']."'");

$today = getdate();
?>
<HTML>
<HEAD>
<TITLE>Земля Ветров [<?=$player->pers['user'];?>]</TITLE>
<META Content='text/html; charset=windows-1251' Http-Equiv=Content-type>
<LINK href='css/main_v2.css' rel=STYLESHEET type=text/css>
<LINK href='css/modern_game.css' rel=STYLESHEET type=text/css>
</HEAD>
<BODY scroll=no style='overflow:hidden;'>
<SCRIPT LANGUAGE='JavaScript' SRC='js/cookie.js'></SCRIPT>
<SCRIPT LANGUAGE='JavaScript' SRC='js/mod/jquery.js'></SCRIPT>
<SCRIPT LANGUAGE='JavaScript' SRC='js/game_v2.js?2'></SCRIPT>
<SCRIPT>
var img_pack = '<?=IMG;?>';
var hours = "<?=$today['hours'];?>";
var minutes = "<?=$today['minutes'];?>";
var seconds = "<?=$today['seconds'];?>";
var ctip = "<?=$player->pers['ctip'];?>";
SoundsOn = "<?=($player->pers['sound']?0:1);?>";
view_frames();
</SCRIPT>
<NOSCRIPT>
<b>Внимание!</b><br>
Для корректной работы игры необходим JavaScript.<br>
Пожалуйста, включите JavaScript в настройках браузера.
</NOSCRIPT>
</BODY>
</HTML>
