<?php
##############################
php

$level = 3;
$dn = mktime(0, 0, 0, 2, 13, 2011);
$dk = mktime(0, 0, 0, 3, 1, 2011);

$priz = 100;
$priz2= 50;

$res = $db->sql('SELECT * FROM `log_referals` WHERE `level`>'.($level-1).' and (`date`>'.$dn.' or `date`<'.$dk.') ORDER BY `uid`;');

$rss = Array();
while ( $r = mysql_fetch_assoc($res) )
{
	$p = $db->sqla('SELECT `user`,`level` FROM `users` WHERE `uid`='.$r['whoref'].' and `block`=""');
	if ($p==false) continue;
	if ( $rss[$r['whoref']]==true )
	{
		$cn = $rss[$r['whoref']]['count']+1;
		$cl = $rss[$r['whoref']]['lvl']+$r['level'];
		
	} else {$cn = 1; $cl = $r['level'];}
	$rss[$r['whoref']] = Array('count'=>$cn, 'lvl'=>$cl, 'usr'=>$p['user'], 'ulvl'=>$p['level']);
}


array_multisort($rss, SORT_DESC);
array_multisort($rss, '', SORT_DESC);


echo "<div class=but2><b>Внимание конкурс!</b><br />
Кто приведёт больше людей в игру по своей реферальной ссылке (<b class=ma>http://".HTTP."/?".UID."</b>) и они достигнут  хотя бы <b>".$level."-го</b> уровня с <b>".date('d.m.Y',$dn)."</b> до <b>".date('d.m.Y',$dk)."</b>,<br />тот получит <b>".$priz." Бр</b>! Второе место получает ".$priz2." Бр.<br />
Лидеры:<table class=but width=100%>";
$i=0;
echo '<tr><td>#</td><td>Персонаж</td><td>Число рефералов</td><td>Общий уровень</td></tr>';
foreach ($rss as $r)
{
	$i++;
	if ($i>5) continue;
	echo '<tr><td>'.$i.')</td><td class=user width=50%>'.$r['usr'].'[<font class=lvl>'.$r['ulvl'].'</font>]<a href="/info.php?'.$r['usr'].'" target="_blank"><img src="http://'.IMG.'/i.gif" style=cursor:pointer></a></td>  <td>'.$r['count'].'</td><td class=user>'.$r['lvl'].'</td></tr>';
}
echo "</table></div>";
?>