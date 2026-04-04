<?php
##############################

echo"
<table width=100% cellspacing=0 cellpadding=3 border=0>
<td align=center valign=top>
<font style='FONT-FAMILY: Arial; COLOR=RED; FONT-SIZE: 16pt;'><b>Почтовое отделение</b></font><br>
</td>
</table>";


$unread = $db->sql("SELECT * FROM `pochta` WHERE `whom` LIKE '".$pers[user]."' AND `read` = 0 " );
$poch = $db->sql("select * from pochta where whom='".$pers[user]."' ORDER by ID DESC");
$send = $db->sql("select * from pochta where user='".$pers[user]."' ORDER by ID DESC");

echo"<body bgcolor=#EBEDEC leftmargin=0 topmargin=0>";

echo"<DIV id=hint1></DIV>";

print"<table width=100% cellspacing=0 cellpadding=5 border=0>
<tr>
<td valign=top width=150 nowrap>
<FIELDSET style='WIDTH: 120'><legend><font class=player>Папки</font></legend>
<a href=?act=new>Написать </a><br>
<a href=?act=read>Входящие (".mysql_num_rows($unread)." / ".mysql_num_rows($poch)." )</a><br>
<a href='?act=write'>Исходяшие</a><br>

</td>

<td width=100% valign=top><FIELDSET style='WIDTH: 98.6%'><legend><font class=player>Письма</font></legend><center>Добрый вечер, вы в разделе <b>Почта</b></center><br>1. Для того что бы написать письмо нажмите на вкладку <b>Написать</b> <i>(Услуга отправки стоит 3 LN)</i><br><br>2. Для того что бы прочитать письмо нажмите на вкладку <b>Входящие</b><br><br>3. Для просмотра отправленных вами писем, нажмите на вкладку <b>Исходящие</b><br><br>";



if ($act=="read") {
	echo "
		<table width=100% cellspacing=0 cellpadding=7 border=1 bordercolor=CCCCCC>
		<tr><td><b>№</td><td><b>Отправитель</td><td width=100%><b>Тема</td></tr>
	";
	while ($pochta = mysql_fetch_array($poch) ) {
		$i++;
		$user=$pochta["user"];
		$text=$pochta["subject"];
		$id=$pochta["id"];
		if ($pochta[read]==0) {$read="<b>";}
		else {$read="";}
		print "<tr style='CURSOR: Hand' onclick='window.location.href=\"?act=let&id=$id\"'><td>$read$i</td><td nowrap>$read$user</td><td>$read$text </td></tr>";
	}
	echo "</table>";
}
$mny=$pers["user"];
if ($act=="let") {
	$pochas = $db->sql("select * from pochta where id='$id' ORDER by ID DESC");
	$let = mysql_fetch_array($pochas);
	$text=$let["text"];
	$subj=$let["subject"];
	$user=$let["user"];
	$who=$let["whom"];
	echo "<b>От:</b> $user ";
 echo "<br>
		<b>Тема:</b> $subj<br>
		<b>Текст:</b><br>$text";
	if ($mny=="$who") {
	$db->sql("UPDATE `pochta` SET `read` = '1' WHERE `id` = '$id' ");
	}
}

if ($act=="write") {
	echo "
		<table width=100% cellspacing=0 cellpadding=7 border=1 bordercolor=CCCCCC>
		<tr><td><b>№</td><td><b>Кому</td><td width=100%><b>Тема</td></tr>
	";
	while ($pochta = mysql_fetch_array($send) ) {
		$i++;
		$user=$pochta["whom"];
		$text=$pochta["subject"];
		$id=$pochta["id"];
		if ($pochta[read]==0) {$read="<b>";}
		else {$read="";}
		print "<tr style='CURSOR: Hand' onclick='window.location.href=\"?act=let&id=$id\"'><td>$read$i</td><td nowrap>$read$user</td><td>$read$text </td></tr>";
	}
	echo "</table>";
}

if ($act=="new") {
?>

<form name=add action=?act=new&do=3 method="POST">Написать письмо:
<br>Тема<br>
<?
$subj=htmlspecialchars($subj);
echo '<input type=text name=subj class=new size=30>
';
?>
<br>Кому<br>
<?
$whom=htmlspecialchars($whom);
echo '<input type=text name=target class=new size=30>
';
?>
<br>
Текст письма<br>
<?
$text=htmlspecialchars($text);
echo '<textarea name=text rows=7 cols=51></textarea>
';
?>
<br>
<input type=submit value="Создать" class=new>
</form>
<?
if ($do=="3") {
	if ($pers["money"]<1) {echo"У вас недостаточно денег!"; die(); }
	else {
		$kolvo=3; //Цена отправки почты
		$sql ="INSERT INTO pochta(user,whom,text,subject) VALUES ('".$pers[user]."','$target','$text','$subj')";
		$result = $db->sql($sql);


		$cr=$pers[money]-$kolvo;
		$db->sql("UPDATE `users` SET `money` = '$cr' WHERE `uid`='".$_SESSION["uid"]."';");
	            $infs = $db->sql("select * from users where user='$target'");
	            $info = mysql_fetch_array($infs);



		print "Письмо $subj успешно отправлено персонажу $target";

                                          say_to_chat ("s","С вашего счета было снято <b>$kolvo KRN</b>","1",$pers["user"],$pers["location"],date("H:i:s"));
                                          say_to_chat ("s","<b>Получено новое сообщение!</b>","1",$info["user"],$pers["location"],date("H:i:s"));
		print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; URL=?act=new\">";
	}
}
}



echo"

</td>
</tr>
</table>


<BR><BR>
";


?>