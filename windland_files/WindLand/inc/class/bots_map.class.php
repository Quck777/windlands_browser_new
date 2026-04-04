<?php
define('MAX_BOT_CELL', 3); // Максимальное число ботов на локации одновременно
define('MAX_GROUP_BOT', 3); // Максимум ботов в группе
define('UP_LIST_BOTS', 1200); // обновление ботов раз в 5 часов
define('WAIT_GETINF', ($pers['priveleged'] ? 1 : 15)); // Таймаут между запросами в секундах


class MapBots
{
	private $db = false;
	private $pers	= Array();
	private $cell	= Array();
	
	public function __construct($p)
	{
		$this->db = $GLOBALS['db'];
		$this->pers = $p;
		$this->cell = $this->db->sqla('SELECT * FROM `nature` WHERE `x` ='.$this->pers['x'].' and `y` ='.$this->pers['y']);
		
		// Обновляем ботов если очень нада
		if ( $this->cell['last_bots_change'] < (tme()-UP_LIST_BOTS) )
			$this->UpBotsLocation();
	}
	
	private function UpBotsLocation()
	{
		$obj_bot_id = Array();
		$ins_bot_id = Array();
		// Удаляем ботов для места под новых
		$this->clearLBot();
		// Вытащим какие могут быть боты на локе и пишем их ид в массив
		$nature_bots = $this->db->sql('SELECT `idmin`, `idmax` FROM `nature_bots` WHERE `x` = "'.$this->cell['x'].'" and `y` = "'.$this->cell['y'].'" ORDER BY `frq` DESC;');
		while( $b = mysql_fetch_assoc($nature_bots) ) { for ( $i=$b['idmin']; $i<=$b['idmax']; $i++ ) $obj_bot_id[] = $i; }
		if ( count($obj_bot_id) )
		{
			if ( (count($obj_bot_id)-5) > MAX_BOT_CELL )
			{
				for ( $i=0; $i<MAX_BOT_CELL; $i++ ) // Делаем выборку по циклу
					$ins_bot_id[] = $this->randIdBot($obj_bot_id, $ins_bot_id);
			}
			elseif ( count($obj_bot_id) > MAX_BOT_CELL )
			{
				$sm =  count($obj_bot_id)-MAX_BOT_CELL;
				$ins_bot_id = array_slice($obj_bot_id, $sm);
			} else $ins_bot_id = $obj_bot_id;
			if ($ins_bot_id) $this->insertBot($ins_bot_id);
		}
		$this->db->sql('UPDATE `nature` SET `last_bots_change` = '.tme().' WHERE `x` = "'.$this->cell['x'].'" and y = "'.$this->cell['y'].'";');
	}
	
	private function randIdBot($obj, $ins)
	{
		$id = @$obj[rand(0, count($obj))];
		if ( isset($ins[$id]) ) return $this->randIdBot($obj, $ins);
		return $id;
	}
	
	private function insertBot($id_obj)
	{
		
		$r = ''; $lid = $this->cell['x'].'_'.$this->cell['y'];
		foreach ( $id_obj as $val )
		{
			if ( !$val ) continue;
			$r.= (!empty($r) ? ' ,' : '').'("'.$val.'", "'.tme().'", "'.$lid.'", "", "'.rand(1,MAX_GROUP_BOT).'")';
		}
		$this->db->sql('INSERT INTO `bots_cell` (`id`, `time`, `xy`, `name`, `count`) VALUES '.$r.';');
	}
	
	private function clearLBot()
	{
		$this->db->sql('DELETE FROM `bots_cell` WHERE `xy` = "'.$this->cell['x'].'_'.$this->cell['y'].'";');
	}
	
	public function viewlistBots()
	{
		if ( !$this->cell['bot'] ) return 'NO@1';
		if ( $this->pers['waiter'] > tme() ) return 'F5@';
		$r = '';
		$res = $this->db->sql('SELECT `bots_cell`.`time`, `bots_cell`.`count`, `bots`.* FROM `bots_cell` LEFT OUTER JOIN `bots` ON `bots_cell`.`id` = `bots`.`id` WHERE `bots_cell`.`xy`="'.$this->cell['x'].'_'.$this->cell['y'].'";', __FILE__,__LINE__,__FUNCTION__,__CLASS__);
		while ( $b = mysql_fetch_assoc($res) )
		{
			$r.= (!empty($r) ? ',' : '').'["'.$b['user'].'","'.$b['level'].'","'.$b['obr'].'","'.$b['count'].'","'.$b['id'].'",['.$b['s1'].','.$b['s2'].','.$b['s3'].','.$b['hp'].',"'.$b['udmin'].'-'.$b['udmax'].'",'.$b['kb'].','.$b['mf1'].','.$b['mf2'].','.$b['mf3'].','.$b['mf4'].','.$b['mf5'].','.$b['rank_i'].']]';
		}
		$this->pers['waiter'] = round(tme()+WAIT_GETINF);
		$this->db->sql('UPDATE `users` SET `waiter` = '.$this->pers['waiter'].', `action` = 6 WHERE `uid` = '.$this->pers['uid'].';');
		return (empty($r)?'NO':'OK').'@['.$r.']@'.WAIT_GETINF;
	}
	
	public function attacBots($id)
	{
		if ( $this->pers['action'] != 6 ) return 'F5@';
		$b = $this->db->sqla('SELECT * FROM `bots_cell` WHERE `xy` = "'.$this->cell['x'].'_'.$this->cell['y'].'" and `id` = '.$id.' LIMIT 1;');
		if ( $b )
		{
			$this->db->sql('DELETE FROM `bots_cell` WHERE `xy` = "'.$this->cell['x'].'_'.$this->cell['y'].'" and `id`="'.$b["id"].'" and `time` < '.tme().' LIMIT 1;');
			$f_type = 0;
			if ($cell["type"]==0) $f_type = 1;
			if ($cell["type"]==1) $f_type = 1;
			if ($cell["type"]==2) $f_type = 4;
			if ($cell["type"]==6) $f_type = 5;
			if ($cell["type"]==5) $f_type = 0;
			if ($cell["type"]==8) $f_type = 2;
			if ($cell["type"]==3) $f_type = 3;
			$bb = '';
			if ($b['id_skin']){for($i=1;$i<=$b["count"];$i++)$bb.="bot=".(floor($b["id"]/100)*100+$this->pers['level']-1)."|";}
			else{for($i=1;$i<=$b["count"];$i++)$bb.="bot=".$b["id"]."|";}
			$bb = substr($bb,0,strlen($bb)-1);
			begin_fight($this->pers['user'],$bb,"Охота на существо",50,300,1,$f_type);
			
			echo 'OK@';
		} else echo 'NO@no';
	}
	
}


?>