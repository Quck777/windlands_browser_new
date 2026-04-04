<?php
if ($rk[51]==0 and $status!='wg') exit('Доступ запрещен.');

$pd = $db->sqlr('SELECT `id` FROM `watch_req_werif` WHERE `whoid`='.UID.' and `date_closed`=0;');


# Берем себе дело
if ( isset($http->get['mid']) and $pd==false)
{
	$mid = intval(abs($http->get['mid']));
	$db->sql('UPDATE `watch_req_werif` SET `whoid`='.UID.', `who`="'.pers_pack($player->pers).'"  WHERE `id`='.$mid.' and `whoid`=0;');
	$pd = $mid;
}

# Сохраняем историю
if ( isset($http->post['save']) )
{
	$id = intval(abs($http->post['save']));
	$txt = $http->post['wttext'];
	$db->sql('UPDATE `watch_req_werif` SET `text`="'.$txt.'" WHERE `id`='.$id.' and `date_closed`=0;');
}
# Проверка пройдена
if ( isset($http->get['ok']) )
{
	$id = intval(abs($http->get['ok']));
	$db->sql('UPDATE `watch_req_werif` SET `date_closed`='.tme().', `result`=1 WHERE `id`='.$id.' and `date_closed`=0 and `text`<>"";');
	$pd = 0;
}
# Проверка не пройдена
if ( isset($http->get['no']) )
{
	$id = intval(abs($http->get['no']));
	$db->sql('UPDATE `watch_req_werif` SET `date_closed`='.tme().', `result`=2 WHERE `id`='.$id.' and `date_closed`=0 and `text`<>"";');
	$pd = 0;
}

echo '<SCRIPT>
function wt_delo(id)
{
	location = "main.php?w=werf&dlr="+id;
}
</SCRIPT>';

echo '<table width="90%" border="1" cellspacing="0" cellpadding="0" class="but">';
if ($pd>0)
{
	$pd = $db->sqla('SELECT `id`,`uid`,`text` FROM `watch_req_werif` WHERE `id`='.$pd.';');
	$p = $db->sqlr('SELECT `user` FROM `users` WHERE `uid`='.$pd['uid']);
	echo '<form action=main.php?w=werf method=post><input type="hidden" name="save" value="'.$pd['id'].'">';
	echo '<tr>
		<td><textarea name="wttext" class="inv_button" cols="50" rows="5">'.$pd['text'].'</textarea></td>
		<td width="100%">
		<table width="100%">
			<tr><td>&nbsp;Вы проверяете персонажа&nbsp;<span class=user>'.$p.'</span> <a href="info.php?'.$p.'" target="_blank"><img src=http://'.IMG.'/i.gif></a>&nbsp;</td></tr>
			<tr><td><input type="submit" value="Сохранить дело" class="login"></td></tr>
			<tr><td><a href="#" onclick="if(confirm(\'Персонаж прошел проверку?\'))location=\'main.php?w=werf&ok='.$pd['id'].'\'" class="bga">Проверка пройдена успешно</a></td></tr>
			<tr><td><a href="#" onclick="if(confirm(\'Персонаж пе прошел проверку на чистоту?\'))location=\'main.php?w=werf&no='.$pd['id'].'\'" class="bga">Проверка не пройдена</a></td></tr>
		</table>
		</td></tr>';
	echo '</form>';
	
} elseif ( isset($http->get['dlr']) ) {
	$dl = $db->sqla('SELECT * FROM `watch_req_werif` WHERE `id`='.abs(intval($http->get['dlr'])).';');
	echo '<tr>';
	echo '<td><div class="inv_button">'.$dl['text'].'</div></td>';
	echo '</tr>';
} else {
	$res = $db->sql('SELECT * FROM `watch_req_werif` WHERE `uid`>0 ORDER BY CONCAT(`date`, `type`) DESC;');
	
	echo '<tr align="center"><td width="115">Дата</td><td>Клиент</td><td>Обработчик</td><td>Результат</td><td>Дата закрытия</td></tr>';
	while ( $rs = mysql_fetch_assoc($res) )
	{
		switch ($rs['type'])
		{
			case 1: $color = '#FFDDDD'; break;
			case 2: $color = '#DDAAAA'; break;
		}
		echo '<tr align="center">';
			$tt = explode('|', $rs['uidp']);
			echo '<td bgcolor='.$color.' class=timef width="115">&nbsp;'.date('d.m.y H:i:s', $rs['date']).'&nbsp;</td>';
			echo "<td class=user>&nbsp;".(($tt[2]!='none') ? '<img src=http://'.IMG.'/signs/'.$tt[2].'.gif>' : '').$tt[0]."[".$tt[1]."] <a href='info.php?".$tt[0]."' target=_blank><img src=http://".IMG."/i.gif></a>&nbsp;</td>";
			if ($rs['whoid']>0)
			{
				$p = explode('|', $rs['who']);
				echo "<td class=user>&nbsp;".(($p[2]!='none') ? '<img src=http://'.IMG.'/signs/'.$p[2].'.gif>' : '').$p[0]."[".$p[1]."] <a href='info.php?".$p[0]."' target=_blank><img src=http://".IMG."/i.gif></a>&nbsp;</td>";
			} else echo '<td class="timef">&nbsp;<a href="main.php?w=werf&mid='.$rs['id'].'" class="bga">Взять дело</a>&nbsp;</td>';
			if ( $rs['date_closed']>0 )
			{
				echo '<td class=timef>&nbsp;'.(($rs['result']==1) ? '<span class="inv_green" onClick="wt_delo('.$rs['id'].');">Проверка пройдена</span>' : '<span class="inv_red" onClick="wt_delo('.$rs['id'].');">Проверка не пройдена</span>').'&nbsp;</td>';
				echo "<td class=return_win>&nbsp;".date("d.m.y H:i:s", $rs['date_closed'])."&nbsp;</td>";
			} else echo "<td class=timef>&nbsp;Не проверено&nbsp;</td><td class=timef>&nbsp;Открыто&nbsp;</td>";
		
		echo '</tr>';
	}
}
echo '</table>';
?>

<br /><br /><div class="but">
*После успешной проверки необходимо так же поставить проверку в возможностях персонажа.
</div>