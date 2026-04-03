<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

<?php
##############################
#### Mod Joe. 13.04.2013 #####
##############################

if ( IS_REGISTER==false ) die;

DEFINE ('REG_TIMEOUT', 360); # Таймаут регистраций с одного IP в секундах
DEFINE ('REG_EXP_REFER', 1000); # Добавляем опыта, тому кто зашел по рефералке
DEFINE ('REG_MONEY_REFER', 100); # Награждаем за преведенного реферала
DEFINE ('REG_COOKIE', 'isReg'); # Название куки, которая ставится при регистрации
DEFINE ('REG_REFERAL', 'RefererReg'); # Название куки реферала
DEFINE ('LOCATION_REG', 'arena');

class Reg
{
	private $login = NULL; // Логин
	private $email = NULL; // Майл
	private $pass = NULL; // Пароль
	private $pass2 = NULL; // Повтор пароля
	private $pol = NULL; // Пол
	private $code = NULL; // Капча
	private $law = 0; // Принято ли соглашение ? 1 : 0
	private $ip = false; // IP юзера
	private $captcha = false;
	public $err = false;
	private $exp = 0;
	private $invitation = false;
	private $invit = 0;
	private $db = false;
	
	function __construct()
	{
		$this->login     = &$GLOBALS['login'];
		$this->email     = &$GLOBALS['email'];
		$this->pass      = &$GLOBALS['pass'];
		$this->pass2     = &$GLOBALS['pass2'];
		$this->pol       = &$GLOBALS['pol'];
		$this->code      = &$GLOBALS['code'];
		$this->law 		 = &$GLOBALS['law'];
		$this->ip     	 = &$GLOBALS['ip'];
		$this->captcha   = &$GLOBALS['captcha'];
		$this->invitation= &$GLOBALS['invitation'];
		$this->db		 = $GLOBALS['db'];
	}

	function verification()
	{
		if ( filter_var($this->login, FILTER_SANITIZE_STRING)==false or $this->is_login($this->login)==true or ( strlen($this->login)<3 or strlen($this->login)>20 ) )
			$this->err = 1;
		elseif ( filter_var($this->email, FILTER_VALIDATE_EMAIL)==false or $this->is_email($this->email)==true )
			$this->err = 2;
		elseif ( $this->pass!=$this->pass2 )
			$this->err = 3;
		elseif ( strlen($this->pass)<8 )
			$this->err = 4;
		elseif ( $this->law!=1 )
			$this->err = 5;
		elseif ( empty($this->code) )
			$this->err = 6;
		elseif ( $this->captcha != $this->code )
			$this->err = 7;
		elseif ( $this->is_ip()==true or !empty($_COOKIE[REG_COOKIE]) )
			$this->err = 8;
#		elseif ( $this->invitation()==false )
#			$this->err = 9;

		if ( $this->err==false ) 
		{
			if ( $this->register() == true ) 
				return 'OK'; else return 'REGERR@8';
		} else return 'REGERR@'.$this->err;
	}
	# Проверка логина на аккаунте на уникальность, если нету, то вернет false
	function is_login($ml)
	{
		if ( empty($ml) ) $eml = false; 
		else $eml = $this->db->sqlr('SELECT `uid` FROM `users` WHERE `smuser`="'.strtolower($ml).'" ;');
		return $eml;	
	}
	# Проверка мыла на аккаунте на уникальность, если нету, то вернет false
	function is_email($ml)
	{
		if ( empty($ml) ) $eml = false; 
		else $eml = $this->db->sqlr('SELECT `uid` FROM `users` WHERE `email`="'.strtolower($ml).'" ;');
		return $eml;	
	}
	# Проверяем, давно ли регистрировался юзер
	function is_ip()
	{
		if ( $this->ip==false ) $ret = false;
		else 
		{
			$ret = $this->db->sqlr('SELECT `uid` FROM `log_register_ipbase` WHERE `ip`="'.$this->ip.'" and `date`>'.( time() - REG_TIMEOUT ).'');
			if ($ret>0) $ret = true;
			else  $ret = false;
		}
		return $ret;
	}
	# Регистрируем юзера
	function register()
	{
		$pol = ( $this->pol == 2 ) ? 'female' : 'male';
		if ( isset($GLOBALS['http']->cook[REG_REFERAL]) )
		{
			$ref = $this->db->sqla_id('SELECT `uid`, `user` FROM `users` WHERE `uid`='.intval($GLOBALS['http']->cook[REG_REFERAL]).' and `lastip`<>"'.$this->ip.'4" ;');
			if ($ref[0]==true) sql ('UPDATE `users` SET `money`=money+'. REG_MONEY_REFER .' WHERE `uid`='.intval($GLOBALS['http']->cook[REG_REFERAL]).' ;');
			$this->exp = REG_EXP_REFER;
		} else $ref = Array(0, '');
		
		$ds = date('d.m.Y H:i');
		$id = $this->db->sqlr('SELECT MAX(uid) FROM `users` ;'); $id = $id+1;
		
		
		$this->db->sql('INSERT INTO `chars` (`uid`) VALUES ('.$id.'); ');
		
		$this->db->sql ('INSERT INTO `users` (`uid`, `user`, `smuser`, `pass`, `email`, `ds`, `pol`, `location`, `referal_nick`,`referal_uid`)
			VALUES ('.$id.', "'.$this->login.'", "'.strtolower($this->login).'", "'.md5($this->pass).'", "'.strtolower($this->email).'", "'.$ds.'", "'.$pol.'", "'.LOCATION_REG.'", "'.$ref[1].'", '.$ref[0].');');
		
	//	$this->db->sql('INSERT INTO `accaunts` (`id`, `e_mail`, `passwd`, `pers_pol`, `referer`) VALUES ('.$id.', "'.strtolower($this->email).'", "'.md5(base64_encode(md5($this->pass))).'", "'.$pol.'", '.$this->invit.'); ');
		
		if ( !mysql_error() )
		{
			$this->db->sql('INSERT INTO `log_register_ipbase` (`ip`, `date`, `uid`) VALUES ("'.filter_var($this->ip, FILTER_SANITIZE_STRING).'", '.time().', '.$id.');');
			if ($ref[0]==true) $this->db->sql('INSERT INTO `log_referals` (`uid`, `whoref`, `date`) VALUES ('.$id.', '.$ref[0].','.time().');');
		//	sql('DELETE FROM `invitation` WHERE `hesh` = '.intval($this->invitation).'');
			setcookie(REG_COOKIE, 1, tme()+REG_TIMEOUT, '/');
		//	send_mail();
			session_destroy();
			return true;
		} else return false;
	}
	
	private function invitation ()
	{
		$inv = intval($this->invitation);
		if ($inv==false) return false;
		else 
		{
			$this->invit = $this->db->sqlr('SELECT `you` FROM `invitation` WHERE `hesh`='.$inv.';');	
			if ($this->invit>0) return true; else return false;
		}
	}
}
?>