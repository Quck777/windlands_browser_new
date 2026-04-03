<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

define('MICROLOAD', true);
// Загружаем файл конфига, ВАЖНЫЙ.
include ($_SERVER['DOCUMENT_ROOT'].'/configs/config.php');
// Подключаемся к SQL базе
$db = new MySQL(SQL_USER, SQL_PASS, SQL_BASE);

$pers = $db->sqla("SELECT `sign` FROM `users` WHERE `uid`=".intval($_COOKIE["uid"]));
?><script type="text/javascript" src="/js/but_v3.js"></script>
<script><?php
echo "var jtime = [".date('H').",".date('i').",".date('s')."];";
echo "show_buttons('".(($pers['sign']<>'none') ? 1 : 0)."');";
?></script>