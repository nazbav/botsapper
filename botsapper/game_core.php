<?php
declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: Назым
 * Date: 21.04.2019
 * Time: 23:58
 */
/**
 * @param        $x_pos
 * @param        $y_pos
 * @param        $map_game
 *
 * @param        $count_open
 *
 * @return bool
 */
function sapper($x_pos, $y_pos, &$map_game, &$count_open) {
    if (is_string($map_game[$y_pos][$x_pos])) {
        return 2;
    } elseif (isset($map_game[$y_pos][$x_pos]) && $map_game[$y_pos][$x_pos] != 9) {
        sapper_open($x_pos, $y_pos, $map_game, $count_open);
        return true;
    } else {
        $map_game[$y_pos][$x_pos] = 10;
        return false;
    }

}

function open_cell($cell) {
    return (string)$cell;
}

// открывает текущую и все соседние с ней клетки,
// в которых нет мин
function sapper_open($x_pos, $y_pos, &$map_game, &$count_open) {
    if (isset($map_game[$y_pos][$x_pos]) && !is_string($map_game[$y_pos][$x_pos]) && $map_game[$y_pos][$x_pos] == 0) {

        $map_game[$y_pos][$x_pos] = open_cell($map_game[$y_pos][$x_pos]);
        $count_open = $count_open + 1;
        // открыть примыкающие клетки
        sapper_open($x_pos - 1, $y_pos, $map_game, $count_open);
        sapper_open($x_pos, $y_pos - 1, $map_game, $count_open);
        sapper_open($x_pos + 1, $y_pos, $map_game, $count_open);
        sapper_open($x_pos, $y_pos + 1, $map_game, $count_open);
        //примыкающие диагонально
        sapper_open($x_pos - 1, $y_pos - 1, $map_game, $count_open);
        sapper_open($x_pos + 1, $y_pos + 1, $map_game, $count_open);
        sapper_open($x_pos + 1, $y_pos - 1, $map_game, $count_open);
        sapper_open($x_pos - 1, $y_pos + 1, $map_game, $count_open);
    } elseif (isset($map_game[$y_pos][$x_pos]) && !is_string($map_game[$y_pos][$x_pos])) {
        $map_game[$y_pos][$x_pos] = open_cell($map_game[$y_pos][$x_pos]);
        $count_open++;
    }
}

/**
 * @param     $count
 * @param     $map_game
 * @param int $x_max
 * @param int $y_max
 *
 * @return mixed
 */
function miner($count, $map_game, $x_max = 3, $y_max = 7) {
    for ($iterator = 0; $iterator < $count; $iterator++) {
        $x_pos = rand(0, $x_max);
        $y_pos = rand(0, $y_max);
        if (isset($map_game[$y_pos][$x_pos])) {
            if ($map_game[$y_pos][$x_pos] != 9) {
                $map_game[$y_pos][$x_pos] = 9;
                $map_game = mine($y_pos, $x_pos, $map_game);
            } else {
                $map_game = miner(1, $map_game);
            }
        }
    }
    return $map_game;
}

/**
 * @param      $map_game
 * @param bool $chance
 * @param int  $x_max
 * @param int  $y_max
 *
 * @return array
 */
function get_chest($map_game, $chance = false, $x_max = 3, $y_max = 7) {
    if (!$chance) $chance = (rand(1, CHEST_CHANCE) == 1) ? true : false;
    if ($chance) {
        $x_chest = rand(0, $x_max);
        $y_chest = rand(0, $y_max);
        if ($map_game[$y_chest][$x_chest] == 1) {
            $map_game[$y_chest][$x_chest] = 33;
        } else {
            get_chest($map_game, $chance);
        }
    }
    return $map_game;
}

/**
 * @param $y_pos
 * @param $x_pos
 * @param $map_game
 *
 * @return mixed
 */
function mine($y_pos, $x_pos, $map_game) {
    $map_array = SplFixedArray::fromArray([[-1, -1], [0, -1], [1, -1], [-1, 0], [0, 0], [1, 0], [-1, 1], [0, 1], [1, 1],]);
    foreach ($map_array as $index => $value) {
        if (isset($map_game[$y_pos + $value[1]][$x_pos + $value[0]])) {
            $map_cell = $map_game[$y_pos + $value[1]][$x_pos + $value[0]];
            if ($map_cell != 9) $map_game[$y_pos + $value[1]][$x_pos + $value[0]] = ++$map_cell;
        }
    }
    return $map_game;
}


/**
 * @param $map_game
 *
 * @return mixed
 */
function get_bonus($map_game) {
    $bonus = 0;
    foreach ($map_game as $index) {
        foreach ($index as $value) {
            if ($value == 33) $bonus += rand(BONUS_MIN, BONUS_MAX);
            if ($value != 0) $bonus += $value;
        }
    }
    return $bonus;
}