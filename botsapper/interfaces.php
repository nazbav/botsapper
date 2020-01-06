<?php
declare(strict_types = 1);
include_once 'labels.php';
/**
 * Created by PhpStorm.
 * User: Назым
 * Date: 03.05.2019
 * Time: 22:32
 */
function show_start() {
    set_buttons(['c' => 2, 'b' => 1], 'Играть', 'default', 0);
	set_buttons(['c' => 1, 'b' => 2], 'Топчик', 'default', 1);
    set_buttons(['c' => 1, 'b' => 1], 'Баланс', 'default', 1);
	set_buttons(['c' => 1, 'b' => 6], 'Бонус', 'default', 3);
  // set_buttons(['c' => 1, 'b' => 5], 'Команды', 'default', 3);
	//set_buttons(['c' => 5, 'b' => 0], 'Лол', 'default', 3);
 //   set_buttons(['c' => 4, 'b' => 0], 'VkCoin', 'default', 3);
}
function show_start2() {
	show_start();
   //set_buttons(['c' => 2, 'b' => 1], 'ByteCoin', 'default', 0);
  //  set_buttons(['c' => 4, 'b' => 0], 'VkCoin', 'default', 3);
}
/**
 * @param      $cell_info
 * @param bool $map_mode
 *
 * @return string
 */
function buttons_colors($cell_info, $map_mode = true) {
    if (!$map_mode) $cell_info = is_string($cell_info) ? $cell_info : 0;
    switch ($cell_info) {
        case 1:
        case 33:
            $color = 'positive';
            break;
        case 2:
            $color = 'primary';
            break;
        case 3:
        case 4:
        case 5:
        case 6:
        case 7:
        case 8:
        case 9:
        case 10:
            $color = 'negative';
            break;
        default:
            $color = 'default';
            break;
    }
    return $color;
}

/**
 * @param array $map_game
 * @param int   $button
 */
function show_user_map($map_game, $button = 4) {
    buttons_unset();
    $button_cell = ['c' => 2, 'b' => $button, 'x' => 0, 'y' => 0];////rand(1, 5000);
    $lines = 0;
    foreach ($map_game as $pos_y => $value) {
        foreach ($value as $pos_x => $cell_info) {
            $button_cell['x'] = $pos_x;
            $button_cell['y'] = $pos_y;
            set_buttons($button_cell, labels_map($cell_info), buttons_colors($cell_info, false), $lines);
        }
        $lines++;
    }
    // set_buttons(['c' => 2, 'b' => 6], "Подсказка", 'positive', $lines);
    set_buttons(['c' => 2, 'b' => 5], "Сдаться", 'negative', $lines);

}

/**
 * @param array $map_game
 */
function show_map_end(array $map_game) {
    buttons_unset();
    $lines = 1;//
    set_buttons(['c' => 0, 'b' => 0], "Выйти", 'primary', 0);

    foreach ($map_game as $pos_y => $value) {
        foreach ($value as $pos_x => $cell_info) {
            set_buttons(['c' => 0, 'b' => 0], labels_end_map($cell_info), buttons_colors($cell_info), $lines);
        }
        $lines++;
    }

}

/**
 * @param int $secs
 * @param int $short
 *
 * @return string
 */
function time_elapsed(int $secs, int $short = -1): string {
    $hours = $secs / 3600 % 24;
    $minutes = $secs / 60 % 60;
    $seconds = $secs % 60;
    $hours = $hours > 0 ? $hours < 10 ? '0' . $hours : $hours : '00';
    $minutes = $minutes > 0 ? $minutes < 10 ? '0' . $minutes : $minutes : '00';
    $seconds = $seconds > 0 ? $seconds < 10 ? '0' . $seconds : $seconds : '00';
    if ($short === 0) {
        return "{$hours}:{$minutes}";
    } elseif ($short === 1) {
        return "{$minutes}:{$seconds}";
    } else {
        return "{$hours}:{$minutes}:{$seconds}";
    }
}

/**
 * @param      $user_data
 * @param bool $full
 *
 * @return string
 */
function get_status($user_data, bool $full = false) {
    $labels = [['🏆', '250+ побед'], ['🏅', '100+ побед'], ['🎖', '50+ побед'], ['🌟', '20+ побед'], ['⭐', '10+ побед'], ['✨', '5+ побед'], ['🍳', 'яичница']];
    if ($user_data['wins'] >= 250) {
        $label = $labels[0];
    } elseif ($user_data['wins'] >= 100) {
        $label = $labels[1];
    } elseif ($user_data['wins'] >= 50) {
        $label = $labels[2];
    } elseif ($user_data['wins'] >= 20) {
        $label = $labels[3];
    } elseif ($user_data['wins'] >= 10) {
        $label = $labels[4];
    } elseif ($user_data['wins'] >= 5) {
        $label = $labels[5];
    } else {
        $label = $labels[6];
    }
    if ($full == true) {
        return $label[1] . ' (' . $label[0] . ')';
    } else {
        return $label[0];
    }
}

/**
 * @param     $user_all
 *
 * @param     $user_data
 * @param int $mines
 *
 * @return string
 * @throws \Krugozor\Database\Mysql\Exception
 * @throws Exception
 */
function show_top_users($user_all, $user_data, $mines = 0) {
    if ($mines == 0) {
        $message = "🎮 Топ игроков\n";
		$message .= "\n";   
   } else {
        $message = "🎮 Топ игроков на сложности {$mines} 💣\n";
		$message .= "\n";
	}
    if ($user_all) {
        $users = [];
        foreach ($user_all as $user_number => $value) {
            $user_id = (int)$value['user_id'];
            $scan_adm = array_search($user_id, ACCESS) !== false ? true : false;
            if (!$scan_adm) {
               $users[] = $value;
            }
        }
        $user_all = $users;
		
		$messa = array_fill(0,10," -- ");
        foreach ($user_all as $user_number => $value) {
            $user_id = (int)$value['user_id'];
            $label = get_status($value);
            switch ($user_number) {
                case 0:
                    $label .= '🥇 ';
                    break;
                case 1:
                    $label .= '🥈 ';
                    break;
                case 2:
                    $label .= '🥉 ';
                    break;
                default:
                    $label = $label . ' ';
                    break;
            }
            
			if ($mines == 0) {
			$sum_win = $value['balance'];
			} else {
			$sum_win = $value['sum_wins'];
			}
			
			if($sum_win <= 0 || $value['wins'] <= 0)
			{
				continue;
			}
			if(isset($value['sum_death'])){
				if(($sum_win-$value['sum_death']) < 0){
					continue;
				}
			$sum_win = ($sum_win-$value['sum_death']);	
			}
           if ($sum_win >= 1e9) $sum_win = ((int)($sum_win / 1e9)) . ' млрд.';  elseif ($sum_win >= 1e6) $sum_win = ((int)($sum_win / 1e6)) . ' млн.'; elseif ($sum_win >= 1e3) $sum_win = ((int)($sum_win / 1e3)) . ' тыс.';
            $u_wins = (int)$value['wins'];
            $wins_show = num_word($u_wins, ['победа', 'победы', 'побед']);
            if ($mines > 0) {
                $users_get = users_get($user_id);//TODO: слишком много чести для одного зверя.
                $messa[$user_number] = "{$label} @id{$user_id}({$users_get['first_name']}) {$sum_win} ({$wins_show})";
            } else {
                $messa[$user_number] = "{$label} @id{$user_id}({$value['first_name']}) {$sum_win} ({$wins_show})";
            }
 
        }
		$message .= implode($messa,"\n\r");
		
            
			if ($mines == 0) {
			$sum_win = $value['balance'];
			} else {
			$sum_win = $value['sum_wins'];
			}
			
		
        if ($mines == 0) {
            $label = get_status($user_data, true);

            $sum_win_show = toCoinShow($sum_win);
            $message .= "\n";
			 $message .= "\n";
            $message .= "🎮 Ваша статистика\n";
            $message .= "😎 Статус: {$label} \n";
            $message .= "🏆 Побед: {$user_data['wins']} \n";
            $message .= "📥 Выиграл: {$sum_win_show}\n";
        } else {
            $user_data = users_top((int)$user_data['user_id'], $mines);
            $message .= "\n";
			$message .= "\n";
            $message .= "🎮 Ваша статистика на сложности {$mines} 💣\n";
            if ($user_data) {
                $parameter = $user_data['wins'] >= 1 ? round($user_data['wins'] / ($user_data['death'] ?: 1), 3) : 0;


                $sum_win = toCoinShow($sum_win);
                $sum_death = toCoinShow($user_data['sum_death']);

                $label = get_status($user_data, true);

                $message .= "😎 Статус: {$label} \n";
                $message .= "🏆 Побед: {$user_data['wins']} \n";
                $message .= "👾 Проигрышей: {$user_data['death']} \n";
                $message .= "💎 Показатель: {$parameter} (Побед/Смертей)\n";
                $message .= "📥 Выиграл: {$sum_win}\n";
                $message .= "📤 Проиграл: {$sum_death}\n";
            } else {
                $message .= "Это Казахстан? ХМ, да, это Казахстан..\n";
            }
        }
        return $message;
    } else {
        return 'Не удалось сформировать топ, на этой сложности нет игр.';
    }
}

/**
 * @param $coin
 *
 * @return string
 */
function toCoinShow($coin) {
    return num_word((int)$coin, ['Серотонин', 'Серотонина', 'Серотонина']);
}

/**
 * @param      $value
 * @param      $words
 * @param bool $show
 *
 * @return string
 */
function num_word($value, $words, $show = true) {
    $number = $value % 100;
    if ($number > 19) {
        $number = $number % 10;
    }
    $value = number_format((float)$value, 0, '', ' ');
    $output = ($show) ? $value . ' ' : '';
    switch ($number) {
        case 1:
            $output .= $words[0];
            break;
        case 2:
        case 3:
        case 4:
            $output .= $words[1];
            break;
        default:
            $output .= $words[2];
            break;
    }
    return $output;
}

/**
 * @param $balance_show
 * @param $payment_show
 * @param $replenish_show
 *
 * @return string
 */
function show_balance($balance_show, $payment_show, $replenish_show) {
    //$message = "Ваш баланс:\n";

    $message = "👛 Кошелек: {$balance_show}\n";
   // $message .= "📤 Выплачено: {$payment_show} BNC \n";
   // $message .= "📥 Пополнено: {$replenish_show} BNC ";

   // set_buttons(['c' => 1, 'b' => 4], 'Вывод', 'negative', 2);
   // set_buttons(['c' => 1, 'b' => 3], 'Пополнить', 'negative', 2);
 //   
  //  set_buttons(['c' => 0, 'b' => 0], "Назад", 'default', 1);
    return $message;
}