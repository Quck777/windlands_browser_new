<?php

/* Памятка
priz_array
	1 - лн, количество
	2 - бр, количество
	3 - вещь навсегда, ид вещи | время в секундах, для навсегда 0
	4 - опыт, количество
	5 - обнул, количество
можно использовать несколько вариантов, нужно разделить запятыми
1|50,2|50@4|22|3600@


job_type
	1 - Выбить предмет с бота 
	2 - Убить бота
	3 - Предмет в инвентаре
	4 - Быть в нужной локе

Тип задания				- job_type
Имя бота, имя предмета	- job_botname
Имя дропа				- job_lutname
Шанс дропа				- job_success
Количество дропа, вещей	- job_count



*/

class jQuest
{
	private $pers = Array();
	private $db = Array();
	private $quest = false;
	public $weapon_yes = Array();
	
	public function __construct($p)
	{
		$this->pers = $p;
		$this->db = $GLOBALS['db'];
	//	$this->quest = $this->db->sqla('SELECT * FROM `jQuest_nps` WHERE `x` = "'.$this->pers['x'].'" and `y` = "'.$this->pers['y'].'" LIMIT 1;');
		
	}
	
	public function view_quest()
	{
		$nps = $this->db->sqla('SELECT `qid`, `img` FROM `jQuest_nps` WHERE `x` = "'.$this->pers['x'].'" and `y` = "'.$this->pers['y'].'" and `active` = 1 LIMIT 1;');
		if ( !$nps )
		{
			echo $this->no_quest_view();
			return false;
		}
		
		$quest_true = false;
		
		// Не рекомендуется вешать на одного НПС дофига квестов + ВЫДАСТ ТОЛЬКО 1 КВЕСТ
		$quest = $this->db->sql('SELECT * FROM `jQuest_list` WHERE `nps_id` = '.$nps['qid'].';');
		while ( $q = mysql_fetch_assoc($quest) )
		{
			// Проверка на ограничение по уровням
			if ( ($q['min_level']>0 and $q['min_level']>$this->pers['level']) or ($q['max_level']>0 and $q['max_level']<$this->pers['level']) ) continue;
			// Проверяем нужен ли предквест для взятия этого квеста
			if ( $q['pre_quest'] )
			{
				$puq = (int)$this->db->sqlr('SELECT `qid` FROM `jQuest_users` WHERE `qid` = '.$q['pre_quest'].' and `uid` = '.$this->pers['uid'].' and `finish` = 1 LIMIT 1;');
				if ( !$puq ) continue;
			}
			
			// Проверить нада висит ли уже этот квест у юзера
			$uq = $this->db->sqla('SELECT * FROM `jQuest_users` WHERE `qid` = '.$q['id'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
			if ( $uq ) { // Квест уже есть у персонажа
				// Если квест завершон, проверяем, можно ли взять его снова
				// 0 - нельзя повторить, -1 - Повтор после успешного выполнения или провала, 1 - раз в repetition дней
				if ( $uq['finish'] )
				{
					if ( $q['repetition']==0 ) continue;
					elseif ( $q['repetition'] > 0 ) // обработка через сколько дней можно повторить
					{
						if ( (date('d', $q['finish_time'])+$q['repetition']) > date('d', tme()) )
							$this->db->sql('DELETE FROM `jQuest_users` WHERE `qid` = '.$q['id'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
						else continue;
					} else { // Если квест можно повторять, то удаляем строку 
						$this->db->sql('DELETE FROM `jQuest_users` WHERE `qid` = '.$q['id'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
					}
				} // Обрабатываем провал квеста по времени
				elseif ( $q['runtime']>0 and tme() > ($uq['start_time']+$q['runtime']) )
				{
					if ( $q['repetition']== -1 ) $this->db->sql('DELETE FROM `jQuest_users` WHERE `qid` = '.$q['id'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
					else {
					//	$this->db->sql('UPDATE `jQuest_users` SET `finish` = 1, `finish_time` = '.tme().' WHERE `qid` = '.$q['id'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
						$this->db->sql('DELETE FROM `jQuest_users` WHERE `qid` = '.$q['id'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
						continue; // Учесть повтор
					}
				}
				$vvt = ''; $vvta = explode('@', (($uq['job_count']==$uq['job_result']) ? $q['text_finish'] : $q['text_action']));
				foreach ( $vvta as $v ) $vvt.= (!empty($vvt)?',':'').'"'.$v.'"';
				echo 'OK@['.$vvt.']@["'.$nps['img'].'",['.(($uq['job_count']==$uq['job_result']) ? 2 : 0).','.$q['id'].']]';
				return true;
			}
			$quest_true = $q;
			break;
		}
		// Если ничего не найдено, то посылаем все нах и выдаем ошибку
		if ( !$quest_true ) { echo $this->no_quest_view(); return false; }
		$vvt = ''; $vvta = explode('@', $quest_true['text_start']);
		foreach ( $vvta as $v ) $vvt.= (!empty($vvt)?',':'').'"'.$v.'"';
		echo 'OK@['.$vvt.']@["'.$nps['img'].'",[1,'.$quest_true['id'].']]';
		return true;
	}
	
	private function no_quest_view()
	{
		return 'OK@["Здравствуй '.$this->pers['user'].', для Вас сейчас нет никаких поручений."]@["",[0,0]]';
	}
	
	public function get_yourself_quest($qid=0)
	{
		if ( !$qid ) { echo 'NO@Квест не существует.'; return false;}
		$quest = $this->db->sqla('SELECT * FROM `jQuest_list` WHERE `id` = '.$qid.';');
		if ( !$quest ) { echo 'NO@Квест не существует.'; return false;}
		// Для того чтоб квест не брали у кого угодно проверяем если ли тут НПС
		$nps = (int)$this->db->sqlr('SELECT `qid` FROM `jQuest_nps` WHERE `x` = "'.$this->pers['x'].'" and `y` = "'.$this->pers['y'].'" and `active` = 1 and `qid` = '.$quest['nps_id'].' LIMIT 1;');
		if ( !$nps ) { echo 'NO@Тут нельзя взять квест'; return false;}
		
		// Проверка на ограничение по уровням
		if ( ($quest['min_level']>0 and $quest['min_level']>$this->pers['level']) or ($quest['max_level']>0 and $quest['max_level']<$this->pers['level']) ) { echo 'NO@Нельзя взять квест.'; return false; }
		// Проверяем нужен ли предквест для взятия этого квеста
		if ( $quest['pre_quest'] )
		{
			$puq = (int)$this->db->sqlr('SELECT `qid` FROM `jQuest_users` WHERE `qid` = '.$quest['pre_quest'].' and `uid` = '.$this->pers['uid'].' and `finish` = 1 LIMIT 1;');
			if ( !$puq ) { echo 'NO@Сначала нужно выполнить предыдущий квест.'; return false; }
		}
		// Проверить нада висит ли уже этот квест у юзера
		$uq = (int)$this->db->sqlr('SELECT `qid` FROM `jQuest_users` WHERE `qid` = '.$quest['id'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
		if ( $uq ) { echo 'NO@Вы уже выполняете этот квест.'; return false; }
		
		$this->db->sql("INSERT INTO `jQuest_users` (`qid`, `uid`, `name`, `finish`, `finish_time`, `start_time`, `runtime`, `job_type`, `job_botname`, `job_lutname`, `job_success`, `job_count`) 
			VALUES ('".$quest['id']."', '".$this->pers['uid']."', '".$quest['name']."', '0', '0', '".tme()."', '".$quest['runtime']."', '".$quest['job_type']."', '".$quest['job_botname']."', '".$quest['job_lutname']."', '".$quest['job_success']."', '".$quest['job_count']."');");
		echo 'OK@Квест получен.';
	}
	
	private function randp($i)
	{
		$r = rand(1,100)-$i; // 70 - 80
		return ( $r > 0 ) ? false : true;
	}
	
	public function get_finish($qid)
	{
		if ( !$qid ) { echo 'NO@Квест не существует.'; return false;}
		$quest = $this->db->sqla('SELECT * FROM `jQuest_list` WHERE `id` = '.$qid.';');
		if ( !$quest ) { echo 'NO@Квест не существует.'; return false;}
		// Для того чтоб квест не брали у кого угодно проверяем если ли тут НПС
		$nps = (int)$this->db->sqlr('SELECT `qid` FROM `jQuest_nps` WHERE `x` = "'.$this->pers['x'].'" and `y` = "'.$this->pers['y'].'" and `active` = 1 and `qid` = '.$quest['nps_id'].' LIMIT 1;');
		if ( !$nps ) { echo 'NO@Тут нельзя взять квест'; return false;}
		
		// Проверка на ограничение по уровням
		if ( ($quest['min_level']>0 and $quest['min_level']>$this->pers['level']) or ($quest['max_level']>0 and $quest['max_level']<$this->pers['level']) ) { echo 'NO@Нельзя взять квест.'; return false; }
		// Проверяем нужен ли предквест для взятия этого квеста
		if ( $quest['pre_quest'] )
		{
			$puq = (int)$this->db->sqlr('SELECT `qid` FROM `jQuest_users` WHERE `qid` = '.$quest['pre_quest'].' and `uid` = '.$this->pers['uid'].' and `finish` = 1 LIMIT 1;');
			if ( !$puq ) { echo 'NO@Сначала нужно выполнить предыдущий квест.'; return false; }
		}
		// Проверить нада висит ли уже этот квест у юзера
		$uq = $this->db->sqla('SELECT * FROM `jQuest_users` WHERE `qid` = '.$quest['id'].' and `uid` = '.$this->pers['uid'].' and `finish` = 0 LIMIT 1;');
		if ( !$uq ) { echo 'NO@У вас нет такого квеста, либо вы его уже сдали.'; return false; }
		if ( $uq['runtime'] > 0 and tme() > ($uq['start_time']+$uq['runtime']) ) { echo 'NO@Время на выполнение квеста закончилось.'; return false; }
		if ( $uq['job_count'] != $uq['job_result'] ) { echo 'NO@Вы не выполнили квест.'; return false; }
		## Все ок, зачисляем награду и закрываем квест
		$obj_array = Array();
		$obj = explode('@', $quest['priz_array']);
		foreach ( $obj as $v )
		{
			$a = explode(',',$v); $ar = Array();
			foreach ($a as $i) $ar[] = explode('|', $i);
			$obj_array[] = $ar;
		}
		# Выбираем какой вариант дарить
		$obj_array = $obj_array[rand(0, count($obj_array)-1)];
		// Даем подарки
		$echo_list = '';
		foreach ($obj_array as $val)
		{
			switch ( $val[0] )
			{
				case 1: // LN
					$echo_list.= $val[1].' LN, ';
					$this->pers['money']+= (int)$val[1];
					$this->db->sql('UPDATE `users` SET `money` = '.$this->pers['money'].' WHERE `uid` = '.$this->pers['uid'].' LIMIT 1;');
					break;
				case 2: // Бр
					$echo_list.= $val[1].' Бр, ';
					$this->pers['dmoney']+= (int)$val[1];
					$this->db->sql('UPDATE `users` SET `dmoney` = '.$this->pers['dmoney'].' WHERE `uid` = '.$this->pers['uid'].' LIMIT 1;');
					break;
				case 3: // даем вещь
						$v = $this->db->sqla('SELECT `id`, `name` FROM `weapons` WHERE `id` = '.(int)$val[1].' LIMIT 1;');
						if ( $v )
						{
							$echo_list.= $v['name'].', ';
							$tme = (int)$val[2];
							$tm = ($tme) ? (tme()+$tme) : 0;
							$id = insert_wp( $v['id'], $this->pers['uid'],-1,0,$this->pers['user'] );
							if ($id)
								$this->db->sql("UPDATE `wp` SET `timeout` = ".$tm.", `describe` = 'Награда за квест «<b>".$quest["name"]."</b>»' WHERE `id` = ".$id);
						}
					break;
				case 4: // Даем опыт
					$echo_list.= $val[1].' опыта, ';
					$this->pers['exp']+= (int)$val[1];
					$this->db->sql('UPDATE `users` SET `exp` = '.$this->pers['exp'].' WHERE `uid` = '.$this->pers['uid'].' LIMIT 1;');
					break;
				case 5:	// Даем обнул
					$echo_list.= $val[1].' обнул., ';
					$this->pers['zeroing']+= (int)$val[1];
					$this->db->sql('UPDATE `users` SET `zeroing` = '.$this->pers['zeroing'].' WHERE `uid` = '.$this->pers['uid'].' LIMIT 1;');
					break;
			}
		}
		if ( !empty($echo_list) ) echo 'OK@Получено: '.substr($echo_list,0,strlen($echo_list)-2);
		else echo 'OK@Спасибо за помощь.';
		$this->db->sql('UPDATE `jQuest_users` SET `finish` = 1, `finish_time` = '.tme().' WHERE `qid` = '.$quest['id'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
	}
	
	public function battle_action($bot)
	{
		$quest = $this->db->sql('SELECT * FROM `jQuest_users` WHERE `uid` = '.$this->pers['uid'].' and `finish` = 0 and (`job_type` = 1 or `job_type` = 2);');
		while ( $q = mysql_fetch_assoc($quest) )
		{
			// Если бот не тот, прекращаем
			if ( $q['job_botname'] != $bot['user'] ) continue;
			// Закрываем действие если уже выполнен квест
			if ( $q['job_count'] == $q['job_result'] ) continue;
			// Если время на выполнение квеста исчерпано прекращаем обработку
			if ( $q['runtime'] > 0 and tme() > ($q['start_time']+$q['runtime']) ) continue;
			
			// Выполняем обработку шанса успешного выпадения
			if ( $q['job_type']==1 )
			{
				if ( !$this->randp($q['job_success']) ) continue;
			}
			# Все ок, зачисляем
			$q['job_result']+= 1;
			$this->db->sql('UPDATE `jQuest_users` SET `job_result` = '.$q['job_result'].' WHERE `qid` = '.$q['qid'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
			
			$text = ( $q['job_type']==1 ) ? $q['job_lutname'] : $q['job_botname'];
			
			say_to_chat('q','По квесту «<b>'.$q["name"].'</b>» засчитан 1 <b>'.$text.'</b>.',1,$this->pers['user'],'*',0);
		}
	}
	
	public function inv_quest()
	{
		$quest = $this->db->sql('SELECT * FROM `jQuest_users` WHERE `uid` = '.$this->pers['uid'].' and `finish` = 0 and `job_type` = 3;');
		while ( $q = mysql_fetch_assoc($quest) )
		{
			// Закрываем действие если уже выполнен квест
			if ( $q['job_count'] == $q['job_result'] ) continue;
			// Если время на выполнение квеста исчерпано прекращаем обработку
			if ( $q['runtime'] > 0 and tme() > ($q['start_time']+$q['runtime']) ) continue;
			
			$w = (int)$this->db->sqlr('SELECT COUNT(`id`) FROM `wp` WHERE `uidp` = "'.$this->pers['uid'].'" and `weared` = 0 and `clan_sign` = "" and `name` = "'.$q['job_botname'].'";');
			// Если нету вещи 
			if ( !$w ) continue;
			// Вещи есть, считаем количество
			if ( $q['job_count'] > $w  )
			{
				$this->weapon_yes[$q['qid']] = $w;
				continue;
			}
			# Все ок, зачисляем
			$q['job_result']+= ($w > $q['job_count']) ? $q['job_count'] : $w;
			$this->db->sql('UPDATE `jQuest_users` SET `job_result` = '.$q['job_result'].' WHERE `qid` = '.$q['qid'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
			$this->db->sql('DELETE FROM `wp` WHERE `uidp` = "'.$this->pers['uid'].'" and `weared` = 0 and `clan_sign` = "" and `name` = "'.$q['job_botname'].'" LIMIT '.$q['job_result'].';');
		}
	}
	
	public function goloc_quest()
	{
		$quest = $this->db->sql('SELECT * FROM `jQuest_users` WHERE `uid` = '.$this->pers['uid'].' and `finish` = 0  and `job_type` = 4;');
		while ( $q = mysql_fetch_assoc($quest) )
		{
			// Закрываем действие если уже выполнен квест
			if ( $q['job_count'] == $q['job_result'] ) continue;
			// Если время на выполнение квеста исчерпано прекращаем обработку
			if ( $q['runtime'] > 0 and tme() > ($q['start_time']+$q['runtime']) ) continue;
			$res = explode('|', $q['job_botname']);
			if ( $this->pers['location'] != $res[0] ) continue;
			if ( $res[0] == 'out' )
			{
				if ( $res[1] != $this->pers['x'] ) continue;
				if ( $res[2] != $this->pers['y'] ) continue;
			}
			elseif ( $res[0] == 'mine' )
			{
				if ( $res[1] != $this->pers['minex'] ) continue;
				if ( $res[2] != $this->pers['miney'] ) continue;
			}
			
			# Все ок, зачисляем
			$q['job_result'] = $q['job_count'];
			$this->db->sql('UPDATE `jQuest_users` SET `job_result` = '.$q['job_result'].' WHERE `qid` = '.$q['qid'].' and `uid` = '.$this->pers['uid'].' LIMIT 1;');
		}
	}

}

/*
Array ( 
	[0] => Array ( 
		[0] => Array ( [0] => 1 [1] => 200 ) 
		[1] => Array ( [0] => 2 [1] => 10 ) 
	) 
	[1] => Array ( 
		[0] => Array ( [0] => 1 [1] => 500 ) 
		[1] => Array ( [0] => 2 [1] => 5 ) 
	) 
) 
*/

?>