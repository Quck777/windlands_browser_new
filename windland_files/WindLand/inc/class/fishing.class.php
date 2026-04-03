<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php

define('MAX_FISH_COUNT', 3); // Максимальное число пойманых рыб может быть
define('MAX_TIRE', 90);		// Максимальная усталка чтоб ловить
define('TIMEOUT_FISH', ($pers['priveleged'] ? 1 : 180)); // Таймаут после заброса
define('PLUS_TIRE', 3);		// Сколько добавлять усталки за раз

class Fishing
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
	//	if ( $this->cell['last_herbal_change'] < (tme()-HERBAL_CHANGE) )
	//		$this->UpHerbals();
	}
	
	public function view_fishlist()
	{
		if ( !$this->cell['fishing'] ) return 'NO@Тут рыбы нет.';
		if ( $this->pers['waiter'] > tme() ) return 'F5@';
		
		$r = '';
		// Пищем список приманок
		$prim = $this->db->sql('SELECT `durability`, `max_durability`, `id`, `image`, `name` FROM `wp` WHERE `uidp` = '.$this->pers['uid'].' and `weared` = 0 and `p_type` = 1 and `durability` > 0 and `type` <> "orujie" and `sp6` = 0 ;');
		while( $v = mysql_fetch_assoc($prim) ){$r.= ',["'.$v['name'].'",'.$v['id'].',"'.$v['image'].'","'.$v['durability'].'","'.$v['max_durability'].'"]';}
		// Есть ли удочка?
		$snasti = (int)$this->db->sqlr('SELECT COUNT(id) FROM `wp` WHERE `uidp` = '.$this->pers['uid'].' and `weared` = 1 and `p_type` = 1 and `durability` > 0;');
		return 'OK@[['.$snasti.',"'.$this->pers['weight_of_w'].'","'.$this->getMassMax().'"]'.$r.']';
	}
	
	private function getMassMax()
	{
		return abs(10+($this->pers['sm3']+$this->pers['s4'])*10);
	}
	
	public function goFishing($pid, $code)
	{
		if ( $this->pers['waiter'] > tme() ) return 'F5@';
		if ( $this->pers['tire'] > MAX_TIRE ) return 'NO@Вы слишком устали.';
		if ( $this->pers['weight_of_w'] > $this->getMassMax() ) return 'NO@Вы перегруженны.';
	//	if ( $code <> $_SESSION['captcha_keystring'] ) return 'NO@Неверный код.';
		// Есть ли удочка, вытаскиваем ид приманки
		$snasti = $this->db->sqla('SELECT * FROM `wp` WHERE `uidp` = '.$this->pers['uid'].' and `weared` = 1 and `p_type` = 1 and `durability` > 0;');
		if ( !$snasti ) return 'NO@Удочка не надета.';
		$prim = $this->db->sqla('SELECT `durability`, `max_durability`, `id`, `image`, `name` FROM `wp` WHERE `uidp` = '.$this->pers['uid'].' and `id` = '.$pid.' and `weared` = 0 and `p_type` = 1 and `durability` > 0 and `type` <> "orujie" and `sp6` = 0;');
		if ( !$prim ) return 'NO@Не найдена приманка.';
		#### Если все ок начинаем расчет
		$skill_p = 0;
		if(WEATHER==5) $skill_p = $this->pers['sp6']/(-2);
		if(WEATHER==7) $skill_p = $this->pers['sp6']/(-1.25);
		$fgood = '';
		$count = MAX_FISH_COUNT;
		$count = ($snasti['durability'] < $prim['durability']) ? (($count > $snasti['durability']) ? $snasti['durability'] : $count) : (($count > $prim['durability']) ? $prim['durability'] : $count);
		$prim['durability']-= $count; $snasti['durability']-= $count;
		$pric = 0;
		$fish_all = $this->db->sql('SELECT * FROM `fish` WHERE `skill` < '.($this->pers['sp6']+$skill_p).' and `place` = "'.$this->cell["fishing"].'" and `prim` = '.intval(str_replace("fishing_prim_","",$prim["image"])).' ORDER BY RAND() LIMIT 0, '.$count.';');
		while( $fish = mysql_fetch_assoc($fish_all) )
		{
			$nk = 0;
			if ( rand(sqrt($this->cell['fish_population']),150)<($fish['no_kl']-sqrt($this->pers['sp6']+$skill_p)+10) or $this->cell['fish_population']==0 ) $nk = 1;
			if ( $fish['id']==0 ) $nk = 1;
			if ( rand(1,100)<($fish['skill']/10-2*sqrt($this->pers['sp6']+$skill_p)) ) $nk = 1;
			if ( $nk ) continue;
			$this->cell['fish_population']-= 1;
			$wid = @insert_wp('fish_1',$this->pers['uid'],-1,0);
			$vesh = Array();
			$k = rand(-3,3);
			$vesh['weight'] = floor(abs($k+4));
			$vesh['price'] = round($fish['price']+sqrt(sqrt($fish['price']/2)*($k+3)),2);
			$vesh['timeout'] = (tme()+345600);
			$vesh['name'] = $fish['name'];
			$vesh['image'] = 'fish/'.$fish['id'];
			if ($k==-3) $l = "Малёк.";
			if ($k==-2) $l = "Подросший малёк.";
			if ($k==-1) $l = "Малая.";
			if ($k==0) $l = "Средняя.";
			if ($k==1) $l = "Большая.";
			if ($k==2) $l = "Огромная.";
			if ($k==3) $l = "Гигантская.";
			$fgood.= $vesh['name'].' ('.$l.')<br>';
			$this->db->sql('UPDATE `wp` SET `price` = '.$vesh["price"].', `weight` = '.$vesh["weight"].', `timeout` = '.$vesh['timeout'].', `describe`="'.$l.'", `name` = "'.$vesh['name'].'", `image`= "'.$vesh['image'].'"  WHERE `id` = '.$wid.';');
			$this->pers['weight_of_w']+= $vesh['weight']; $pric+= $vesh['price'];
		}
		$pum = (rand(0,10) == 0) ? round(5.0/(sqrt($this->pers['sp6'])+1),2) : 0;
		$this->pers['waiter'] = round(tme()+TIMEOUT_FISH);
		$this->pers['tire']+= PLUS_TIRE;
		$this->pers['sp6']+= $pum;
		$this->db->sql('UPDATE `users` SET `action` = 0, `waiter` = '.$this->pers['waiter'].', `weight_of_w` = '.$this->pers['weight_of_w'].', `tire` = '.$this->pers['tire'].', `sp6` = '.$this->pers['sp6'].' WHERE `uid` = '.$this->pers['uid'].';');
		$this->db->sql('UPDATE `wp` SET `durability` = '.$snasti['durability'].' WHERE `id` = '.$snasti['id'].';');
		$this->db->sql('UPDATE `wp` SET `durability` = '.$prim['durability'].' WHERE `id` = '.$prim['id'].';');
		if ($pric) loges_proffesions($pric, 1, $this->pers['sp6']+$skill_p, $this->pers['uid']);
		if ($fgood) return 'OK@'.$fgood.($pum ? ('<br>Уменя повышено на '.$pum.'.'):'').'@'.TIMEOUT_FISH; else return 'NO@Нет клева.@'.TIMEOUT_FISH;
	}
	
}
?>