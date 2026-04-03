<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<center class="inv">
<a class=bga href=main.php?go=administration>Назад</a>
<?php
if (@$_GET["delete"] and $_GET["delete"]<>$pers["uid"] and $priv['emain']==2)
{
	$db->sql("UPDATE `users` SET `priveleged`=0 WHERE `uid`=".intval($_GET["delete"]));
	$db->sql("DELETE FROM `priveleges` WHERE `uid`=".intval($_GET["delete"]));
}
if (@$_GET["edit"] and $priv['emain']==2)
{
	$p = $db->sqla("SELECT * FROM priveleges WHERE uid=".intval($_GET["edit"])."");
	echo "<form action=main.php?sedit=".$p["uid"]." method=post>";
	echo "<ul>";
	foreach ($p as $key=>$value)
	{
		if (is_string($key) and $key<>'uid')
		{
			echo "<li>".$key." : <input class=laar type=text value=".$value." name='".$key."'></li>";
		}
	}
	echo "</ul>[0 - не доступно. 1 - просмотр. 2 - изменение]";
	echo "<input class=login type=submit value='Сохранить'>";
	echo "</form><br />";
}
if (@$_GET["sedit"] and @$_POST and $priv['emain']==2)
{
	$q = '';
	foreach($_POST as $key=>$value)
	{
		$key = str_replace (" ","",$key);
		$value = str_replace("'","",$value);
		$q .= "`".$key."`='".$value."',";
	}
	$q = substr($q,0,strlen($q)-1);
	$db->sql("UPDATE priveleges SET ".$q." WHERE uid=".intval($_GET["sedit"]));
}
if (@$_POST['go_in'] and $priv['emain']==2)
{
	$p = $db->sqlr("SELECT `uid` FROM `users` WHERE `user`='".$_POST['go_in']."'",0);
	if ($p)
	{
	$db->sql("INSERT INTO `priveleges` ( `uid` , `emap` , `ewp` , `emagic` , `ebots` , `eusers` , `emain` , `emedia` , `status` ) 
VALUES ('".$p."', '0', '0', '0', '0', '0', '0', '0', 'Министр');");
	$db->sql("UPDATE users SET priveleged=1 WHERE uid=".$p."");
	}
}
unset($p);
?>
<table border="1" width="95%" cellspacing="0" cellpadding="0" bordercolorlight="#C0C0C0" bordercolordark="#FFFFFF">
<tr> <td></td> <td>НИК</td> <td>Карта</td> <td>Вещи</td> <td>Магия</td> <td>Боты</td> <td>Население</td> <td>Министры</td> <td>Медиа</td> <td>Квесты</td> <td>Масс-медиа</td> <td>Кланы</td> <td>Должность</td> </tr>
<?php


$m = $db->sql("SELECT `uid`,`user` FROM `users` WHERE `priveleged`=1");
while($p = mysql_fetch_assoc($m))
{
	$prv = $db->sqla("SELECT * FROM `priveleges` WHERE `uid`=".$p['uid']);
	echo"<tr><td>";
	if ( $p['uid']<>$pers['uid'] and $priv['emain']==2 )echo "<a href='javascript:if(confirm(\"Вы действительно хотите исключить ".$p["user"]." из кабинетa министров?\")) location=\"main.php?delete=".$p["uid"]."\";'><img src=http://".IMG."/icons/del.png></a> <a href=main.php?edit=".$p["uid"]."><b><img src=http://".IMG."/icons/edit.png></b></a>";
	echo"</td><td><img src=http://".IMG."/signs/admin/".$prv['level'].".png> <font class=user>".$p['user']."</font></td>";
	foreach ($prv as $key=>$value)
	{
		if (is_string($key))
		{
		if ($value and $key<>'status' and $key<>'uid' and $key<>'level') echo "<td class=green>ДА[".$value."]</td>";
		elseif ($key<>'status' and $key<>'uid' and $key<>'level') echo "<td class=hp>НЕТ</td>";
		}
	}
	echo"<td class=login>".$prv['status']."</td></tr>";

} echo '</table>';

if ($priv['emain']==2 and (UID==1 or UID==7))
echo'<br /><table border="0" width="100%" style="border-style: solid; border-width: 1px; border-color: #777777" cellspacing="1">
<tr>
		<td bgcolor="#F0F0F0" class="td"><form method="POST" action=main.php>
<p align="right">
<input name=go_in size=100 class=laar style="float: left"> 
<input type="submit" value="Принять" class=inv_but></p>
		</form></td>
	</tr></table>';
?>