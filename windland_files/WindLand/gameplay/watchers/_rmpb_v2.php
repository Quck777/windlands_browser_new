<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
if ($p23<>1) exit;
/*
1 - молчанка
2 - снатая молчанка

3 - кара
4 - снятая кара

5 - тюрьма
6 - снятая тюрьма

7 - блок
8 - снятый блок
*/
$molch  = '';
$kara   = '';
$blocks = '';



$molch = $db->sql('SELECT `date`,`type`,`reason`,`who` FROM `watch_punishments` WHERE `uid`='.$player->pers['uid'].' and (`type`=1 or `type`=2) ORDER BY `date`;');
$r = '';
while ($m = mysql_fetch_assoc($molch))
{
	$d = str_replace(' ','&nbsp;', date('d.m.y H:i:s',$m['date']));
	$r.= $d.'|'.$m['type'].'|'.$m['reason'].'|'.$m['who'].'@';
}
$molch = $r;


?>
<SCRIPT language="JavaScript">
rmpb('<?php echo $molch;?>','<?php echo $kara;?>','<?php echo $blocks;?>');
</SCRIPT>