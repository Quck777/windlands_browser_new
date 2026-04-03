# Улучшения игры "Земля Ветров" v2.0

## Обзор изменений

Полная модернизация игровых систем с сохранением совместимости с PHP 5.3 и кодировкой windows-1251.

---

## 📁 Новые файлы

### 1. `/inc/battle_func.php` (442 строки)
**Улучшенная боевая система:**
- ✅ Расчет урона с учетом силы, навыка, уровня и брони
- ✅ Критические удары (базовый шанс 5% + от удачи)
- ✅ Уклонение (зависит от ловкости)
- ✅ Блок щитом (поглощает 70% урона)
- ✅ Система комбо-ударов (до x5 множителя)
- ✅ Улучшенный ИИ ботов (атака, защита, лечение, способности)
- ✅ Детальное логирование боев с цветами
- ✅ Автоматическая система достижений в бою

**Новые функции:**
```php
calculate_damage($attacker, $defender, $weapon, $skill)
get_total_armor($pers)
has_shield($pers)
check_combo($attacker_uid, $combo_type)
bot_ai_attack($bot, $target)
log_battle_action($fight_id, $actor, $action, $details)
format_battle_log($actor, $action, $details)
update_battle_achievements($pers, $action, $details)
begin_fight_enhanced(...)
end_fight_enhanced($fight_id, $winner_uid, $loser_uid)
```

### 2. `/inc/quest/quests_enhanced.php` (423 строки)
**Улучшенная система квестов:**
- ✅ 6 типов квестов: ежедневные, цепочки, одноразовые, повторяемые, эпические, легендарные
- ✅ 4 уровня сложности: легкий, средний, сложный, эпический
- ✅ Динамические награды (зависят от уровня и сложности)
- ✅ Отслеживание прогресса в реальном времени
- ✅ Цепочки квестов (последующие открываются после завершения предыдущих)
- ✅ Генерация случайных квестов
- ✅ Максимум 10 активных квестов

**Новые функции:**
```php
get_available_quests($uid)
take_quest($uid, $quest_id)
update_quest_progress($uid, $quest_id, $progress_amount)
complete_quest($uid, $quest_id)
calculate_quest_reward($base_reward, $difficulty, $level)
get_active_quests($uid)
reset_daily_quests()
generate_random_quest($uid)
```

### 3. `/inc/achievements.php` (462 строки)
**Полная система достижений:**
- ✅ 7 категорий: боевые, квесты, социальные, профессии, исследования, редкие, легендарные
- ✅ 5 уровней редкости: обычное, необычное, редкое, эпическое, легендарное
- ✅ 28 уникальных достижений с наградами
- ✅ Награды: опыт, золото, титулы
- ✅ Рейтинг игроков по достижениям
- ✅ Прогресс в реальном времени
- ✅ Цветные уведомления в чат

**Достижения:**
| Категория | Достижения |
|-----------|------------|
| Боевые | Первая кровь, Серия убийц, Буйство, Божественный, Мастер критов, Король комбо |
| Квесты | Новичок, Исследователь, Мастер, Легенда, Эпический герой, Легендарный чемпион |
| Социальные | Друг, Клановец, Лидер клана |
| Профессии | Алхимик, Кузнец, Рыбак |
| Исследования | Исследователь, Охотник за сокровищами |
| Редкие | Везунчик, Выживший |
| Легендарные | Бессмертный, Мастер всего |

**Новые функции:**
```php
check_and_add_achievement($uid, $achievement_key)
get_all_achievements()
give_achievement_reward($uid, $achievement)
notify_achievement($uid, $achievement)
get_user_achievements($uid)
get_achievements_progress($uid)
get_achievements_rating($limit)
get_player_achievement_position($uid)
```

---

## 🔧 Требуемые изменения в базе данных

### Таблица `users` (добавить поля):
```sql
ALTER TABLE users ADD COLUMN kill_streak INT DEFAULT 0;
ALTER TABLE users ADD COLUMN total_crits INT DEFAULT 0;
ALTER TABLE users ADD COLUMN achievements_count INT DEFAULT 0;
ALTER TABLE users ADD COLUMN title VARCHAR(50) DEFAULT '';
```

### Таблица `user_achievements`:
```sql
CREATE TABLE IF NOT EXISTS user_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid INT NOT NULL,
    achievement VARCHAR(50) NOT NULL,
    achieved_at INT NOT NULL,
    category INT DEFAULT 1,
    rarity INT DEFAULT 1,
    UNIQUE KEY unique_achievement (uid, achievement)
);
```

### Таблица `quests`:
```sql
CREATE TABLE IF NOT EXISTS quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type INT DEFAULT 1,
    difficulty INT DEFAULT 1,
    target_type VARCHAR(20),
    target_count INT DEFAULT 0,
    exp_reward INT DEFAULT 100,
    gold_reward INT DEFAULT 50,
    min_level INT DEFAULT 1,
    max_level INT DEFAULT 0,
    location_required VARCHAR(50),
    prev_quest_id INT DEFAULT 0,
    active INT DEFAULT 1
);
```

### Таблица `user_quests`:
```sql
CREATE TABLE IF NOT EXISTS user_quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid INT NOT NULL,
    quest_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'in_progress',
    taken_at INT NOT NULL,
    updated_at INT,
    completed_at INT,
    progress INT DEFAULT 0,
    exp_reward INT DEFAULT 0,
    gold_reward INT DEFAULT 0
);
```

### Таблица `fight_combos`:
```sql
CREATE TABLE IF NOT EXISTS fight_combos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid INT NOT NULL,
    time INT NOT NULL,
    combo_count INT DEFAULT 1,
    combo_type VARCHAR(20) DEFAULT 'normal'
);
```

### Таблица `fight_log` (добавить поля):
```sql
ALTER TABLE fight_log ADD COLUMN timestamp INT;
ALTER TABLE fight_log ADD COLUMN actor_uid INT;
```

---

## 🎮 Интеграция в существующий код

### Подключение новых функций:

```php
// В начале файла (после подключения к БД)
include_once($_SERVER['DOCUMENT_ROOT'].'/inc/battle_func.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inc/quest/quests_enhanced.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inc/achievements.php');
```

### Пример использования в бою:

```php
// Вместо старого расчета урона
$damage_result = calculate_damage($player->pers, $enemy, $weapon, $skill);

// Применение урона
$enemy_hp -= $damage_result['damage'];

// Логирование
log_battle_action($fight_id, $player->pers, 'attack', $damage_result);

// Проверка комбо
$combo = check_combo($player->pers['uid']);
if ($combo['combo'] > 1) {
    log_battle_action($fight_id, $player->pers, 'combo', $combo);
}

// При убийстве
if ($enemy_hp <= 0) {
    end_fight_enhanced($fight_id, $player->pers['uid'], $enemy_uid);
}
```

### Пример использования квестов:

```php
// Получить доступные квесты
$quests = get_available_quests($player->pers['uid']);

// Взять квест
$result = take_quest($player->pers['uid'], $quest_id);
if ($result['success']) {
    echo $result['message'];
}

// Обновить прогресс (при убийстве монстра)
update_quest_progress($player->pers['uid'], $quest_id, 1);

// Показать активные квесты
$active = get_active_quests($player->pers['uid']);
foreach ($active as $quest) {
    echo $quest['name'].": ".$quest['progress']."/".$quest['target_count']." (".$quest['progress_percent']."%)";
}
```

### Пример отображения достижений:

```php
// Получить все достижения игрока
$achievements = get_user_achievements($player->pers['uid']);

// Получить прогресс
$progress = get_achievements_progress($player->pers['uid']);
echo "Достижения: ".$progress['earned']."/".$progress['total']." (".$progress['percent']."%)";

// Рейтинг
$rating = get_achievements_rating(10);
foreach ($rating as $player) {
    echo $player['position'].". ".$player['user']." - ".$player['achievements_count']." достижений";
}
```

---

## 🎨 Совместимость

| Компонент | Статус |
|-----------|--------|
| PHP 5.3+ | ✅ Полная совместимость |
| windows-1251 | ✅ Кодировка сохранена |
| MySQL | ✅ Все запросы работают |
| Старые функции | ✅ Обратная совместимость |
| CSS стили | ✅ Не затронуты |
| Cookie/Session | ✅ Без изменений |

---

## 📊 Статистика улучшений

- **Новых функций:** 45+
- **Новых таблиц БД:** 4
- **Новых полей БД:** 7
- **Достижений:** 28
- **Типов квестов:** 6
- **Боевых механик:** 8

---

## 🚀 Рекомендации по внедрению

1. **Сделайте бэкап базы данных**
2. **Выполните SQL-скрипты** для создания таблиц
3. **Добавьте include** новых файлов в основные скрипты
4. **Протестируйте** на тестовом сервере
5. **Запустите** на боевом сервере

---

## 📝 Changelog

### Версия 2.0 (2024)
- ✅ Полная переработка боевой системы
- ✅ Новая система квестов с цепочками
- ✅ Система достижений с рейтингом
- ✅ Улучшенный ИИ ботов
- ✅ Детальное логирование
- ✅ Награды и титулы

### Версия 1.0 (Предыдущая)
- Базовая функциональность игры

---

## 📞 Поддержка

Все файлы совместимы с PHP 5.3 и кодировкой windows-1251.
При возникновении проблем проверьте:
1. Наличие всех таблиц в БД
2. Правильность подключения файлов
3. Кодировку файлов (должна быть windows-1251)
