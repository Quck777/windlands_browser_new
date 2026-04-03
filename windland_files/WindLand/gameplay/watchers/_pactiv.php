<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
if ($p24<>1) exit;



	$res = $db->sql("SELECT * FROM `log_proffesions` WHERE `uid`=".$player->pers["uid"]." ORDER BY `date` DESC");
	
	echo '<table border="1" cellspacing="0" cellpadding="0" bordercolorlight=#C0C0C0 bordercolordark=#FFFFFF bgcolor=#F5F5F5 align=center>';
	$dur = Array();
	$dur[1] = 0;
	$dur[2] = 0;
	$dur[3] = 0;
	$dur[4] = 0;
	$dur[5] = 0;
	while($rs = mysql_fetch_assoc($res))
	{
		if ($rs['type']==1) {$color = '#FFDDDD';$tt = 'РЫБАЛКА';}
		if ($rs['type']==2) {$color = '#FFEEEE';$tt = 'ШАХТЕРСТВО';}
		if ($rs['type']==3) {$color = '#DDAAAA';$tt = 'АЛХИМИЯ';}
		/*
		if ($rs['type']==9) {$color = '#EEBBBB';$tt = 'Снято БЛОК';}
		if ($rs['type']==3) {$color = '#AADDAA';$tt = 'ТЮРЬМА';}
		if ($rs['type']==8) {$color = '#BBEEBB';$tt = 'Снято Тюрьма';}
		if ($rs['type']==4) {$color = '#AAAADD';$tt = 'КАРА';}
		if ($rs['type']==10) {$color = '#BBBBEE';$tt = 'Снято КАРА';}
		if ($rs['type']==5) {$color = '#AAAAFF';$tt = 'БЛОК ИНФЫ';}
		*/
		$dur[$rs['type']] += $rs['price'];
		echo "<tr>";
		echo "<td bgcolor=".$color." class=timef>&nbsp;".date("d.m.y H:i",$rs['date'])."&nbsp;</td>";
		echo "<td class=items>&nbsp;".$tt."&nbsp;</td>";
		echo "<td class=timef>&nbsp;".round($rs['price'])." LN&nbsp;</td>";
		echo "<td class=return_win>&nbsp;".$rs['um']." умения&nbsp;</td>";
		echo "<td class=items>&nbsp;".$rs['upd']." действий&nbsp;</td>";
		echo "</tr>";
	}
	echo '</table>';
	echo "Суммарный заработок:";
	echo "<br /><b>РЫБАЛКА</b>: ".round($dur[1])." LN";
	echo "<br /><b>ШАХТЕРСТВО</b>: ".round($dur[2])." LN";
	echo "<br /><b>АЛХИМИЯ</b>: ".round($dur[3])." LN";
	echo "<hr />";
?>