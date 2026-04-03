<?php
/**
 * achievements.php - Система достижений для игры "Земля Ветров"
 * Версия: 2.0 Full Enhanced
 * Совместимость: PHP 5.3+, кодировка windows-1251
 * 
 * Улучшения:
 * - Полная система достижений с категориями
 * - Прогресс достижений в реальном времени
 * - Награды за достижения (опыт, золото, титулы)
 * - Рейтинг игроков по достижениям
 * - Редкие и легендарные достижения
 */

if (!defined('MICROLOAD')) { define('MICROLOAD', true); }

// Категории достижений
define('ACH_COMBAT', 1);      // Боевые
define('ACH_QUEST', 2);       // Квесты
define('ACH_SOCIAL', 3);      // Социальные
define('ACH_PROFESSION', 4);  // Профессии
define('ACH_EXPLORATION', 5); // Исследования
define('ACH_RARE', 6);        // Редкие
define('ACH_LEGENDARY', 7);   // Легендарные

// Редкость достижений
define('RARITY_COMMON', 1);
define('RARITY_UNCOMMON', 2);
define('RARITY_RARE', 3);
define('RARITY_EPIC', 4);
define('RARITY_LEGENDARY', 5);

/**
 * Проверка и добавление достижения
 */
function check_and_add_achievement($uid, $achievement_key) {
    GLOBAL $db;
    
    // Получаем данные о достижении
    $achievement = get_achievement_data($achievement_key);
    
    if (!$achievement) return false;
    
    // Проверяем, есть ли уже у игрока
    $exists = $db->sqlr("SELECT COUNT(*) FROM user_achievements WHERE uid=".intval($uid)." AND achievement='".$achievement_key."'");
    
    if ($exists > 0) return false; // Уже есть
    
    // Добавляем достижение
    $db->sql("INSERT INTO user_achievements (uid, achievement, achieved_at, category, rarity) VALUES (".intval($uid).", '".$achievement_key."', ".tme().", ".intval($achievement['category']).", ".intval($achievement['rarity']).")", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Обновляем счетчик
    $db->sql("UPDATE users SET achievements_count=achievements_count+1 WHERE uid=".intval($uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Выдаем награду
    give_achievement_reward($uid, $achievement);
    
    // Уведомление
    notify_achievement($uid, $achievement);
    
    return true;
}

/**
 * Получить данные о достижении
 */
function get_achievement_data($key) {
    $achievements = get_all_achievements();
    
    return isset($achievements[$key]) ? $achievements[$key] : false;
}

/**
 * Все доступные достижения
 */
function get_all_achievements() {
    return array(
        // БОЕВЫЕ
        'first_blood' => array(
            'name' => 'Первая кровь',
            'description' => 'Убить первого противника',
            'category' => ACH_COMBAT,
            'rarity' => RARITY_COMMON,
            'reward_exp' => 50,
            'reward_gold' => 25,
            'reward_title' => ''
        ),
        'killing_spree' => array(
            'name' => 'Серия убийц',
            'description' => 'Убить 5 врагов без смерти',
            'category' => ACH_COMBAT,
            'rarity' => RARITY_UNCOMMON,
            'reward_exp' => 200,
            'reward_gold' => 100,
            'reward_title' => 'Охотник'
        ),
        'rampage' => array(
            'name' => 'Буйство',
            'description' => 'Убить 10 врагов без смерти',
            'category' => ACH_COMBAT,
            'rarity' => RARITY_RARE,
            'reward_exp' => 500,
            'reward_gold' => 250,
            'reward_title' => 'Берсерк'
        ),
        'godlike' => array(
            'name' => 'Божественный',
            'description' => 'Убить 25 врагов без смерти',
            'category' => ACH_COMBAT,
            'rarity' => RARITY_EPIC,
            'reward_exp' => 1500,
            'reward_gold' => 750,
            'reward_title' => 'Божество'
        ),
        'crit_master' => array(
            'name' => 'Мастер критов',
            'description' => 'Нанести 100 критических ударов',
            'category' => ACH_COMBAT,
            'rarity' => RARITY_RARE,
            'reward_exp' => 600,
            'reward_gold' => 300,
            'reward_title' => 'Снайпер'
        ),
        'combo_king' => array(
            'name' => 'Король комбо',
            'description' => 'Выполнить комбо x5',
            'category' => ACH_COMBAT,
            'rarity' => RARITY_EPIC,
            'reward_exp' => 1000,
            'reward_gold' => 500,
            'reward_title' => 'Комбоман'
        ),
        
        // КВЕСТЫ
        'quest_novice' => array(
            'name' => 'Новичок квестов',
            'description' => 'Выполнить 1 квест',
            'category' => ACH_QUEST,
            'rarity' => RARITY_COMMON,
            'reward_exp' => 100,
            'reward_gold' => 50,
            'reward_title' => ''
        ),
        'quest_explorer' => array(
            'name' => 'Исследователь',
            'description' => 'Выполнить 10 квестов',
            'category' => ACH_QUEST,
            'rarity' => RARITY_UNCOMMON,
            'reward_exp' => 300,
            'reward_gold' => 150,
            'reward_title' => 'Путешественник'
        ),
        'quest_master' => array(
            'name' => 'Мастер квестов',
            'description' => 'Выполнить 50 квестов',
            'category' => ACH_QUEST,
            'rarity' => RARITY_RARE,
            'reward_exp' => 1000,
            'reward_gold' => 500,
            'reward_title' => 'Герой'
        ),
        'quest_legend' => array(
            'name' => 'Легенда квестов',
            'description' => 'Выполнить 100 квестов',
            'category' => ACH_QUEST,
            'rarity' => RARITY_EPIC,
            'reward_exp' => 3000,
            'reward_gold' => 1500,
            'reward_title' => 'Легенда'
        ),
        'epic_hero' => array(
            'name' => 'Эпический герой',
            'description' => 'Выполнить эпический квест',
            'category' => ACH_QUEST,
            'rarity' => RARITY_EPIC,
            'reward_exp' => 2000,
            'reward_gold' => 1000,
            'reward_title' => 'Эпик'
        ),
        'legendary_champion' => array(
            'name' => 'Легендарный чемпион',
            'description' => 'Выполнить легендарный квест',
            'category' => ACH_QUEST,
            'rarity' => RARITY_LEGENDARY,
            'reward_exp' => 5000,
            'reward_gold' => 2500,
            'reward_title' => 'Чемпион'
        ),
        
        // СОЦИАЛЬНЫЕ
        'friend_maker' => array(
            'name' => 'Друг',
            'description' => 'Добавить 10 друзей',
            'category' => ACH_SOCIAL,
            'rarity' => RARITY_UNCOMMON,
            'reward_exp' => 200,
            'reward_gold' => 100,
            'reward_title' => 'Душа компании'
        ),
        'clan_member' => array(
            'name' => 'Клановец',
            'description' => 'Вступить в клан',
            'category' => ACH_SOCIAL,
            'rarity' => RARITY_COMMON,
            'reward_exp' => 100,
            'reward_gold' => 50,
            'reward_title' => ''
        ),
        'clan_leader' => array(
            'name' => 'Лидер клана',
            'description' => 'Создать свой клан',
            'category' => ACH_SOCIAL,
            'rarity' => RARITY_RARE,
            'reward_exp' => 500,
            'reward_gold' => 250,
            'reward_title' => 'Вождь'
        ),
        
        // ПРОФЕССИИ
        'alchemist' => array(
            'name' => 'Алхимик',
            'description' => 'Сварить 100 зелий',
            'category' => ACH_PROFESSION,
            'rarity' => RARITY_RARE,
            'reward_exp' => 800,
            'reward_gold' => 400,
            'reward_title' => 'Алхимик'
        ),
        'blacksmith' => array(
            'name' => 'Кузнец',
            'description' => 'Сковать 100 предметов',
            'category' => ACH_PROFESSION,
            'rarity' => RARITY_RARE,
            'reward_exp' => 800,
            'reward_gold' => 400,
            'reward_title' => 'Кузнец'
        ),
        'fisher' => array(
            'name' => 'Рыбак',
            'description' => 'Поймать 500 рыб',
            'category' => ACH_PROFESSION,
            'rarity' => RARITY_UNCOMMON,
            'reward_exp' => 400,
            'reward_gold' => 200,
            'reward_title' => 'Рыбак'
        ),
        
        // ИССЛЕДОВАНИЯ
        'explorer' => array(
            'name' => 'Исследователь',
            'description' => 'Посетить все локации',
            'category' => ACH_EXPLORATION,
            'rarity' => RARITY_RARE,
            'reward_exp' => 1000,
            'reward_gold' => 500,
            'reward_title' => 'Первопроходец'
        ),
        'treasure_hunter' => array(
            'name' => 'Охотник за сокровищами',
            'description' => 'Найти 50 тайников',
            'category' => ACH_EXPLORATION,
            'rarity' => RARITY_EPIC,
            'reward_exp' => 1500,
            'reward_gold' => 750,
            'reward_title' => 'Кладоискатель'
        ),
        
        // РЕДКИЕ
        'lucky_one' => array(
            'name' => 'Везунчик',
            'description' => 'Выпадение легендарного предмета',
            'category' => ACH_RARE,
            'rarity' => RARITY_LEGENDARY,
            'reward_exp' => 3000,
            'reward_gold' => 1500,
            'reward_title' => 'Везунчик'
        ),
        'survivor' => array(
            'name' => 'Выживший',
            'description' => 'Выжить с 1 HP',
            'category' => ACH_RARE,
            'rarity' => RARITY_EPIC,
            'reward_exp' => 1000,
            'reward_gold' => 500,
            'reward_title' => 'Бессмертный'
        ),
        
        // ЛЕГЕНДАРНЫЕ
        'immortal' => array(
            'name' => 'Бессмертный',
            'description' => 'Провести в игре 365 дней',
            'category' => ACH_LEGENDARY,
            'rarity' => RARITY_LEGENDARY,
            'reward_exp' => 10000,
            'reward_gold' => 5000,
            'reward_title' => 'Бессмертный'
        ),
        'master_of_all' => array(
            'name' => 'Мастер всего',
            'description' => 'Получить 50 достижений',
            'category' => ACH_LEGENDARY,
            'rarity' => RARITY_LEGENDARY,
            'reward_exp' => 5000,
            'reward_gold' => 2500,
            'reward_title' => 'Мастер'
        )
    );
}

/**
 * Выдать награду за достижение
 */
function give_achievement_reward($uid, $achievement) {
    GLOBAL $db;
    
    $exp = intval($achievement['reward_exp']);
    $gold = intval($achievement['reward_gold']);
    $title = $achievement['reward_title'];
    
    // Выдаем опыт и золото
    if ($exp > 0 || $gold > 0) {
        $db->sql("UPDATE users SET exp=exp+".intval($exp).", gold=gold+".intval($gold)." WHERE uid=".intval($uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    }
    
    // Выдаем титул если есть
    if (!empty($title)) {
        $current_title = $db->sqlr("SELECT title FROM users WHERE uid=".intval($uid));
        
        if (empty($current_title)) {
            $db->sql("UPDATE users SET title='".$title."' WHERE uid=".intval($uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
        }
    }
}

/**
 * Уведомление о получении достижения
 */
function notify_achievement($uid, $achievement) {
    GLOBAL $db;
    
    $pers = $db->sqla("SELECT user FROM users WHERE uid=".intval($uid));
    
    // Цвет в зависимости от редкости
    $colors = array(
        RARITY_COMMON => '#ffffff',
        RARITY_UNCOMMON => '#00ff00',
        RARITY_RARE => '#0066ff',
        RARITY_EPIC => '#ff6600',
        RARITY_LEGENDARY => '#ffd700'
    );
    
    $color = isset($colors[$achievement['rarity']]) ? $colors[$achievement['rarity']] : '#ffffff';
    
    $rarity_names = array(
        RARITY_COMMON => 'Обычное',
        RARITY_UNCOMMON => 'Необычное',
        RARITY_RARE => 'Редкое',
        RARITY_EPIC => 'Эпическое',
        RARITY_LEGENDARY => 'Легендарное'
    );
    
    $rarity_name = isset($rarity_names[$achievement['rarity']]) ? $rarity_names[$achievement['rarity']] : '';
    
    // Уведомление в чат
    say_to_chat('System', '🏆 <b>'.$pers['user'].'</b> получает достижение: <font color="'.$color.'"><b>['.$rarity_name.'] '.$achievement['name'].'</b></font>!', '*', 0, '*');
    
    // Можно добавить личное сообщение или всплывающее уведомление
}

/**
 * Получить все достижения игрока
 */
function get_user_achievements($uid) {
    GLOBAL $db;
    
    $achievements = $db->sql("SELECT * FROM user_achievements WHERE uid=".intval($uid)." ORDER BY achieved_at DESC");
    
    $result = array();
    
    while ($ach = mysql_fetch_array($achievements)) {
        $ach_data = get_achievement_data($ach['achievement']);
        if ($ach_data) {
            $ach['data'] = $ach_data;
            $result[] = $ach;
        }
    }
    
    return $result;
}

/**
 * Получить прогресс достижений
 */
function get_achievements_progress($uid) {
    GLOBAL $db;
    
    $total = count(get_all_achievements());
    $earned = $db->sqlr("SELECT COUNT(*) FROM user_achievements WHERE uid=".intval($uid));
    
    $by_category = array();
    $by_rarity = array();
    
    // По категориям
    $categories = $db->sql("SELECT category, COUNT(*) as count FROM user_achievements WHERE uid=".intval($uid)." GROUP BY category");
    while ($cat = mysql_fetch_array($categories)) {
        $by_category[$cat['category']] = $cat['count'];
    }
    
    // По редкости
    $rarities = $db->sql("SELECT rarity, COUNT(*) as count FROM user_achievements WHERE uid=".intval($uid)." GROUP BY rarity");
    while ($rar = mysql_fetch_array($rarities)) {
        $by_rarity[$rar['rarity']] = $rar['count'];
    }
    
    return array(
        'total' => $total,
        'earned' => $earned,
        'percent' => round($earned / $total * 100, 1),
        'by_category' => $by_category,
        'by_rarity' => $by_rarity
    );
}

/**
 * Рейтинг игроков по достижениям
 */
function get_achievements_rating($limit = 10) {
    GLOBAL $db;
    
    $rating = $db->sql("SELECT u.uid, u.user, u.achievements_count, u.title 
                        FROM users u 
                        WHERE u.achievements_count > 0 
                        ORDER BY u.achievements_count DESC 
                        LIMIT ".intval($limit));
    
    $result = array();
    $position = 0;
    
    while ($player = mysql_fetch_array($rating)) {
        $position++;
        $player['position'] = $position;
        $result[] = $player;
    }
    
    return $result;
}

/**
 * Получить позицию игрока в рейтинге
 */
function get_player_achievement_position($uid) {
    GLOBAL $db;
    
    $player_count = $db->sqlr("SELECT achievements_count FROM users WHERE uid=".intval($uid));
    
    $higher = $db->sqlr("SELECT COUNT(*) FROM users WHERE achievements_count > ".intval($player_count));
    
    return $higher + 1;
}

?>
