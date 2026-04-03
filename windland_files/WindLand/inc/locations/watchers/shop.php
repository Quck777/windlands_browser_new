<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php

if ( UID == 7 or 1 )
{

	## Меняем деньги
	if ( isset($_POST['obmen']) and $player->pers['wmoney']>0 )
	{
		$k = abs(intval($_POST['obmen']));
		if ( $k>0 and $k<$player->pers['wmoney'] )
		{
			$w = $k*WN_CURS;
			$player->pers['wmoney']-= $k;
			set_vars("`money`=money+".$w.", `wmoney`=wmoney-".$k, $player->pers['uid']);
			echo "<div class=return_win>Вы удачно обменяли ".$k." WN на ".$w." LN.</div>";
		}
	}
	
	if ($player->pers['wmoney']>0) echo '<form method="POST" action="main.php?w=shop"><table border="0" width="90%" cellspacing="0" class="fightlong"><tr><td width="356">Обмен валюты: (1 WN = '.WN_CURS.' LN)</td><td>&nbsp;<input type="text" name="obmen" size="10" class=laar>WN<input type="submit" value="Обменять" class="login"></td></tr></table></form><br>';
	
	## Покупаем вещичку)
	if ( isset($_GET['buy']) ) 
	{
		$v = $db->sqla("SELECT * FROM `weapons` WHERE `id`='".intval($_GET['buy'])."' ;");
		if ( $v['id']>0 )
		{
			if ( $player->pers['wmoney'] < $v['price'] ) echo "<b><font class=hp>Не хватает денег.</font></b>"; 
			else 
			{
				$_colls = '';
				$_params = '';
				$r = all_params();
				foreach ($r as $param)
				{
					if($v[$param]!=0)
					{
						$_colls .= ',`'.$param.'`'; $_params .= ",'".$v[$param]."'";
					}
					$param = 't'.$param;
					if($v[$param]!=0)
					{
						$_colls .= ',`'.$param.'`'; $_params .= ",'".$v[$param]."'";
					}
				}
				if ( $db->sql("INSERT INTO `wp` (`uidp` , `weared` ,`id_in_w`, `price`, `dprice`, `image`, `index`, `sign`, `type` , `stype` , `name` , `describe` , `weight` , `where_buy` , `max_durability` , `durability` , `present` , `clan_sign` , `clan_name` ,`radius` , `slots` ,`arrows` ,`arrows_max` ,`arrow_name` , `arrow_price` , `tlevel` ,`p_type` , `user`, `material_show`, `material` ".$_colls.") 
						VALUES ('".UID."', '0','".$v['id']."', '".$v["price"]."', '".$v["dprice"]."', '".$v["image"]."', '".$v["index"]."', '".$v['sign']."', '".$v["type"]."', '".$v["stype"]."', '".$v["name"]."', '".$v["describe"]."', '".$v["weight"]."', '".$v["where_buy"]."', '".$v["max_durability"]."', '".$v['max_durability']."', '".$v["present"]."', '', '', '".$v["radius"]."', '".$v["slots"]."', '".$v["arrows"]."', '".$v["arrows_max"]."', '".$v["arrow_name"]."', '".$v["arrow_price"]."', '".$v["tlevel"]."','".$v["p_type"]."', '".$player->pers['user']."', '".$v["material_show"]."', '".$v["material"]."' ".$_params.");") )
				{
					$player->pers['wmoney']-= $v['price'];
					set_vars("`wmoney`=".$player->pers['wmoney'], $player->pers['uid']);
					echo '<b><font class=hp>Вы купили '.$v['name'].', с Вашего счета снято '.$v['price'].' WN.</font></b>';
				} else echo '<b><font class=hp>Ошибка.</font></b>';
			}
		}
	}

	echo "<center class=lUser>У вас с собой <b>".round($player->pers['wmoney'],2)." WN</b></center><table border=2 width=98% cellspacing=2 cellpadding=2 bordercolorlight=#C0C0C0 bordercolordark=#FFFFFF><form action=main.php onsubmit='return false;' name=lavka1>";
	$enures = $db->sql("SELECT * FROM `weapons` WHERE `sign`='watchers' ORDER BY `price` DESC;");
	while ( $v = mysql_fetch_assoc($enures) )
	{
		echo '<tr><td align="left" class="weapons_box">';
		if ( $v['q_s']<1 )
			echo '<font class="hp"><b> Нет в наличии</b></font> ';
		elseif ( $v['q_s']>0 and $v['dprice']<=$player->pers['wmoney'] )
			echo '<input type="button" class="inv_but" onclick="if(confirm(\'Купить '.$v['name'].'?\'))location = \'main.php?w=shop&buy='.$v['id'].'\'" value="Купить '.$v['name'].'"> <div class="inv_but" id="id'.$v['id'].'"></div>';
		$vesh = $v;
		include ('inc/inc/weapon.php');
		echo '</td></tr>';
	}
	echo '</form></table>';
} else echo 'Недоступно.';

?>