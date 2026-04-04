<?php
define('WIDTH', 4);
define('HEIGHT', 2);
define('MTIME', tme());
define('TF', 1.3); // Коэф роста усталки
define('TM', 90); // Максимум усталки для движения
define('ADMTURBO', true);
define('DN', (date("H")>6 and date("H")<22) ? 'day' : 'night');

class Naturen
{
	private $pers	= Array();
	private $allNature = Array();
	private $objectMap = false;
	private $lastom_key = false;
	private $db = Array();
	
	public function __construct($p)
	{
		$this->pers = $p;
		$this->lastom_key = (isset($_GET['act'])) ? $this->pers['lastom'] : $GLOBALS['player']->lastom_new;
	//	$this->lastom_key =  $GLOBALS['player']->lastom_new;
		$this->db = $GLOBALS['db'];
	//	echo $this->lastom_key;
	}
	
	public function goloc_to_newcord($x, $y)
	{
		// Если замечаем что юзер хитрим убиваем дальнейдую работу скрипта
		if( (($x-$this->pers['x'])*($x-$this->pers['x'])+($y-$this->pers['y'])*($y-$this->pers['y'])) > 2 ) {echo 'F5@1'; die;}
		// Проверим не много ли усталки
		if( $this->pers['tire'] > TM ) {echo 'MESS@["Вы слишком устали, отдохните."]'; die;}
		// Проверим, может персонаж уже перемещается..
		if( $this->pers['waiter'] > MTIME or $this->pers['curstate'] != 2 or $this->pers['location'] != 'out' ) {echo 'F5@2'; die;}
		// Проверим, есть ли травма) и если есть убиваем дальнейший переход
		if( $this->db->sqlr("SELECT COUNT(special) FROM `p_auras` WHERE `uid`=".$this->pers['uid']." and (`special`=5 or `special`=50) and `esttime`>".MTIME) ) {echo 'MESS@["Вы не можете двигатся имея столь тяжелые ранения."]'; die;}
		#### Все ок, начинаем переход и сохранение параметров в БД, отображение новых параметров
			
			# Пишем новые корды юзеру, вытаскиваем объект карты, мутим с расчетом перемещения умелкой..
			$this->pers['x'] = $x;
			$this->pers['y'] = $y;
			$this->objMap();
			$wt = $this->geTIsTT($this->allNature[$x.'_'.$y]['type']); //
			# Сохраняем все в базу и отдаем аякс код
			set_vars('`tire`='.$this->pers['tire'].', `x`= '.$this->pers['x'].', `y`= '.$this->pers['y'].', `waiter`="'.$this->pers['waiter'].'", `sp8`= "'.$this->pers['sp8'].'"', $this->pers['uid']);
			
			## Квестовые перемещения
			include_once (ROOT.'/inc/class/quest.class.php');
			$que = new jQuest($this->pers);
			$que->goloc_quest();
			##
			
			$e = '';
			$e .= 'GO@';
			$e .= $x.'@';
			$e .= $y.'@';
			$e .= '['.$this->genMapCords().']@';
			$e .= '['.$this->mapbt().']@';
			$e .= '['.$wt.',"'.DN.'", '.round($this->pers['tire']).']';
			echo $e;
		
	}
	
	private function objMap()
	{
		if ( $this->objectMap ) return;
		$res = $this->db->sql("SELECT `name`, `x`, `y`, `go_id`, `type`, `wood`, `fishing`, `herbal`, `teleport` FROM `nature` WHERE `x` >= ".($this->pers['x']-WIDTH)." and `x` <= ".($this->pers['x']+WIDTH)." and `y` >= ".($this->pers['y']-HEIGHT)." and `y` <= ".($this->pers['y']+HEIGHT)." ;");
		while ( $c = mysql_fetch_assoc($res) )
		{
			$this->allNature[$c['x'].'_'.$c['y']] = $c;
		}
		$this->objectMap = true;
	}
	
	public function retVievMap()
	{
		$this->objMap();
		return $this->genMapCords();
	}
	
	private function geTIsTT($t)
	{
		$tr = TF;
		if ($t==0) $wait = 1;
		else
		{
			$wait = mtrunc( floor( ($t*10+10)-($this->pers['sp8']/8) ) );
			if (WEATHER==2) $wait+=5;
			if (WEATHER==3) {$wait+=12;$tr+=2;}
			if (WEATHER==4) {$wait-=3;$tr+=1;}
			if (WEATHER==6) {$wait*=1.5;$tr+=0;}
			if (WEATHER==7) {$wait+=60;$tr+=0;}
			if (WEATHER==6) {$wait+=5;$tr+=0;}
			if ($wait<2 and $t!=0) $wait = 2;
			if (WEATHER==7 and rand(1,100)<10)
			{
				$zid = $this->db->sqlr("SELECT `id` FROM `auras` WHERE `special`=3 ORDER BY RAND()");
				$a = aura_on2($zid,$this->pers);
				$str = '«<font class=red><B>'.$a['name'].'.</B> <i>'.$a['describe'].'</i></font>»';
				say_to_chat('s','На вас обрушились огромные градины и вы получили травму:'.$str.'.','1',$this->pers['user'],$this->pers['location'],date('H:i:s'));
			}
		}
		if ($tr<1) $tr=1;
		# Умнижаем время перехода при перегрузе
		if ( abs(10+($this->pers['sm3']+$this->pers['s4'])*10) < $this->pers['weight_of_w'] ) $wait = $wait*2;
		// Добавляем умелку
		$this->pers['sp8']+= ($wait/($this->pers['sp8']*2+1));
		$this->pers['tire']+= (ADMTURBO and $this->pers['priveleged']==1) ? 0 : $tr;
		if (ADMTURBO and $this->pers['priveleged']==1) $wait = 1;
		$this->pers['waiter'] = $wait+MTIME;
		return $wait;
	}
	
	private function genMapCords()
	{
		$go = '';
		foreach ( $this->allNature as $ce)
		{
			$go .= ((!empty($go)) ? ',' : '').'['.($ce['x']).','.($ce['y']).',"kk"]';
		}
		return $go;
	}
	
	public function getAllBotsMap()
	{
		
	}
	
	public function is_teleport_list()
	{
		$cord = $this->db->sqla('SELECT `teleport` FROM `nature` WHERE `x` = "'.$this->pers['x'].'" and `y` = "'.$this->pers['y'].'" ;');
		if ( $cord['teleport'] )
		{
			$TPs = $this->db->sql('SELECT `name`,`x`,`y`,`teleport` FROM `nature` WHERE `teleport`>0 and not (`x` = "'.$this->pers['x'].'" and `y` = "'.$this->pers['y'].'");');
			$SEL = '';
			while($TP = mysql_fetch_assoc($TPs))
			{
				$SEL .= ((!empty($SEL)) ? ',' : '').'["'.$TP['x'].'","'.$TP['y'].'","'.$TP['name'].'","'.$TP['teleport'].'"]';
			}
			echo 'OK@['.$SEL.']';
		} else echo 'NO@tp';
	}
	
	public function go_teleport($x,$y)
	{
		$cord = $this->db->sqla('SELECT `teleport` FROM `nature` WHERE `x` = "'.$this->pers['x'].'" and `y` = "'.$this->pers['y'].'" ;');
		if ( $cord['teleport'] )
		{
			$cord_new = $this->db->sqla('SELECT `teleport`, `x`, `y` FROM `nature` WHERE `x` = "'.$x.'" and `y` = "'.$y.'" ;');
			if ( $cord_new['teleport'] and $this->pers['money']>=$cord_new['teleport'] )
			{
				$this->pers['money']-= $cord_new['teleport'];
				$this->pers['x'] = $cord_new['x'];
				$this->pers['y'] = $cord_new['y'];
				set_vars('`money`='.$this->pers['money'].', `x`= '.$this->pers['x'].', `y`= '.$this->pers['y'], $this->pers['uid']);
				echo 'OK';
			} else echo 'NO';
		} else echo 'NO';
	}
	
	public function mapbt()
	{
		$this->objMap();
		$youCord = $this->allNature[$this->pers['x'].'_'.$this->pers['y']];
		$r = '';
		$r.= '["bots","Найти монстров","vk1",[]]';
		// Добавляем кнопку для входа в здание если нада
		if ( $youCord['go_id'] ) $r.= ',["'.$youCord['go_id'].'","Войти","'.$this->golocKey($youCord['go_id']).'",[]]';
		if ( $youCord['fishing'] ) $r.= ',["fish","Рыболовля","vk2",[]]';
		if ( $youCord['herbal'] ) $r.= ',["herb","Осмотреть растительность","vk3",[]]';
		if ( $youCord['wood'] ) $r.= ',["wood","Осмотреть деревья","vk4",[]]';
		if ( $youCord['teleport'] ) $r.= ',["telep","Использовать телепорт","vk5",[]]';
		// Кнопочка квеста
		$r.= ',["quest","Квесты","vk6",[]]';
		return $r;
	}
	
	private function golocKey($locid)
	{
		$str = md5(strtoupper($this->lastom_key.$locid.count($locid)));
		return $str;
	}
	
}


?>