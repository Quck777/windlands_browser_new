<?
####// Главная таблица
	echo "<table border=0 style='width:100%;background-image:url(\"images/bg.png\");' cellspacing=0 cellpadding=0><tr><td valign=top align=center style='width:250px;background-color:#EEEEEE;'>";
####//
	echo show_pers_in_f($player->pers,3);
####//
	echo "</td><td id=fight valign=top style='background-color:#EEEEEE;'></td><td valign=top align=right style='width:250px;background-color:#EEEEEE;'>";
####//
	if ($player->pers['chp'] > 0 and $persvs['chp']>0) 
		echo show_pers_in_f($persvs,1); 
	else 
		echo "<img src='images/battle_art/".($fight["id"]%12+1).".jpg'>";
####//
	echo "</td></tr></table>";
####// Finished.
	
	#######################################################################################
### - JS\ON - ##
echo "<SCRIPT>";
if ($options[7]<>"no")echo "top.flog_set();";
############
#/#/# Parameters:::
$radius = $db->sqlr("SELECT MAX(radius) FROM wp WHERE uidp=".$player->pers["uid"]." and weared=1 and type='orujie'",0);
if ($radius<1)$radius=1;
if($player->pers["fstate"]<2 and $radius<(floor(sqrt(sqr($player->pers["xf"]-$persvs["xf"])+sqr($player->pers["yf"]-$persvs["yf"])))) and intval($fight["bplace"]))
	echo "var NEAR = 0;";
else
	echo "var NEAR = 1;";
	

	echo "\n var _closed = ".intval($fight["closed"]).";\n";
	echo "var whatteam=".$player->pers["fteam"].";\n";
	echo "var level=".$player->pers["level"].";\n";
	echo "var logid = '".$player->pers["cfight"]."';\n";
	echo "var arrow_name='".ARROW_NAME."';\n";
	echo "var x=".intval($player->pers["xf"]).";var y=".intval($player->pers["yf"]).";\n";
	echo "var mid='".intval($fight["bplace"])."';\n";
	echo "var maxx=".intval($fight["maxx"]).";\n";
	echo "var maxy=".intval($fight["maxy"]).";\n";
	echo "var damage_get=".intval($player->pers["damage_get"]-$player->pers["chp"]).";";
	echo "var damage_give=".intval($player->pers["damage_give"]).";\n";
	echo "var persvs_id='".base64_encode($persvs["id"].$persvs["uid"])."';\n"; 
	echo "var ffreq = '".$player->pers["fight_request"]."';\n";
	echo "var your_img = '".$player->pers["pol"]."_".$player->pers["obr"]."';\n";
	if ($persvs["invisible"]>tme()) echo "var vs_img = 'male_invisible';\n";
	else echo "var vs_img = '".$persvs["pol"]."_".$persvs["obr"]."';\n";
	if ($player->pers["hp"]>$player->pers["chp"] and (($player->pers["hp"]-$player->pers["chp"])<=$player->pers["cma"])) echo "var up_health=".($player->pers["hp"]-$player->pers["chp"]).";"; 
	else  echo "var up_health=0;"; 
#/#/#// Finished;

$go_no_p ='|'; // -  Переменная определяющая клетки куда ходить нельзя.

//////////////// Командs
$p_t1 = $db->sql("SELECT user,chp,level,sign,hp,uid,xf,yf,cma,ma,invisible FROM users WHERE cfight=".$player->pers["cfight"]." and fteam=1 and chp>0", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
$b_t1 = $db->sql("SELECT user,chp,level,hp,id,xf,yf,cma,ma FROM bots_battle WHERE cfight=".$player->pers["cfight"]." and fteam=1 and chp>0", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
$p_t2 = $db->sql("SELECT user,chp,level,sign,hp,uid,xf,yf,cma,ma,invisible FROM users WHERE cfight=".$player->pers["cfight"]." and fteam=2 and chp>0", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
$b_t2 = $db->sql("SELECT user,chp,level,hp,id,xf,yf,cma,ma FROM bots_battle WHERE cfight=".$player->pers["cfight"]." and fteam=2 and chp>0", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
$LIFE1 = 0; // - Кол-во живых игроков в команде 1 включая ботов
$LIFE2 = 0; // - Кол-во живых игроков в команде 2 влючая ботов
$BOTS1 = 0; // Боты в 1ой команде
$BOTS2 = 0; // боты во 2о1 команде
$_CAN_TURN = 0; // - Можно ли сходить
#################################################################
$cans = $db->sql("SELECT uid2 FROM turns_f WHERE uid1=".$player->pers["uid"]."", __FILE__,__LINE__,__FUNCTION__,__CLASS__);####
$uids = '';															####
while($c = mysql_fetch_array($cans)) $uids.='<'.$c["uid2"].'>';		####
#################################################################
$CAN_TURN = 0;
/*
	Верхний блок нужен для получения строчки ИД персонажей кого ударил текущий персонаж. И если при переборе персонажей противоположной команды найдётся хотябы один чей ИД отсутствует в этой строке значит текущий персонаж может сходить.
	$CAN_TURN - отвечает за то может ли персонаж сходить. 0-нет 1-да.
*/

################# - Вывод команд  - ##################################################################################### 
	echo "var team1 = '";
	// Выводит всех людей в первой команде:
	while ($tmp = mysql_fetch_array($p_t1))
	{
		$LIFE1++;
		if ($tmp["invisible"]>tme() and $tmp["uid"]<>$player->pers["uid"])
		 {
			$tmp["user"]='невидимка';
			$tmp["sign"]='none';
			$tmp["level"]='??';
			$tmp["chp"]=1;$tmp["hp"]=1;
			$tmp["cma"]=1;$tmp["ma"]=1;
		 }
		echo $tmp["sign"]."|".$tmp["user"]."|".$tmp["level"]."|".$tmp["chp"]."|".$tmp["hp"]."|".$tmp["cma"]."|".$tmp["ma"]."|".$tmp["xf"]."|".$tmp["yf"]."|".base64_encode($tmp["uid"])."|@";
		$go_no_p .= $tmp["xf"]."_".$tmp["yf"]."|";
		#####
			if ($player->pers["fteam"]<>1 and !substr_count($uids,"<".$tmp["uid"].">")) $CAN_TURN = 1;
		#####
	}
	///////////// Выводит всех ботов в первой  команде::
	while ($tmp = mysql_fetch_array($b_t1))
	{
		$LIFE1++;
		$BOTS1++;
		echo "none|".$tmp["user"]."|".$tmp["level"]."|".$tmp["chp"]."|".$tmp["hp"]."|".$tmp["cma"]."|".$tmp["ma"]."|".$tmp["xf"]."|".$tmp["yf"]."|".base64_encode($tmp["id"])."|@";
		$go_no_p .= $tmp["xf"]."_".$tmp["yf"]."|";
		#####
			if ($player->pers["fteam"]<>1) $CAN_TURN = 1;
		#####
	}
	echo "';\n";
###########################################################################################################
		echo "var team2 = '";
	// Выводит всех людей в первой команде:
	while ($tmp = mysql_fetch_array($p_t2))
	{
		$LIFE2++;
		if ($tmp["invisible"]>tme() and $tmp["uid"]<>$player->pers["uid"])
		 {
			$tmp["user"]='невидимка';
			$tmp["sign"]='none';
			$tmp["level"]='??';
			$tmp["chp"]=1;$tmp["hp"]=1;
			$tmp["cma"]=1;$tmp["ma"]=1;
		 }
		echo $tmp["sign"]."|".$tmp["user"]."|".$tmp["level"]."|".$tmp["chp"]."|".$tmp["hp"]."|".$tmp["cma"]."|".$tmp["ma"]."|".$tmp["xf"]."|".$tmp["yf"]."|".base64_encode($tmp["uid"])."|@";
		$go_no_p .= $tmp["xf"]."_".$tmp["yf"]."|";
		#####
			if ($player->pers["fteam"]<>2 and !substr_count($uids,"<".$tmp["uid"].">")) $CAN_TURN = 1;
		#####
	}
	///////////// Выводит всех ботов в первой  команде::
	while ($tmp = mysql_fetch_array($b_t2))
	{
		$LIFE2++;
		$BOTS2++;
		echo "none|".$tmp["user"]."|".$tmp["level"]."|".$tmp["chp"]."|".$tmp["hp"]."|".$tmp["cma"]."|".$tmp["ma"]."|".$tmp["xf"]."|".$tmp["yf"]."|".base64_encode($tmp["id"])."|@";
		$go_no_p .= $tmp["xf"]."_".$tmp["yf"]."|";
		#####
			if ($player->pers["fteam"]<>2) $CAN_TURN = 1;
		#####
	}
	echo "';\n";
#######################################################################################

if(($LIFE1-$BOTS1)==0 and ($LIFE2-$BOTS2)==0 and $LIFE1 and $LIFE2 and $fight["type"]!='f') // в бою остались одни боты
	include("inc/bots/bots_battle.php");
	
	
echo "var go_no='".$bplace["xy"]."@".$go_no_p."';\n";
$timeout = mtrunc($fight["ltime"]+$fight["timeout"]-time());

if($OD_UDAR+3<=(round($player->pers["sb1"])+5))
	echo "var od = ".(round($player->pers["sb1"])+5).";";
else
	echo "var od = ".($OD_UDAR+3).";";
echo "var od_udar = ".($OD_UDAR).";";
echo "show_fight_head(".intval($fight["oruj"]).",".intval($fight["travm"]).",".$timeout.");";

#############################################
$_FINISHED = 0;
if ($LIFE1==0 or $LIFE2==0 or $fight["type"]=='f') {include ('inc/inc/fights/finish.php');$_FINISHED = 1;}
## Для завоевания. Если происходит завоевание и щёлкает таймаут - проигрыш и конец завоевания.
if ($player->pers["gain_time"] and $timeout==0 and $BOTS1==$LIFE1 and !$_FINISHED)
{
	$LIFE2 = 0;
	include ('inc/inc/fights/finish.php');
}
#############################################
## Таймаут с ботом.
if ($timeout==0 and $BOTS1==$LIFE1 and !$_FINISHED)
{
	$LIFE2 = 0;
	include ('inc/inc/fights/finish.php');
}
#############################################
elseif ($player->pers["chp"]>0 and !$_FINISHED and $CAN_TURN) # - Твой ХОД
{
	################################### -  МАГИЯ - ######################################################
	$kblast=0;$kaura=0;$kkid=0;
	$idall=explode ("|",BOOK_INDEX);
	$img[0]='';$name[0]='';$idc[0]='';
	$arimg[0]='';$arname[0]='';$arid[0]='';
	$kidimg[0]='';$kidname[0]='';$kidid[0]='';
	if ($player->pers["fstate"]==3)
	{
		$bls = $db->sql("SELECT * FROM u_blasts WHERE uidp=".$player->pers["uid"]." and tlevel<=".$player->pers["level"]." and ts6<=".$player->pers["s6"]." and manacost<=".$player->pers["cma"]." and cur_colldown<=".time()." and cur_turn_colldown<=".$player->pers["f_turn"], __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		while($bl = mysql_fetch_array($bls))
		{
			if ($bl["turn_colldown"]) $colldown= 'Перезарядка: '.$bl["turn_colldown"].' ход.';
			else $colldown= 'Перезарядка: '.$bl["colldown"].' сек.';
			$bl["name"] = $bl["name"]."|<b class=user>".$bl["name"]."</b><br><font class=ma>".$bl["manacost"]." MA</font><br>Удар: <b>".$bl["udmin"]."-".$bl["udmax"]."</b><br>".$colldown."<br>".$bl["describe"];
			$kblast++;
			$img[$kblast]=$bl["image"];
			$name[$kblast]=$bl["name"];
			$idc[$kblast]=$bl["id"];
		}

		$as = $db->sql("SELECT * FROM u_auras WHERE uidp=".$player->pers["uid"]." and tlevel<=".$player->pers["level"]." and ts6<=".$player->pers["s6"]." and manacost<=".$player->pers["cma"]." and cur_colldown<=".time()." and cur_turn_colldown<=".$player->pers["f_turn"], __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		$txt = '';
		while($a = mysql_fetch_array($as))
		{
			$txt .= $a["image"].'#'.$a["id"].'#<b class=user>'.$a["name"].'</b>@';
			if ($a["turn_colldown"]) 
			$colldown= 'Перезарядка: <b>'.$a["turn_colldown"].'ход.</b>';
			else
			$colldown= 'Перезарядка: <b>'.$a["colldown"].'сек.</b>';
			if ($a["turn_esttime"]) 
			$esttime= 'Время действия: <b>'.$a["turn_esttime"].'ход.</b>';
			else
			$esttime= 'Время действия: <b>'.$a["esttime"].'сек.</b>';
			if ($a["targets"]<1) $a["targets"] = 1;
			$targets = '@<i>Целей не более '.$a["targets"]."</i>";
			if ($a["forenemy"]==0)
					$forenemy = "<i class=green>На свою команду</i>";
				elseif ($bl["forenemy"]==1)
					$forenemy = "<i class=red><b>На чужую команду</b></i>";
				else
					$forenemy = "<i class=blue>На любую команду</i>";
			$txt .= $esttime.'@'.$colldown.$targets."@".$forenemy;
			$params = explode("@",$a["params"]);
				foreach($params as $par)
				{
					$p = explode("=",$par);
					$perc = '';
					if (substr($p[0],0,2)=='mf') $perc = '%';
					if ($p[1])
					$txt .= '@'.name_of_skill($p[0]).':<b>'.plus_param($p[1]).$perc.'</b>';
				}
			$txt .= '|';
		}

	}
  echo '
var can_turn = '.intval($CAN_TURN).';
var n = '.$kblast.';
img = new Array();
id = new Array();
nam = new Array();

var auras = \''.$txt.'\';

var nk = '.$kkid.';
arimg = new Array();
arid = new Array();
arnam = new Array();';

for ($i=1;$i<=$kblast;$i++) echo'
img['.$i.']="'.$img[$i].'";
nam['.$i.']="'.$name[$i].'";
id['.$i.']="'.$idc[$i].'";';

for ($i=1;$i<=$kkid;$i++) echo'
kidimg['.$i.']="'.$kidimg[$i].'";
kidnam['.$i.']="'.$kidname[$i].'";
kidid['.$i.']="'.$kidid[$i].'";';
##########################################################################
	$bliz = '';
	$bliz_od = '';
	if ($player->pers["fstate"]==1)
	{
		$bliz = 'Простой|Прицельный|Оглушающий';
		$bliz_od = '3|5|7';
		$spds = $db->sql("SELECT * FROM u_special_dmg WHERE uid=".$player->pers["uid"]." ORDER BY od ASC", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		while($spd = mysql_fetch_array($spds,MYSQL_ASSOC))
		{
			$bliz .= "|".$spd["name"];
			$bliz_od .= "|".($spd["od"]+2);
		}
	}
	if ($player->pers["fstate"]==3)
	{
		$bliz = 'Магия';
		$bliz_od = 'magic';
	}
	if ($player->pers["fstate"]==4)
	{
		$bliz = 'Кинуть предмет';
		$bliz_od = 'kid';
	}
	$block = 'Простой|Усиленный|Крепчайший';
	$block_od = '1|2|5';
	##############################################################################
	echo "var bliz='".$bliz."';";
	echo "var bliz_od='".$bliz_od."';";
	echo "var block='".$block."';";
	echo "var block_od='".$block_od."';";
	echo "var speed=".(1.5).";";
	echo "var before=".(($player->pers["turn_before"])?1:0).";";
	if (@$http->get["noone"]==1 or substr_count($fight["nowhomvote"],"<".$player->pers["user"].">")) 
		echo "var noone=1;";
	else
		echo "var noone=0;";
	echo "show_boxes_and_form('".$addon."','".$addon_od."',".$player->pers["fstate"].");";
	$_CAN_TURN = 1;
	# Голосование за ничью:
	if (@$http->get["noone"]==1 and !substr_count($fight["nowhomvote"],"<".$player->pers["user"].">"))
	{
		$fight["nowhom"]++;
		if ($player->pers["invisible"]<=tme())
		$nyou = "<font class=bnick color=".$colors[$player->pers["fteam"]].">".$player->pers["user"]."</font>[".$player->pers["level"]."]";
		else 
		$nyou = "<font class=bnick color=".$colors[$player->pers["fteam"]]."><i>невидимка</i></font>[??]";
		$s = $nyou." голосует за ничью! Ещё ".mtrunc(floor($fight["players"]/2) - $fight["nowhom"] + 1)." голосов до ничьи.";
		$fight["nowhomvote"].="<".$player->pers["user"].">";
		$fight["all"]='<font class=timef>'.date("H:i")."</font>".$s.";".$fight["all"];
		$db->sql("UPDATE `fights` SET `all`='".addslashes($fight["all"])."' , `ltime`='".time()."' , nowhom=nowhom+1, nowhomvote='".$fight["nowhomvote"]."' WHERE `id`='".$fight["id"]."' ;", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		if (mtrunc(floor($fight["players"]/2) - $fight["nowhom"] + 1)==0)
		{
			$s = "Бой закончен голосованием. Ничья.";
			$db->sql("UPDATE users SET chp=0 WHERE cfight=".$player->pers["cfight"], __FILE__,__LINE__,__FUNCTION__,__CLASS__);
			add_flog($s,$player->pers["cfight"]); 
			$fight["all"]='<font class=timef>'.date("H:i")."</font>".$s.";".$fight["all"];
			$db->sql("UPDATE `fights` SET `all`='".addslashes($fight["all"])."' , `ltime`='".time()."' WHERE `id`='".$fight["id"]."' ;", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
			include ('inc/inc/fights/finish.php');
			$fight["type"] = 'f';
			echo "location = 'main.php';";
		}else add_flog($s,$player->pers["cfight"]); 
	}
}
######################################## - Не твой ХОД
if ($timeout==0 and @$http->get["battle"]=="nowhom" and $CAN_TURN==0 and $player->pers["chp"]>0) 
{
	if ($player->pers["invisible"]<=tme()) $nyou = "<font class=bnick color=".$colors[$player->pers["fteam"]].">".$player->pers["user"]."</font>[".$player->pers["level"]."]";
	else $nyou = "<font class=bnick color=".$colors[$player->pers["fteam"]]."><i>невидимка</i></font>[??]";
	$s = "Бой закончен по таймауту. Ничья (".$nyou.").";
	add_flog($s,$player->pers["cfight"]); 
	$fight["all"]='<font class=timef>'.date("H:i")."</font>".$s.";".$fight["all"];
	$db->sql("UPDATE `fights` SET `all`='".addslashes($fight["all"])."' , `ltime`='".time()."' WHERE `id`='".$fight["id"]."' ;", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
	include ('inc/inc/fights/finish.php');
}
elseif ($timeout==0 and @$http->get["battle"]=="finish" and $CAN_TURN==0 and $player->pers["chp"]>0)
{
	$not_turned = $db->sqlr("SELECT COUNT(*) FROM users WHERE cfight=".$player->pers["cfight"]." and fteam=".$player->pers["fteam"]." and chp>0 and can_turn=1",0, __FILE__,__LINE__,__FUNCTION__,__CLASS__); //Кол-во не сходивших в вашей команде
	if(!$not_turned)
	{
		if ($player->pers["invisible"]<=tme())
			$nyou = "<font class=bnick color=".$colors[$player->pers["fteam"]].">".$player->pers["user"]."</font>[".$player->pers["level"]."]";
		else 
			$nyou = "<font class=bnick color=".$colors[$player->pers["fteam"]]."><i>невидимка</i></font>[??]";
		$s = "Бой закончен по таймауту. Победа. (".$nyou.").";
		$fight['travm'] = 100;
		if ($player->pers["fteam"]==1) $LIFE2 = 0; else $LIFE1 = 0;
		$pt = $db->sql("SELECT * FROM users WHERE cfight=".$player->pers["cfight"]." and fteam<>".$player->pers["fteam"]." and chp>0", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		$db->sql("UPDATE users SET chp=0 WHERE cfight=".$player->pers["cfight"]." and fteam<>".$player->pers["fteam"]." and chp>0", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		while ($_persvs = mysql_fetch_array($pt)){include ('inc/inc/fights/travm.php');$s.=$str;}
		add_flog($s,$player->pers["cfight"]); 
		$fight["all"]='<font class=timef>'.date("H:i")."</font>".$s.";".$fight["all"];
		$db->sql("UPDATE `fights` SET `all`='".addslashes($fight["all"])."' , `ltime`='".time()."' WHERE `id`='".$fight["id"]."' ;", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		include ('inc/inc/fights/finish.php');
	}
}
elseif ($timeout>0 and !$_FINISHED and $CAN_TURN==0)
{
	if ($player->pers["chp"]>0) 
		echo "show_message_in_f('<div align=center class=title>Ожидаем хода соперника.</div>');";
	else 
		echo "show_message_in_f('<div align=center class=title>Ожидаем конца боя.</div>');";
}elseif($timeout==0 and !$_FINISHED and $CAN_TURN==0)
{
	$not_turned = $db->sqlr("SELECT COUNT(*) FROM users WHERE cfight=".$player->pers["cfight"]." and fteam=".$player->pers["fteam"]." and chp>0 and can_turn=1",0, __FILE__,__LINE__,__FUNCTION__,__CLASS__); //Кол-во не сходивших в вашей команде
	if(!$not_turned and $player->pers["chp"]>0)
	 	echo "show_message_in_f('<center class=return_win><b>Время хода противника истекло.</b><br> Чтобы прекратить бой нажмите на любую кнопку.Чтобы дать сопернику ещё время, просто ничего не нажимайте.<br><input type=button class=login value=Ничья onclick=\"location=\'main.php?battle=nowhom\'\"> | <input type=button class=login value=\"Завершить бой по таймауту\" onclick=\"location=\'main.php?battle=finish\'\"></center>');";
	 else
	 	echo "show_message_in_f('<div align=center class=title>Ожидаем хода соперника.</div>');";
}
	
// Р”РѕР±Р°РІР»СЏРµРј РёРЅРґРёРєР°С‚РѕСЂ С…РѕРґР° Рё СѓР»СѓС‡С€РµРЅРЅС‹Р№ РёРЅС‚РµСЂС„РµР№СЃ
echo "<div id='turn_indicator' class='turn_indicator' style='display:none;'></div>";

set_vars("can_turn=".intval($_CAN_TURN)."",$player->pers["uid"]);

$log = $db->sql("SELECT time,log FROM fight_log WHERE cfight=".$player->pers["cfight"]." ORDER BY turn DESC LIMIT 0,3", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
$_LOG = '';
$i = 0;
while ($l = mysql_fetch_array($log))
{
	if($i>0) $_LOG .= "<hr>";
	$i++;
	if($i%2) $_LOG .= "<div style=\"background-color: #FFFFFF;\">";
	$_LOG .= str_replace("'",'"',str_replace("%","<br><br><font class=timef>".$l["time"]."</font> ","<font class=timef>".$l["time"]."</font> ".$l["log"]));
	if($i%2) $_LOG .= "</div>";
}
echo "show_message_in_f('<div class=but>".$_LOG."</div>');";	
########################
if(!$_FINISHED)
	echo "show_finish(".$player->pers["fexp"].",".$player->pers["exp_in_f"].",".$player->pers["f_turn"].");";
## - JS\OFF - ##
echo "</SCRIPT>";
###########
?>