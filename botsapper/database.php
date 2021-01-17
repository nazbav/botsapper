<?php

declare(strict_types=1);

/**
 * @param int   $user_id
 * @param array $user_data
 * @param int   $balance
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_add(int $user_id, array $user_data, int $balance = USER_START_BALANCE)
{
    global $database;
    $user_data = ['user_id' => $user_id, 'last_name' => $user_data['last_name'], 'first_name' => $user_data['first_name'], 'balance' => $balance, 'block' => 0];
    $database->query('INSERT INTO `users` SET ?As', $user_data);
}

/**
 * @param int $user_id
 * @param int $balance
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_update(int $user_id, int $balance)
{
    global $database;
    $database->query("UPDATE `users` SET `balance` = '?i' WHERE `user_id` = '?i';", $balance, $user_id);
}

/**
 * @param int $user_id
 * @param int $torment_mode
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_torment_mode(int $user_id, bool $torment_mode)
{
    global $database;
    $database->query("UPDATE `users` SET `torment_mode` = '?i' WHERE `user_id` = '?i';", $torment_mode, $user_id);
}

/**
 * @param int    $user_id
 * @param string $torment_cell
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_torment_cell(int $user_id, string $torment_cell)
{
    global $database;
    $database->query("UPDATE `users` SET `torment_cell` = '?i' WHERE `user_id` = '?i';", $torment_cell, $user_id);
}

/**
 * @param int  $user_id
 * @param bool $block
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_block(int $user_id, bool $block = true)
{
    global $database;
    $database->query("UPDATE `users` SET `block` = '?i' WHERE `user_id` = '?i';", $block, $user_id);
}

/**
 * @param int $user_id
 * @param int $payment
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_payment(int $user_id, int $payment)
{
    global $database;
    $database->query("UPDATE `users` SET `payment` = '?i' WHERE `user_id` = '?i';", $payment, $user_id);
}

/**
 * @param int $user_id
 * @param int $replenishment
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_replenish(int $user_id, int $replenishment)
{
    global $database;
    $database->query("UPDATE `users` SET `replenishment` = '?i' WHERE `user_id` = '?i';", $replenishment, $user_id);
}

/**
 * @param int $user_id
 * @param int $sum_wins
 * @param int $u_wins
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_wins(int $user_id, int $sum_wins, int $u_wins)
{
    global $database;
    $database->query("UPDATE `users` SET `sum_wins1` = '?i',`wins` = '?i' WHERE `user_id` = '?i';", $sum_wins, $u_wins, $user_id);
}

/**
 * @param int $user_id
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_bonus(int $user_id, int $bonus = 28800)
{
    global $database;
    $database->query("UPDATE `users` SET `bonus` = '?i' WHERE `user_id` = '?i';", time() + $bonus, $user_id);
}

/**
 * @param int $user_id
 * @param int $user_spectator
 * @param int $spectator_time
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_spectator(int $user_id, int $user_spectator, int $spectator_time)
{
    global $database;
    $database->query("UPDATE `users` SET `spectator` = '?i', `spectator_time` = '?i' WHERE `user_id` = '?i';", $user_spectator, $spectator_time, $user_id);
}

/**
 * @param int $user_id
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_delete(int $user_id)
{
    global $database;
    $database->query("DELETE FROM `users` WHERE `user_id` = '?i';", $user_id);
}

/**
 * @param int $user_id
 *
 * @throws \Krugozor\Database\Mysql\Exception
 * @throws Exception
 *
 * @return array
 */
function users_get(int $user_id)
{
    global $database;
    $result = $database->query("SELECT * FROM `users` WHERE `user_id` = '?i';", $user_id);
    $result = $result->fetch_assoc();
    if ($result) {
        return $result;
    } else {
        return [];
    }
}

/**
 * @param int $limit
 *
 * @throws \Krugozor\Database\Mysql\Exception
 *
 * @return array
 */
function users_get_all($limit = 11)
{
    global $database;
    $result = $database->query('SELECT * FROM `users` ORDER BY `balance` DESC LIMIT ?d', (int) $limit);
    $result = $result->fetch_assoc_array();
    if ($result) {
        return $result;
    } else {
        return [];
    }
}

/**
 * @param int $balance_min
 *
 * @throws \Krugozor\Database\Mysql\Exception
 *
 * @return array
 */
function users_get_bank($balance_min = 1)
{
    global $database;
    $result = $database->query("SELECT SUM(`balance`) FROM `users` WHERE `balance` >= '?d'", $balance_min);
    $result = $result->fetch_row();
    if ($result) {
        return $result;
    } else {
        return [];
    }
}

/**
 * @param int $user_id
 * @param int $mines
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_top_add(int $user_id, int $mines)
{
    global $database;
    $database->query('INSERT INTO `users_top` SET `user_id` = "?i", `mines` = "?i"', $user_id, $mines);
}

/**
 * @param int $top_id
 * @param int $mines
 * @param int $sum_wins
 * @param int $u_wins
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_top_win(int $top_id, int $mines, int $sum_wins, int $u_wins)
{
    global $database;
    $database->query('UPDATE `users_top` SET `sum_wins` = "?i",`wins` = "?i" WHERE `user_id` = "?i" AND `mines` = "?i";', $sum_wins, $u_wins, $top_id, $mines);
}

/**
 * @param int $user_id
 * @param int $member
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_member(int $user_id, int $member)
{
    global $database;
    $database->query('UPDATE `users` SET `is_member` = "?i" WHERE `user_id` = "?i";', $member, $user_id);
}

/**
 * @param int $user_id
 * @param int $mines
 * @param int $sum_death
 * @param int $death
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function users_top_death(int $user_id, int $mines, int $sum_death, int $death)
{
    global $database;
    $database->query('UPDATE `users_top` SET `sum_death` = "?i",`death` = "?i" WHERE `user_id` = "?i" AND `mines` = "?i";', $sum_death, $death, $user_id, $mines);
}

/**
 * @param int $user_id
 * @param int $mines
 *
 * @throws \Krugozor\Database\Mysql\Exception
 *
 * @return array|bool|\Krugozor\Database\Mysql\Statement
 */
function users_top(int $user_id, int $mines = 0)
{
    global $database;
    if ($mines == 0) {
        $result = $database->query("SELECT * FROM `users_top` WHERE `user_id` = '?i' ORDER BY (`sum_wins`-`sum_death`) DESC;", $user_id);
        $result = $result->fetch_assoc_array();
    } else {
        $result = $database->query("SELECT * FROM `users_top` WHERE `user_id` = '?i' AND `mines` = '?i' ORDER BY (`sum_wins`-`sum_death`) DESC;", $user_id, $mines);
        $result = $result->fetch_assoc();
    }
    if ($result) {
        return $result;
    } else {
        return [];
    }
}

/**
 * @param int $mines
 * @param int $limit
 *
 * @throws \Krugozor\Database\Mysql\Exception
 *
 * @return array
 */
function users_top_get(int $mines = MINES_MIN, int $limit = 10)
{
    global $database;
    if ($mines == 0) {
        $result = $database->query('SELECT * FROM `users_top`');
        $result = $result->fetch_assoc_array();
    } else {
        $result = $database->query('SELECT * FROM `users_top` WHERE `mines` = "?i" ORDER BY (`sum_wins`-`sum_death`) DESC LIMIT ?d', $mines, (int) $limit);
        $result = $result->fetch_assoc_array();
    }
    if ($result) {
        return $result;
    } else {
        return [];
    }
}

/**
 * @param string $map_key
 * @param array  $map_game
 * @param int    $mine_count
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function maps_add(string $map_key, array $map_game, int $mine_count)
{
    global $database;
    $user_data = ['map_key' => $map_key, 'mine_count' => $mine_count, 'cell_open' => 0, 'map_game' => json_encode($map_game)];
    $database->query('INSERT INTO `maps` SET ?As', $user_data);
}

/**
 * @param string $map_key
 * @param array  $map_game
 * @param int    $cell_open
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function maps_update(string $map_key, array $map_game, int $cell_open)
{
    global $database;
    $database->query("UPDATE `maps` SET `cell_open` = '?i',`map_game` = '?s' WHERE `map_key` = '?s';", $cell_open, json_encode($map_game), $map_key);
}

/**
 * @param string $map_key
 *
 * @throws \Krugozor\Database\Mysql\Exception
 * @throws Exception
 *
 * @return array|bool|\Krugozor\Database\Mysql\Statement
 */
function maps_get(string $map_key)
{
    global $database;
    $result = $database->query("SELECT * FROM `maps` WHERE `map_key` = '?s';", $map_key);
    $result = $result->fetch_assoc();
    if ($result) {
        $result['map_game'] = json_decode($result['map_game']);

        return $result;
    } else {
        return [];
    }
}

/**
 * @param int    $user_id
 * @param int    $time
 * @param string $map_key
 * @param int    $coast
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function games_add(int $user_id, int $time, string $map_key, int $coast)
{
    global $database;
    $user_data = ['map_key' => $map_key, 'user_id' => $user_id, 'coast' => $coast, 'time' => $time];
    $database->query('INSERT INTO `games` SET ?As', $user_data);
}

/**
 * @param string $map_key
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function games_delete(string $map_key)
{
    global $database;
    $database->query("DELETE FROM `games` WHERE `map_key` = '?s';", $map_key);
    $database->query("DELETE FROM `maps` WHERE `map_key` = '?s';", $map_key);
}

/**
 * @param string $map_key
 * @param int    $coast
 * @param int    $help
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function games_update(string $map_key, int $coast)
{
    global $database;
    $database->query("UPDATE `games` SET `coast` = '?i' WHERE `map_key` = '?s';", $coast, $map_key);
}

/**
 * @param string $map_key
 * @param int    $help
 *
 * @throws \Krugozor\Database\Mysql\Exception
 */
function maps_help(string $map_key, int $help = 0)
{
    global $database;
    $database->query("UPDATE `maps` SET `help` = '?i'  WHERE `map_key` = '?s';", $help, $map_key);
}

/**
 * @param int $user_id
 *
 * @throws \Krugozor\Database\Mysql\Exception
 * @throws Exception
 *
 * @return array
 */
function games_get(int $user_id)
{
    global $database;
    $result = $database->query("SELECT * FROM `games` WHERE `user_id` = '?i';", $user_id);
    $result = $result->fetch_assoc();
    if ($result) {
        return $result;
    } else {
        return [];
    }
}
