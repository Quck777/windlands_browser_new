<?php
/**
 * battle_func.php - Улучшенные функции боя для игры "Земля Ветров"
 * Версия: 2.0 Full Enhanced
 * Совместимость: PHP 5.3+, кодировка windows-1251
 * 
 * Улучшения:
 * - Новая система расчета урона с критическими ударами
 * - Система комбо-ударов
 * - Улучшенный ИИ ботов
 * - Детальные логи боев
 * - Система достижений в бою
 * - Баланс классов
 */

if (!defined('MICROLOAD')) { define('MICROLOAD', true); }

// Константы для системы боя
define('CRIT_CHANCE_BASE', 5);      // Базовый шанс крита 5%
define('CRIT_MULTIPLIER', 1.5);     // Множитель критического удара
define('DODGE_CHANCE_BASE', 3);     // Базовый шанс уклонения
define('BLOCK_CHANCE_BASE', 8);     // Базовый шанс блока
define('COMBO_WINDOW', 3);          // Окно для комбо-ударов (сек)
define('MAX_COMBO', 5);             // Максимальный множитель комбо

/**
 * Расчет урона с учетом всех факторов
 */
function calculate_damage($attacker, $defender, $weapon = array(), $skill = 0) {
    GLOBAL $db;
    
    // Базовый урон оружия
    $base_damage = isset($weapon['damage']) ? intval($weapon['damage']) : 10;
    
    // Добавляем силу атакующего
    $strength_bonus = floor($attacker['strength'] / 10);
    $base_damage += $strength_bonus;
    
    // Добавляем навык владения оружием
    $skill_bonus = floor($skill / 20);
    $base_damage += $skill_bonus;
    
    // Разница уровней
    $level_diff = $attacker['level'] - $defender['level'];
    if ($level_diff > 0) {
        $base_damage += min($level_diff, 10); // Максимум +10 за уровни
    }
    
    // Расчет брони защитника
    $armor = get_total_armor($defender);
    $armor_reduction = min($armor, 0.6); // Максимум 60% редукции
    
    // Применяем броню
    $damage = $base_damage * (1 - $armor_reduction);
    
    // Критический удар
    $crit_chance = CRIT_CHANCE_BASE + floor($attacker['luck'] / 5);
    $is_crit = (rand(1, 100) <= $crit_chance);
    
    if ($is_crit) {
        $damage *= CRIT_MULTIPLIER;
    }
    
    // Уклонение
    $dodge_chance = DODGE_CHANCE_BASE + floor($defender['agility'] / 10);
    $is_dodge = (rand(1, 100) <= $dodge_chance);
    
    if ($is_dodge) {
        $damage = 0;
    }
    
    // Блок щитом
    if ($damage > 0 && $damage < 50) {
        $block_chance = BLOCK_CHANCE_BASE + floor($defender['defense'] / 15);
        $has_shield = has_shield($defender);
        
        if ($has_shield && rand(1, 100) <= $block_chance) {
            $damage *= 0.3; // Щит поглощает 70% урона
        }
    }
    
    // Случайный разброс ±10%
    $variance = rand(-10, 10);
    $damage = $damage * (100 + $variance) / 100;
    
    // Минимальный урон 1 (если не уклонение)
    if (!$is_dodge && $damage < 1) {
        $damage = 1;
    }
    
    return array(
        'damage' => floor($damage),
        'is_crit' => $is_crit,
        'is_dodge' => $is_dodge,
        'is_block' => ($has_shield && rand(1, 100) <= $block_chance && !$is_dodge),
        'base' => $base_damage,
        'armor' => $armor
    );
}

/**
 * Получить общую броню персонажа
 */
function get_total_armor($pers) {
    GLOBAL $db;
    
    $total_armor = 0;
    
    // Проверяем надетые предметы
    $items = $db->sql("SELECT w.defense FROM wp w WHERE w.uidp=".intval($pers['uid'])." AND w.weared=1 AND w.defense>0", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    while ($item = mysql_fetch_array($items)) {
        $total_armor += intval($item['defense']);
    }
    
    // Добавляем защиту от характеристик
    $total_armor += floor($pers['defense'] / 5);
    
    return $total_armor;
}

/**
 * Проверка наличия щита
 */
function has_shield($pers) {
    GLOBAL $db;
    
    $shield = $db->sqlr("SELECT COUNT(*) FROM wp WHERE uidp=".intval($pers['uid'])." AND weared=1 AND type='schit'");
    
    return ($shield > 0);
}

/**
 * Система комбо-ударов
 */
function check_combo($attacker_uid, $combo_type = 'normal') {
    GLOBAL $db;
    
    $now = tme();
    
    // Получаем последнюю атаку
    $last_attack = $db->sqla("SELECT time, combo_count FROM fight_combos WHERE uid=".intval($attacker_uid)." ORDER BY time DESC LIMIT 1");
    
    if (!$last_attack) {
        // Первая атака
        $db->sql("INSERT INTO fight_combos (uid, time, combo_count, combo_type) VALUES (".intval($attacker_uid).", ".$now.", 1, '".$combo_type."')", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
        return array('combo' => 1, 'multiplier' => 1.0);
    }
    
    $time_diff = $now - $last_attack['time'];
    
    if ($time_diff <= COMBO_WINDOW) {
        // Продолжаем комбо
        $new_combo = min(intval($last_attack['combo_count']) + 1, MAX_COMBO);
        $multiplier = 1.0 + ($new_combo - 1) * 0.15; // +15% за каждый уровень комбо
        
        $db->sql("UPDATE fight_combos SET time=".$now.", combo_count=".$new_combo." WHERE uid=".intval($attacker_uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
        
        return array('combo' => $new_combo, 'multiplier' => $multiplier);
    } else {
        // Комбо прервано, начинаем заново
        $db->sql("UPDATE fight_combos SET time=".$now.", combo_count=1 WHERE uid=".intval($attacker_uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
        
        return array('combo' => 1, 'multiplier' => 1.0);
    }
}

/**
 * Улучшенный ИИ бота для атаки
 */
function bot_ai_attack($bot, $target) {
    // Выбор цели
    $action = rand(1, 100);
    
    if ($action <= 60) {
        // Обычная атака (60%)
        return 'attack';
    } elseif ($action <= 75) {
        // Защитная стойка (15%)
        return 'defend';
    } elseif ($action <= 85 && $bot['hp'] < $bot['chp'] * 0.3) {
        // Лечение если мало HP (10%)
        return 'heal';
    } elseif ($action <= 92) {
        // Специальная способность (7%)
        return 'special';
    } else {
        // Уклонение/блок (8%)
        return 'dodge';
    }
}

/**
 * Логирование боя с деталями
 */
function log_battle_action($fight_id, $actor, $action, $details = '') {
    GLOBAL $db;
    
    $time = date("H:i:s");
    $timestamp = tme();
    
    $log_message = format_battle_log($actor, $action, $details);
    
    $db->sql("INSERT INTO fight_log (cfight, log, time, timestamp, actor_uid) VALUES (".intval($fight_id).", '".mysql_real_escape_string($log_message)."', '".$time."', ".$timestamp.", ".intval($actor['uid']).")", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Обновляем статистику достижений
    update_battle_achievements($actor, $action, $details);
}

/**
 * Форматирование сообщения лога
 */
function format_battle_log($actor, $action, $details) {
    $color = '000000';
    
    switch($action) {
        case 'attack':
            $color = 'cc0000';
            if (isset($details['is_crit']) && $details['is_crit']) {
                $msg = "<b>".$actor['user']."</b> наносит <font color='#ff0000'><b>КРИТИЧЕСКИЙ</b></font> удар на <b>".$details['damage']."</b> урона!";
            } elseif (isset($details['is_dodge']) && $details['is_dodge']) {
                $msg = "<b>".$actor['user']."</b> атакует, но противник <font color='#00aa00'><b>уклоняется</b></font>!";
            } elseif (isset($details['is_block']) && $details['is_block']) {
                $msg = "<b>".$actor['user']."</b> атакует, но противник <font color='#0066cc'><b>блокирует</b></font> щитом!";
            } else {
                $msg = "<b>".$actor['user']."</b> атакует и наносит <b>".$details['damage']."</b> урона.";
            }
            break;
            
        case 'combo':
            $color = 'ff6600';
            $msg = "<b>".$actor['user']."</b> выполняет комбо-удар x".$details['combo']."! Множитель урона: <b>".round($details['multiplier'], 2)."</b>";
            break;
            
        case 'heal':
            $color = '00aa00';
            $msg = "<b>".$actor['user']."</b> лечится на <b>".$details['amount']."</b> HP.";
            break;
            
        case 'defend':
            $color = '0066cc';
            $msg = "<b>".$actor['user']."</b> переходит в защитную стойку.";
            break;
            
        case 'special':
            $color = '9900cc';
            $msg = "<b>".$actor['user']."</b> использует специальную способность: <i>".$details['name']."</i>!";
            break;
            
        case 'kill':
            $color = 'ff0000';
            $msg = "<b>".$actor['user']."</b> <font color='#ff0000'><b>УБИВАЕТ</b></font> противника!";
            break;
            
        default:
            $msg = $actor['user'].": ".$action;
    }
    
    return "<font color='#".$color."'>".$msg."</font>";
}

/**
 * Система достижений для боя
 */
function update_battle_achievements($pers, $action, $details) {
    GLOBAL $db;
    
    $achievements = array();
    
    // Первое убийство
    if ($action == 'kill') {
        $achievements[] = 'first_blood';
        
        // Серия убийств
        $kill_streak = get_kill_streak($pers['uid']);
        if ($kill_streak >= 5) $achievements[] = 'killing_spree';
        if ($kill_streak >= 10) $achievements[] = 'rampage';
        if ($kill_streak >= 25) $achievements[] = 'godlike';
    }
    
    // Критические удары
    if (isset($details['is_crit']) && $details['is_crit']) {
        $total_crits = get_total_crits($pers['uid']);
        if ($total_crits >= 100) $achievements[] = 'crit_master';
    }
    
    // Комбо
    if ($action == 'combo' && $details['combo'] >= MAX_COMBO) {
        $achievements[] = 'combo_king';
    }
    
    // Сохраняем достижения
    foreach ($achievements as $ach) {
        check_and_add_achievement($pers['uid'], $ach);
    }
}

/**
 * Получить серию убийств
 */
function get_kill_streak($uid) {
    GLOBAL $db;
    
    $streak = $db->sqlr("SELECT kill_streak FROM users WHERE uid=".intval($uid));
    return intval($streak);
}

/**
 * Получить общее количество критов
 */
function get_total_crits($uid) {
    GLOBAL $db;
    
    $crits = $db->sqlr("SELECT total_crits FROM users WHERE uid=".intval($uid));
    return intval($crits);
}

/**
 * Проверка и добавление достижения
 */
function check_and_add_achievement($uid, $achievement) {
    GLOBAL $db;
    
    // Проверяем, есть ли уже достижение
    $exists = $db->sqlr("SELECT COUNT(*) FROM user_achievements WHERE uid=".intval($uid)." AND achievement='".$achievement."'");
    
    if ($exists == 0) {
        // Добавляем достижение
        $db->sql("INSERT INTO user_achievements (uid, achievement, achieved_at) VALUES (".intval($uid).", '".$achievement."', ".tme().")", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
        
        // Обновляем счетчик достижений
        $db->sql("UPDATE users SET achievements_count=achievements_count+1 WHERE uid=".intval($uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
        
        // Отправляем уведомление (можно добавить в чат)
        $achievement_names = array(
            'first_blood' => 'Первая кровь',
            'killing_spree' => 'Серия убийц',
            'rampage' => 'Буйство',
            'godlike' => 'Божественный',
            'crit_master' => 'Мастер критов',
            'combo_king' => 'Король комбо'
        );
        
        if (isset($achievement_names[$achievement])) {
            $pers = $db->sqla("SELECT user FROM users WHERE uid=".intval($uid));
            say_to_chat('System', 'Игрок <b>'.$pers['user'].'</b> получает достижение: <font color="#ffd700">'.$achievement_names[$achievement].'</font>!', '*', 0, '*');
        }
    }
}

/**
 * Начать бой (улучшенная версия)
 */
function begin_fight_enhanced($bots_list, $perstowho, $message, $travm, $timeout, $f_type = 0, $location_type = 0) {
    GLOBAL $db, $player;
    
    // Создаем запись о бое
    $fight_data = array(
        'initiator' => $player->pers['uid'],
        'target' => $perstowho,
        'timeout' => $timeout,
        'travm' => $travm,
        'type' => ($f_type == 0) ? 'p' : 'b',
        'started' => tme()
    );
    
    // Вставляем в таблицу fights
    $fight_id = $db->sql("INSERT INTO fights (uid1, uid2, timeout, travm, type, ltime, closed, maxx, maxy, bplace) VALUES (".intval($fight_data['initiator']).", 0, ".$timeout.", ".$travm.", '".$fight_data['type']."', ".tme().", 0, 10, 10, ".intval($location_type).")", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    $fight_id = $db->insert_id();
    
    // Обновляем состояние игрока
    $db->sql("UPDATE users SET cfight=".$fight_id.", fstate=1, damage_get=0, damage_give=0 WHERE uid=".intval($player->pers['uid']), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Добавляем ботов в бой
    if (!empty($bots_list)) {
        $bot_array = explode('|', $bots_list);
        $team = 2; // Боты всегда во второй команде
        
        foreach ($bot_array as $bot_entry) {
            if (preg_match('/bot=(\d+)/', $bot_entry, $matches)) {
                $bot_id = intval($matches[1]);
                
                // Получаем данные бота
                $bot = $db->sqla("SELECT * FROM bots WHERE id=".$bot_id);
                
                if ($bot) {
                    // Добавляем бота в таблицу bots_battle
                    $db->sql("INSERT INTO bots_battle (cfight, id, user, chp, hp, level, xf, yf, cma, ma, fteam) VALUES (".$fight_id.", ".$bot_id.", '".$bot['user']."', ".$bot['hp'].", ".$bot['hp'].", ".$bot['level'].", ".rand(1, 10).", ".rand(1, 10).", ".$bot['ma'].", ".$bot['ma'].", ".$team.")", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
                }
            }
        }
    }
    
    // Логируем начало боя
    log_battle_action($fight_id, $player->pers, 'fight_start', array('enemy' => $perstowho));
    
    return $fight_id;
}

/**
 * Обработка конца боя
 */
function end_fight_enhanced($fight_id, $winner_uid, $loser_uid = 0) {
    GLOBAL $db;
    
    $fight = $db->sqla("SELECT * FROM fights WHERE id=".intval($fight_id));
    
    if (!$fight) return false;
    
    // Обновляем статус боя
    $db->sql("UPDATE fights SET type='f', result=".intval($winner_uid)." WHERE id=".intval($fight_id), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    // Получаем победителя
    $winner = $db->sqla("SELECT * FROM users WHERE uid=".intval($winner_uid));
    
    if ($winner) {
        // Награда победителю
        $exp_reward = rand(50, 150) * $fight['travm'] / 100;
        $gold_reward = rand(10, 50);
        
        $db->sql("UPDATE users SET exp=exp+".intval($exp_reward).", gold=gold+".intval($gold_reward).", kill_streak=kill_streak+1, total_crits=total_crits+0 WHERE uid=".intval($winner_uid), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
        
        // Логируем победу
        log_battle_action($fight_id, $winner, 'kill', array('victim' => $loser_uid));
        
        // Уведомление в чат
        if ($loser_uid > 0) {
            $loser = $db->sqla("SELECT user FROM users WHERE uid=".intval($loser_uid));
            say_to_chat('System', '<b>'.$winner['user'].'</b> победил в бою <b>'.$loser['user'].'</b>! Награда: '.intval($exp_reward).' опыта, '.intval($gold_reward).' золота.', '*', 0, '*');
        }
    }
    
    // Очищаем состояния участников
    $db->sql("UPDATE users SET cfight=0, fstate=0, damage_get=0, damage_give=0, kill_streak=0 WHERE cfight=".intval($fight_id), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    $db->sql("DELETE FROM bots_battle WHERE cfight=".intval($fight_id), __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    $db->sql("DELETE FROM fight_combos WHERE uid IN (SELECT uid FROM users WHERE cfight=".intval($fight_id).")", __FILE__,__LINE__,__FUNCTION__,__CLASS__);
    
    return true;
}

?>
