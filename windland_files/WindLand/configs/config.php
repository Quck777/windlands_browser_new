<?php
##############################
#### Global Update 2026 #####
#### WindLand RPG v2.0 ######
##############################

// Конфигурационный файл проекта
// Кодировка: Windows-1251

// Настройки подключения к базе данных
define('SQL_HOST', 'localhost');
define('SQL_USER', 'root');
define('SQL_PASS', '');
define('SQL_BASE', 'windland');

// Корневая директория проекта
define('ROOT', $_SERVER['DOCUMENT_ROOT']);

// Настройки отображения ошибок
if ( @$_COOKIE['AdminJoe'] ) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Таймзона
date_default_timezone_set('Europe/Moscow');

// Версия игры
define('GAME_VERSION', '2.0.2026');

// URL проекта
define('SITE_URL', 'https://cb662053.tw1.ru');

// Путь к изображениям
define('IMG', '/img');

?>
