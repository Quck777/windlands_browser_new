<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

class MySQL
{
    public $host = SQL_HOST;
    private $port = 3306;
    public $sql = false; // Ресурс запроса (mysqli_result)
    private $base = NULL; // Соединение (mysqli)
    public $user = false; // Копия пользователя БД, для бекапа
    public $pass = false; // Копия пароля к БД, для бекапа
    public $db_name = false; // Копия имени БД, для бекапа
    public $tme = 0; // Общее время работы БД
    public $all = 0; // Количество запросов
    public $sql_all = Array(); // Лог запросов
    
    public function __construct($user, $pwd, $db)
    {
        $this->user = $user;
        $this->pass = $pwd;
        $this->db_name = $db;
        
        // Подключение к базе данных через MySQLi
        $this->base = new mysqli($this->host, $user, $pwd, $db, $this->port);
        
        if ( $this->base->connect_error ) {
            die('<h1>Ошибка подключения к базе данных (MySQL Off): ' . $this->base->connect_error . '</h1>');
        }
        
        // Установка кодировки Windows-1251
        $this->base->set_charset('cp1251');
        $this->sql('SET NAMES cp1251');
        $this->sql('SET CHARACTER SET cp1251');
    }
    
    private function error($q, $file='', $line='', $func='', $class='')
    {
        if ( @$_COOKIE['AdminJoe'] ) {
            echo '<hr><b>MySQL Error:</b> '.$this->base->error.'<br>
            <b>Запрос:</b> '.$q.'<br>
            <b>File:</b> '.$file.'<br>
            <b>Line:</b> '.$line.'<br>
            <b>Function:</b> '.$func.'<br>
            <b>Class:</b> '.$class.'<hr>';
        }
    }
    
    public function sql($res, $file='', $line='', $func='', $class='')
    {
        $t = microtime(true);
        $this->sql = $this->base->query($res);
        $t = microtime(true) - $t;
        
        if ( $this->base->error ) {
            $this->error($res, $file, $line, $func, $class);
        }
        
        $this->tme += abs($t);
        $this->all++;
        $this->sql_all[] = Array($res, $file, $line, $func, $class);
        
        return $this->sql;
    }
    
    public function sqla($res, $file='', $line='', $func='', $class='')
    {
        $result = $this->sql($res, $file, $line, $func, $class);
        if ( $result instanceof mysqli_result ) {
            return $result->fetch_assoc();
        }
        return false;
    }
    
    public function sqlr($res, $count=0, $file='', $line='', $func='', $class='')
    {
        $result = $this->sql($res, $file, $line, $func, $class);
        if ( $result instanceof mysqli_result ) {
            if ($count == 0) {
                $row = $result->fetch_row();
                return @$row[0];
            } else {
                $result->data_seek($count);
                $row = $result->fetch_row();
                return @$row[0];
            }
        }
        return false;
    }
    
    public function sqla_id($res, $file='', $line='', $func='', $class='')
    {
        $result = $this->sql($res, $file, $line, $func, $class);
        if ( $result instanceof mysqli_result ) {
            return $result->fetch_row();
        }
        return false;
    }
    
    public function join($res, $file='', $line='', $func='', $class='')
    {
        // Метод для будущих расширений
    }
    
    public function insert_id()
    {
        return $this->base->insert_id;
    }
    
    public function affected_rows()
    {
        return $this->base->affected_rows;
    }
    
    public function escape_string($str)
    {
        return $this->base->real_escape_string($str);
    }
    
    public function __destruct()
    {
        if ( $this->base ) {
            $this->base->close();
        }
    }
}

// Функции-обертки для обратной совместимости со старым кодом
function mysql_fetch_assoc($result) {
    if ( $result instanceof mysqli_result ) {
        return $result->fetch_assoc();
    }
    return false;
}

function mysql_fetch_row($result) {
    if ( $result instanceof mysqli_result ) {
        return $result->fetch_row();
    }
    return false;
}

function mysql_fetch_array($result, $type = MYSQL_BOTH) {
    if ( $result instanceof mysqli_result ) {
        return $result->fetch_array($type);
    }
    return false;
}

function $db->sql($query, $link_identifier = null) {
    global $db;
    if ( isset($db) && $db instanceof MySQL ) {
        return $db->sql($query);
    }
    return false;
}

function mysql_result($result, $row = 0, $field = 0) {
    if ( $result instanceof mysqli_result ) {
        $result->data_seek($row);
        $row_data = $result->fetch_array();
        return @$row_data[$field];
    }
    return false;
}

function mysql_insert_id($link_identifier = null) {
    global $db;
    if ( isset($db) && $db instanceof MySQL ) {
        return $db->insert_id();
    }
    return 0;
}

function mysql_error($link_identifier = null) {
    global $db;
    if ( isset($db) && $db instanceof MySQL ) {
        return $db->base->error;
    }
    return '';
}

function mysql_close($link_identifier = null) {
    global $db;
    if ( isset($db) && $db instanceof MySQL ) {
        $db->base->close();
    }
    return true;
}

function mysql_num_rows($result) {
    if ( $result instanceof mysqli_result ) {
        return $result->num_rows;
    }
    return 0;
}

define('MYSQL_BOTH', MYSQLI_BOTH);
define('MYSQL_ASSOC', MYSQLI_ASSOC);
define('MYSQL_NUM', MYSQLI_NUM);
?>
