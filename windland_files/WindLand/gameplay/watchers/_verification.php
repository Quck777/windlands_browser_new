<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
if ($p21<>1) exit;

	$wwer = $db->sql("SELECT * FROM `watch_verification` WHERE `uid`=".$player->pers['uid']."  ORDER BY `date` DESC");
	echo '<table border="1" cellspacing="0" cellpadding="0" bordercolorlight=#C0C0C0 bordercolordark=#FFFFFF bgcolor=#F5F5F5 align=center>';
	$i = 0;
	while( $ww = mysql_fetch_assoc($wwer) )
	{
		$i++;
		if ($ww['type']==1)
		{
			$wrtupe = 'Коммерческая проверка';
			$clr = '#FFDDDD';
		} else {$wrtupe = 'Проверка Смотрителей'; $clr = '#DDFFDD';}
		
		echo "<tr>";
		echo "<td bgcolor=".$clr." class=timef>&nbsp;".date("d.m.y H:i:s",$ww['date'])."&nbsp;</td>";
		echo "<td class=user>&nbsp;".$ww['who']." <a href='info.php?".$ww['who']."' target=_blank><img src='/images/_i.gif'></a>&nbsp;</td>";
		echo "<td class=return_win>&nbsp;".$wrtupe."&nbsp;</td>";
		echo "<td bgcolor=#BBBBEE class=timef>&nbsp;".date("d.m.y H:i:s",$ww['date']+432000)."&nbsp;</td>";
		echo "</tr>";
	}
	if ($i==0) echo 'Нет проверок.';
	echo '</table>';
	unset($ww);
?>