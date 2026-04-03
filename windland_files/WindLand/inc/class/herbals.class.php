<?php

define('HERBAL_CHANGE', 3600); // 18200
define('HERBAL_GROW', 1800); // 8600
define('HERBAL_COUNT', 5);
define('HLOOK_TIME', ($pers['priveleged'] ? 1 : 40)); // Время осмотра) 40 сек// для админа 1
define('HERBDELTIME', 1200000);

class Herbals
{
	private $pers = Array();
	private $db = Array();
	private $cell = Array();
	
	
	public function __construct($p)
	{
		$this->pers = $p;
		$this->db = $GLOBALS['db'];
		$this->cell = $this->db->sqla('SELECT * FROM `nature` WHERE `x` ='.$this->pers['x'].' and `y` ='.$this->pers['y']);
	
		// Обновляем ботов если очень нада
		if ( $this->cell['last_herbal_change'] < (tme()-HERBAL_CHANGE) )
			$this->UpHerbals();
	}
	
	private function UpHerbals()
	{
		$w = $this->db->sqlr('SELECT COUNT(image) FROM `herbals_cell` WHERE `x_y` = "'.$this->cell['x']."_".$this->cell['y'].'" ;');
		if ( $w > HERBAL_COUNT ) $this->db->sql('DELETE FROM herbals_cell WHERE `x_y` = "'.$this->cell['x']."_".$this->cell['y'].'";');
		$h = $this->db->sqla("SELECT * FROM herbals WHERE image%5=".($this->cell['herbal']-1)." ORDER BY RAND() LIMIT 1");
		$this->db->sql("INSERT INTO `herbals_cell` ( `image` , `name` , `time` , `x_y` ) VALUES ('".$h["image"]."', '".$h["name"]."', '".(tme()-HERBAL_GROW-1)."', '".$this->cell["x"]."_".$this->cell["y"]."');");
		$this->db->sql("UPDATE `nature` SET `last_herbal_change` = ".tme()." WHERE `x` = ".$this->cell['x']." and `y` = ".$this->cell['y'].";");
	}
	
	public function view_hlist()
	{
		if ( $this->pers['waiter'] > tme() ) return 'NO@wait';
		if ( !$this->cell['herbal'] ) return 'NO@badl';
		
		// Добавляем время действия 
		$this->pers['waiter'] = round(tme()+HLOOK_TIME);
		$this->db->sql('UPDATE `users` SET `action` = 1, `waiter` = '.$this->pers['waiter'].' WHERE `uid` = '.$this->pers['uid'].';');
		
		$herbal_grow = HERBAL_GROW;
		if (WEATHER==2) $herbal_grow/=2;
		if (WEATHER==3) $herbal_grow*=2;
		if (WEATHER==1 and date("m")>5 and date("m")<9) $herbal_grow*=3;
		if (WEATHER==6) $herbal_grow/=3;
		// Проверяем инструмент и режим
		$w = (int)$this->db->sqlr('SELECT `id` FROM `wp` WHERE `uidp`= '.$this->pers['uid'].' and `weared`=1 and `p_type` = 2 and `durability`>0 ;');
		$r = '';
		$res = $this->db->sql('SELECT * FROM `herbals_cell` WHERE `x_y` = "'.$this->cell['x']."_".$this->cell['y'].'";');
		while( $h = mysql_fetch_assoc($res) )
		{// Имя, картинка, кей если можно резать
			$r.= (empty($r)?'':',').'["'.$h['name'].'","'.$h['image'].'","'.((($h['time']+$herbal_grow)>=tme()) ? '0' : $this->hKey($h['name'])).'","'.$w.'"]';
		}
		return 'OK@['.$r.']';
	}
	
	private function hKey($v)
	{
		return md5($this->pers['city'].$v);
	}
	
	public function srezHerbal($id, $key)
	{
		if ( $this->pers['action']!=1 ) return 'NO@act';
		// Проверяем инструмент и режим
		$w = (int)$this->db->sqlr('SELECT `id` FROM `wp` WHERE `uidp`= '.$this->pers['uid'].' and `weared`=1 and `p_type` = 2 and `durability`>0 ;');
		if ( $w )
		{
			$herbal_grow = HERBAL_GROW;
			if (WEATHER==2) $herbal_grow/=2;
			if (WEATHER==3) $herbal_grow*=2;
			if (WEATHER==1 and date('m')>5 and date("m")<9) $herbal_grow*=3;
			if (WEATHER==6) $herbal_grow/=3;
			$res = $this->db->sqla('SELECT `time`,`image`,`name` FROM `herbals_cell` WHERE `x_y` = "'.$this->cell['x']."_".$this->cell['y'].'" and `image` = "'.$id.'" and `time` < '.(tme()-$herbal_grow).';');
			if ( $res['image'] and $this->hKey($res['name'])==$key )
			{
				$this->db->sql('UPDATE `wp` SET `durability`=durability-1 WHERE id = '.$w.';');
				$this->pers['peace_exp']+= 1;
				$lastid = (int)$this->db->sqlr('SELECT MAX(id) FROM `wp`'); $lastid = 1+$lastid;
				$this->db->sql("INSERT INTO `wp` ( `id` , `uidp` , `weared` ,`id_in_w`, `price` , `dprice` , `image` , `index` , `type` , `stype` , `name` , `describe` , `weight` , `where_buy` , `max_durability` , `durability` ,`p_type`, `timeout`) 
					VALUES ('".$lastid."', '".$this->pers['uid']."', '0','','1', '0', 'herbals/".$id."', '', 'herbal', 'herbal', '".$res['name']."', '', '1', '0', '1', '1','200',".(tme()+HERBDELTIME).");");
				$this->db->sql('UPDATE `herbals_cell` SET `time`='.tme().' WHERE `x_y` = "'.$this->cell['x']."_".$this->cell['y'].'" and `image` = "'.$id.'" and `time` < '.(tme()-$herbal_grow).';');
				$r = 'OK@'.$res['name'];
			} else $r = 'NO@rs';
		} else $r = 'NO@ins';
		$this->db->sql('UPDATE `users` SET `action` = 0, `peace_exp` = '.$this->pers['peace_exp'].' WHERE `uid` = '.$this->pers['uid'].';');
		return $r;
	}
	
}


?>