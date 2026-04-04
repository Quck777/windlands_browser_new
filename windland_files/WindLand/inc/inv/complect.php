<?php
if ( !$db->sqlr("SELECT COUNT(*) FROM chars WHERE uid='".UID."'") )
{
	$db->sql("INSERT INTO `chars` (`uid`) VALUES ('".UID."');");
}

$chars = $db->sqla('SELECT `complects` FROM `chars` WHERE `uid` = '.UID.';');

if ( $http->_post('name') )
{	
	$wears = $db->sql("SELECT id FROM wp WHERE weared=1 and uidp=".UID."");
	$perswears = '';
	while ($w = mysql_fetch_row($wears)) $perswears.= $w[0].'|';
	$chars["complects"].= addslashes($http->post["name"]).":".$perswears."@";
	$db->sql('UPDATE `chars` SET `complects`= "'.$chars['complects'].'" WHERE `uid` = '.UID.';');
}

if ( $http->post["do"]=="del" )
{
	$cc = explode("@",$chars["complects"]);
	$cc = $cc[$http->post["c"]];
	$chars["complects"] = str_replace($cc."@","",$chars["complects"]);
	$db->sql('UPDATE `chars` SET `complects` = "'.$chars['complects'].'" WHERE `uid` = '.UID.';');
}

echo "<center><br><table width=80% class=inv_but><tr><td align=center><form method=post>Название:<input type=text name=name class=login> <input class=login type=submit value='Запомнить текущий комплект' style='width:100%'></form></td></tr></table></center>";

if ( !empty($chars["complects"]) and $chars["complects"]<>'@' )
{
	echo "<hr><form method=post><table border=0>";
	$pres = explode ("@",$chars["complects"]);
	$i=0;
	foreach($pres as $p)
	{
		if ($p<>'')
		{
			echo "<tr>";
			$z = explode(":",$p);
			echo '<td class=but>'.$z[0].'</td><td><input type="radio" name="c" value="'.$i.'"></td>';
			$i++;
			echo"</tr>";
		}
	}
	echo "</table>";
	echo '<hr><center>Надеть:<input type="radio" name="do" value="wear" /> | Удалить:<input type=radio name="do" value="del" /><br><input type="submit" class="login" value="Ок" style="width:200px" /></center></form>';
	// onclick=\"document.getElementById('sbmt123').disabled = true;\" id=sbmt123

} else echo "<hr>У вас нет комплектов.";


if ($http->get["action"]=="delete")
	$db->sql('UPDATE `chars` SET `presents`= "'.$chars['presents'].'" WHERE `uid`= '.UID.';');



?>