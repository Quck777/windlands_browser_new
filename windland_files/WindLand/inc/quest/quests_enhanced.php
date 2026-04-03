<?php
/**
 * quests_enhanced.php - Улучшенная система квестов для игры "Земля Ветров"
 * Версия: 2.0 Full Enhanced
 * Совместимость: PHP 5.3+, кодировка windows-1251
 * 
 * Улучшения:
 * - Система ежедневных квестов
 * - Цепочки квестов (квесты продолжаются друг из друга)
 * - Динамические награды в зависимости от уровня
 * - Отслеживание прогресса в реальном времени
 * - Редкие и эпические квесты
 * - Достижения за выполнение квестов
 */

if (!defined('MICROLOAD')) { define('MICROLOAD', true); }

// Типы квестов
define('QUEST_DAILY', 1);      // Ежедневный
define('QUEST_CHAIN', 2);      // Цепочка
define('QUEST_ONCE', 3);       // Одноразовый
define('QUEST_REPEAT', 4);     // Повторяемый
define('QUEST_EPIC', 5);       // Эпический
define('QUEST_LEGENDARY', 6);  // Легендарный

// Сложность квестов
define('DIFFICULTY_EASY', 1);
define('DIFFICULTY_MEDIUM', 2);
define('DIFFICULTY_HARD', 3);
define('DIFFICULTY_EPIC', 4);

/**
 * Получить доступные квесты для игрока
 */
function get_available_quests($uid) {
    GLOBAL $db;
    
    $pers = $db->sqla("SELECT level, location FROM users WHERE uid=".intval($uid));
    
    if (!$pers) return array();
    
    // Получаем все активные квесты
    $quests = $db->sql("SELECT * FROM quests WHERE active=1 ORDER BY difficulty ASC, exp_reward DESC");
    
    $available = array();
    
    while ($quest = mysql_fetch_array($quests)) {
        // Проверяем уровень
        if ($quest['min_level'] > $pers['level']) continue;
        if ($quest['max_level'] > 0 && $quest['max_level'] < $pers['level']) continue;
        
        // Проверяем локацию
        if (!empty($quest['location_required']) && $quest['location_required'] != $pers['location']) continue;
        
        // Проверяем, не выполнен ли уже (для одноразовых)
        if ($quest['type'] == QUEST_ONCE) {
            $completed = $db->sqlr("SELECT COUNT(*) FROM user_quests WHERE uid=".intval($uid)." AND quest_id=".$quest['id']." AND status='completed'");
            if ($completed > 0) continue;
        }
        
        // Проверяем, не выполняется ли уже
        $in_progress = $db->sqlr("SELECT COUNT(*) FROM user_quests WHERE uid=".intval($uid)." AND quest_id=".$quest['id']." AND status='in_progress'");
        if ($in_progress > 0) continue;
        
        // Проверяем предыдущий квест в цепочке
        if ($quest['prev_quest_id'] > 0) {
            $prev_completed = $db->sqlr("SELECT COUNT(*) FROM user_quests WHERE uid=".intval($uid)." AND quest_id=".$quest['prev_quest_id']." AND status='completed'");
            if ($prev_completed == 0) continue;
        }
        
        // Проверяем ежедневные (сброс каждые 24 часа)
        if ($quest['type'] == QUEST_DAILY) {
            $last_completed = $db->sqlr("SELECT completed_at FROM user_quests WHERE uid=".intval($uid)." AND quest_id=".$quest['id']." ORDER BY completed_at DESC LIMIT 1");
            if ($last_completed && (tme() - $last_completed) < 86400) continue;
        }
        
        $available[] = $quest;
    }
    
    return $available;
}

/**
 * Взять квест
 */
function take_quest($uid, $quest_id) {
    GLOBAL $db;
    
    $quest = $db->sqla("SELECT * FROM quests WHERE id=".intval($quest_id)." AND active=1");
    
    if (!$quest) {
        return array('success' => false, 'message' => 'Квест не найден или не активен');
    }
    
    // Проверяем, можно ли взять
    $available = get_available_quests($uid);
    $can_take = false;
    
    foreach ($available as $q) {
        if ($q['id'] == $quest_id) {
            $can_take = true;
            break;
        }
    }
    
    if (!$can_take) {
        return array('success' => false, 'message' => 'Этот квест недоступен для вас');
    }
    
    // Проверяем максимальное количество активных квестов
    $active_quests = $db->sqlr("SELECT COUNT(*) FROM user_quests WHERE uid=".intval($uid)." AND status='in_progress'");
    if ($active_quests >= 10) {
        return array('success' => false, 'message' => 'У вас слишком много активных квестов (максимум 10)');
    }
    
    // Добавляем квест игроку
    $db->sql("INSERT INTO user_quests (uid, quest_id, status, taken_at, progress) VALUES (".intval($uid).", ".intval($quest_id).", 'in_progress', ".tme().", '0')", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Уведомление
    say_to_chat('System', '<b>'.$_SESSION['user'].'</b> взял квест: <font color="#ffd700">'.$quest['name'].'</font>!', '*', 0, '*');
    
    return array(
        'success' => true,
        'message' => 'Квест "'.$quest['name'].'" взят!',
        'quest' => $quest
    );
}

/**
 * Обновить прогресс квеста
 */
function update_quest_progress($uid, $quest_id, $progress_amount = 1) {
    GLOBAL $db;
    
    $user_quest = $db->sqla("SELECT * FROM user_quests WHERE uid=".intval($uid)." AND quest_id=".intval($quest_id)." AND status='in_progress'");
    
    if (!$user_quest) return false;
    
    $quest = $db->sqla("SELECT * FROM quests WHERE id=".intval($quest_id));
    
    if (!$quest) return false;
    
    $new_progress = intval($user_quest['progress']) + intval($progress_amount);
    $required = intval($quest['target_count']);
    
    // Ограничиваем прогресс
    if ($new_progress > $required) {
        $new_progress = $required;
    }
    
    // Обновляем прогресс
    $db->sql("UPDATE user_quests SET progress=".$new_progress.", updated_at=".tme()." WHERE uid=".intval($uid)." AND quest_id=".intval($quest_id), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Проверяем завершение
    if ($new_progress >= $required) {
        return complete_quest($uid, $quest_id);
    }
    
    return array(
        'completed' => false,
        'progress' => $new_progress,
        'required' => $required,
        'percent' => round($new_progress / $required * 100, 1)
    );
}

/**
 * Завершить квест
 */
function complete_quest($uid, $quest_id) {
    GLOBAL $db;
    
    $user_quest = $db->sqla("SELECT * FROM user_quests WHERE uid=".intval($uid)." AND quest_id=".intval($quest_id)." AND status='in_progress'");
    
    if (!$user_quest) {
        return array('success' => false, 'message' => 'Квест не найден');
    }
    
    $quest = $db->sqla("SELECT * FROM quests WHERE id=".intval($quest_id));
    
    if (!$quest) {
        return array('success' => false, 'message' => 'Квест не найден');
    }
    
    // Получаем данные игрока
    $pers = $db->sqla("SELECT * FROM users WHERE uid=".intval($uid));
    
    // Рассчитываем награду с бонусами
    $exp_reward = calculate_quest_reward($quest['exp_reward'], $quest['difficulty'], $pers['level']);
    $gold_reward = calculate_quest_reward($quest['gold_reward'], $quest['difficulty'], $pers['level']);
    
    // Для эпических и легендарных квестов добавляем бонус
    if ($quest['type'] == QUEST_EPIC) {
        $exp_reward = floor($exp_reward * 1.5);
        $gold_reward = floor($gold_reward * 1.5);
    } elseif ($quest['type'] == QUEST_LEGENDARY) {
        $exp_reward = floor($exp_reward * 2);
        $gold_reward = floor($gold_reward * 2);
    }
    
    // Выдаем награду
    $db->sql("UPDATE users SET exp=exp+".intval($exp_reward).", gold=gold+".intval($gold_reward)." WHERE uid=".intval($uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Обновляем статус квеста
    $db->sql("UPDATE user_quests SET status='completed', completed_at=".tme().", exp_reward=".intval($exp_reward).", gold_reward=".intval($gold_reward)." WHERE uid=".intval($uid)." AND quest_id=".intval($quest_id), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Добавляем достижение
    $achievements = get_quest_achievements($uid, $quest);
    foreach ($achievements as $ach) {
        check_and_add_achievement($uid, $ach);
    }
    
    // Уведомление в чат
    $color = '#ffffff';
    if ($quest['type'] == QUEST_EPIC) $color = '#ff6600';
    if ($quest['type'] == QUEST_LEGENDARY) $color = '#ffd700';
    
    say_to_chat('System', '<b>'.$pers['user'].'</b> завершил квест: <font color="'.$color.'"><b>'.$quest['name'].'</b></font>! Награда: '.intval($exp_reward).' опыта, '.intval($gold_reward).' золота.', '*', 0, '*');
    
    // Проверяем следующий квест в цепочке
    $next_quest = $db->sqla("SELECT * FROM quests WHERE prev_quest_id=".intval($quest_id)." AND active=1");
    $next_available = false;
    
    if ($next_quest) {
        $next_available = true;
    }
    
    return array(
        'success' => true,
        'message' => 'Квест "'.$quest['name'].'" завершен!',
        'exp_reward' => $exp_reward,
        'gold_reward' => $gold_reward,
        'next_quest_available' => $next_available,
        'next_quest' => $next_quest
    );
}

/**
 * Расчет награды с учетом сложности и уровня
 */
function calculate_quest_reward($base_reward, $difficulty, $level) {
    $multiplier = 1.0;
    
    switch($difficulty) {
        case DIFFICULTY_EASY:
            $multiplier = 0.8;
            break;
        case DIFFICULTY_MEDIUM:
            $multiplier = 1.0;
            break;
        case DIFFICULTY_HARD:
            $multiplier = 1.5;
            break;
        case DIFFICULTY_EPIC:
            $multiplier = 2.5;
            break;
    }
    
    // Бонус за уровень
    $level_bonus = 1 + ($level / 100);
    
    return floor($base_reward * $multiplier * $level_bonus);
}

/**
 * Получить достижения за квест
 */
function get_quest_achievements($uid, $quest) {
    $achievements = array();
    
    GLOBAL $db;
    
    // Подсчет выполненных квестов
    $total_completed = $db->sqlr("SELECT COUNT(*) FROM user_quests WHERE uid=".intval($uid)." AND status='completed'");
    
    if ($total_completed >= 1) $achievements[] = 'quest_novice';
    if ($total_completed >= 10) $achievements[] = 'quest_explorer';
    if ($total_completed >= 50) $achievements[] = 'quest_master';
    if ($total_completed >= 100) $achievements[] = 'quest_legend';
    
    // За эпические квесты
    if ($quest['type'] == QUEST_EPIC) {
        $achievements[] = 'epic_hero';
    }
    
    // За легендарные квесты
    if ($quest['type'] == QUEST_LEGENDARY) {
        $achievements[] = 'legendary_champion';
    }
    
    return $achievements;
}

/**
 * Получить активные квесты игрока
 */
function get_active_quests($uid) {
    GLOBAL $db;
    
    $quests = $db->sql("SELECT uq.*, q.name, q.description, q.target_type, q.target_count, q.exp_reward, q.gold_reward, q.difficulty, q.type 
                        FROM user_quests uq 
                        JOIN quests q ON uq.quest_id = q.id 
                        WHERE uq.uid=".intval($uid)." AND uq.status='in_progress' 
                        ORDER BY q.difficulty DESC, uq.taken_at ASC");
    
    $result = array();
    
    while ($quest = mysql_fetch_array($quests)) {
        $percent = 0;
        if ($quest['target_count'] > 0) {
            $percent = round(intval($quest['progress']) / intval($quest['target_count']) * 100, 1);
        }
        
        $quest['progress_percent'] = $percent;
        $result[] = $quest;
    }
    
    return $result;
}

/**
 * Сброс ежедневных квестов
 */
function reset_daily_quests() {
    GLOBAL $db;
    
    $now = tme();
    $day_ago = $now - 86400;
    
    // Находим все ежедневные квесты, которые можно сбросить
    $daily_quests = $db->sql("SELECT id FROM quests WHERE type=".QUEST_DAILY);
    
    while ($quest = mysql_fetch_array($daily_quests)) {
        // Удаляем старые записи о выполнении (старше 24 часов)
        $db->sql("DELETE FROM user_quests WHERE quest_id=".intval($quest['id'])." AND status='completed' AND completed_at < ".$day_ago, __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    }
    
    return true;
}

/**
 * Создать случайный квест для игрока
 */
function generate_random_quest($uid) {
    GLOBAL $db;
    
    $pers = $db->sqla("SELECT level, location FROM users WHERE uid=".intval($uid));
    
    if (!$pers) return false;
    
    // Генерируем параметры
    $types = array('kill', 'collect', 'deliver', 'explore');
    $type = $types[array_rand($types)];
    
    $difficulties = array(DIFFICULTY_EASY, DIFFICULTY_MEDIUM, DIFFICULTY_HARD);
    $difficulty = $difficulties[array_rand($difficulties)];
    
    // Базовые награды
    $base_exp = 100 * $pers['level'];
    $base_gold = 50 * $pers['level'];
    
    // Целевое количество
    $target_count = rand(5, 20) * $difficulty;
    
    // Создаем квест
    $quest_data = array(
        'name' => generate_quest_name($type, $difficulty),
        'description' => generate_quest_description($type, $pers['location']),
        'type' => QUEST_REPEAT,
        'difficulty' => $difficulty,
        'target_type' => $type,
        'target_count' => $target_count,
        'exp_reward' => $base_exp,
        'gold_reward' => $base_gold,
        'min_level' => max(1, $pers['level'] - 5),
        'max_level' => $pers['level'] + 5,
        'location_required' => $pers['location'],
        'active' => 1
    );
    
    // Вставляем в базу (если нужно динамическое создание)
    // Или возвращаем данные для использования
    
    return $quest_data;
}

/**
 * Генерация названия квеста
 */
function generate_quest_name($type, $difficulty) {
    $prefixes = array('Охота', 'Поиск', 'Сбор', 'Разведка', 'Зачистка');
    $suffixes = array('новичка', 'опытного бойца', 'мастера', 'легенды');
    
    $diff_index = $difficulty - 1;
    if ($diff_index < 0) $diff_index = 0;
    if ($diff_index > 3) $diff_index = 3;
    
    $names = array(
        'kill' => 'Охота на монстров',
        'collect' => 'Сбор ресурсов',
        'deliver' => 'Доставка груза',
        'explore' => 'Исследование территории'
    );
    
    return $names[$type] . ' - ' . $suffixes[$diff_index];
}

/**
 * Генерация описания квеста
 */
function generate_quest_description($type, $location) {
    $descriptions = array(
        'kill' => 'Уничтожьте опасных монстров в локации '.$location.'. Будьте осторожны!',
        'collect' => 'Соберите ценные ресурсы в локации '.$location.'. Они пригодятся в хозяйстве.',
        'deliver' => 'Доставьте важный груз через локацию '.$location.'. Не попадитесь врагам!',
        'explore' => 'Исследуйте локацию '.$location.'. Узнайте, что там скрывается.'
    );
    
    return isset($descriptions[$type]) ? $descriptions[$type] : 'Выполните это задание.';
}

?>
