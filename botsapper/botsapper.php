<?php

declare(strict_types=1);

ini_set('date.timezone', 'Europe/Volgograd');

use Krugozor\Database\Mysql\Mysql;

include_once '../vendor/autoload.php';
include_once 'config.php';
include_once 'config_bot.php';
include_once 'vk_api.php';
include_once 'game_core.php';
include_once 'database.php';
include_once 'interfaces.php';

/**
 * @param $map_game
 * @param $user_game
 * @param $user_data
 *
 * @return array
 */
function check_map($map_game, $user_game, $user_data): array
{
    foreach ($map_game as $pos_y => $value) {
        foreach ($value as $pos_x => $cell_info) {
            $map_game[$pos_y][$pos_x] = (int) $map_game[$pos_y][$pos_x];
        }
    }
    $amount = (int) $user_game['coast'];
    $user_id = (int) $user_data['user_id'];
    $map_key = md5($user_id.'|'.$amount.'|'.json_encode($map_game));
    $check_true = $map_key == $user_game['map_key'];
    $message = $check_true ? "\n✅ Игра признана подлинной" : "\n\n‼ Система выявила нарушение подлинности игры\nВ связи с выявлением нарушения целостности игры, ваша ставка аннулируется, а игра признается подстроенной.";

    return [$message, $check_true];
}

/**
 * @param     $user_map
 * @param int $sapper_open
 *
 * @return int
 */
function cell_open($user_map, int $sapper_open): int
{
    $cell_open = (32 - ($user_map['cell_open'] + $sapper_open)) - $user_map['mine_count'];

    return $cell_open;
}

/**
 * @param int     $clicks
 * @param Closure $color_rand
 */
function tikva_keys(int $clicks, Closure $color_rand)
{
    set_buttons(['c' => 3, 'b' => 1, 'c11k5' => $clicks], '🎃', $color_rand(1), 1);

    set_buttons(['c' => 3, 'b' => 2, 'c11k5' => $clicks], '🎃', $color_rand(2), 1);

    set_buttons(['c' => 3, 'b' => 3, 'c11k5' => $clicks], '🎃', $color_rand(3), 1);

    set_buttons(['c' => 3, 'b' => 4, 'c11k5' => $clicks], '🎃', $color_rand(4), 2);

    set_buttons(['c' => 3, 'b' => 5, 'c11k5' => $clicks], '🎃', $color_rand(5), 2);

    set_buttons(['c' => 3, 'b' => 6, 'c11k5' => $clicks], '🎃', $color_rand(6), 2);

    set_buttons(['c' => 3, 'b' => 7, 'c11k5' => $clicks], '🎃', $color_rand(7), 3);

    set_buttons(['c' => 3, 'b' => 8, 'c11k5' => $clicks], '🎃', $color_rand(8), 3);

    set_buttons(['c' => 3, 'b' => 9, 'c11k5' => $clicks], '🎃', $color_rand(9), 3);
}

try {
    /**
     * @return array
     */
    function method_replenish(): array
    {
        $message = "👻 Одноразовая ссылка для оплаты: 👻\n https://vk.com/app6948819#transfer_service=notsapper\n\n👿 Не получил? 👿\n Вызывай администратора: смс nazbav (текст сообщения)";
        // $message = "Пополнение невозможно по техническим причинам.";
        return [$message];
    }

    /**
     * @return string
     */
    function method_commands(): string
    {
        $message = "Список доступных текстовых команд:\n1. баланс -- показывает ваш баланс\n3. бонус -- ежедневный бонус\n4. (любое целое число) -- ставка\n5. смс (id) (текст) -- отправляет сообщение игроку\n6. флаг -- активирует функцию подтверждения хода в игре\n\n";
        //$bot_bank = (float)info()['data']['coins'];
        //$balance_users = users_get_bank();
        //  $bank_users = (float)$balance_users[0];
        //   $bot_bank = $bot_bank - $bank_users;

        // $bot_bank = toCoinShow($bot_bank);
        //$bank_users = toCoinShow($bank_users);
        //  $message .= "\nБанк игры: {$bot_bank}\nСумма балансов игроков: {$bank_users}";
        return $message;
    }

    /**
     * @param $user_id
     *
     * @return string
     */
    function method_user($user_id): string
    {
        $user_information = users_get($user_id);
        $user_top = users_top($user_id);

        $message = "\nИнформация о пользователе:";
        if ($user_information) {
            $message .= "\n📗 Код: ".$user_information['user_id'];
            $message .= "\n📘 Игрок: ".$user_information['first_name'].' '.$user_information['last_name'];
            $message .= "\n📕 Блокировка: ".($user_information['block'] ? 'есть' : 'нет');
            $message .= "\n📙 Ссылка: https://vk.com/id".$user_information['user_id'];

            $parameter = number_format((float) $user_information['balance'], 0, '', ' ');
            $message .= "\n\n👛 Баланс: ".$parameter;
            $message .= "\n🏆 Побед: ".$user_information['wins'];
            $parameter = number_format((float) $user_information['sum_wins'], 0, '', ' ');
            $message .= "\n🍗 Куш: ".$parameter;
            $parameter = number_format((float) $user_information['payment'], 0, '', ' ');
            $message .= "\n📤 Выведено: ".$parameter;
            $parameter = number_format((float) $user_information['replenishment'], 0, '', ' ');
            $message .= "\n📥 Пополнено: ".$parameter."\n";
        }
        if (is_array($user_top)) {
            foreach ($user_top as $value) {
                $message .= "\nСложность: ".$value['mines'].' 💣';
                $message .= "\n🏆 Побед: ".$value['wins'];
                $message .= "\n👾 Проигрышей: ".$value['death'];
                $parameter = $value['wins'] >= 1 ? round($value['wins'] / ($value['death'] ?: 1), 3) : 0;
                $message .= "\n💎 W/D: ".$parameter;
                $parameter = number_format((float) $value['sum_wins'], 0, '', ' ');
                $message .= "\n📥 Выиграл: ".$parameter;
                $parameter = number_format((float) $value['sum_death'], 0, '', ' ');
                $message .= "\n📤 Проиграл: ".$parameter."\n";
            }
        }

        return $message;
    }

    $request = json_decode(file_get_contents('php://input'), true);
    if (!isset($_REQUEST)) {
        exit('ok');
    }

    if (isset($request['type']) || isset($request['event'])) {
        $database = $request['type'] == 'message_new' || $request['event'] == 'transfer' ? Mysql::create(DB_HOST, DB_USER, DB_PASSWORD, DB_PORT)// Выбор базы данных
    ->setDatabaseName(DB_NAME)->setCharset('utf8') : null;
    }

    /*     if (isset($request['event'])) {
            switch ($request['event']) {
                case 'transfer':
                    $amount = (int)$request['data']['sum'];
                    if ($amount >= 1) {
                        $user_id = (int)$request['data']['user_id'];
                        $user_data = users_get($user_id);
                        if ($user_data) {
                            if ($user_data['block'] != true) {
                            users_update($user_id, (int)($amount + $user_data['balance']));
                            users_replenish($user_id, (int)($user_data['replenishment'] + $amount));
                            $amount = toCoinShow($amount);
                            vk_send($user_id, "Поступил платеж!\n {$request['data']['created_at_text']} (MSK)\nЗачислено: {$amount}. \n");
                            }
                        }
                    }
                    exit('ok');
                    break;
            }
        } elseif (GROUP_ID != $request['group_id'] || SECRET_KEY != $request['secret']) {
            exit('ok');
        } */

    switch ($request['type']) {
        case 'message_new':
            $attachments = [];
            $user_id = (int) $request['object']['from_id'];
            $start_message = $message = 'Я тебя не понял! Используй клавиатуру, и будет счастье ^-^ ';

            $user_data = users_get($request['object']['from_id']);
            $new_user = false;

            if (!$user_data) {
                vk_user_info($request);
                users_add($user_id, $request['user']); //array
                $new_user = true;
            } else {
                $request['user'] = $user_data;
            }
            if ($new_user) {
                $message = $request['user']['first_name'].", Приветствую тебя!\nСвязь с администратором: \"смс nazbav (краткое сообщение)\"\n Выбирите валюту для игры:";
                show_start2();
            } elseif (isset($request['object']['payload'])) {
                if (isset($request['user']['block']) && $request['user']['block'] == true) {
                    break;
                }

                $use_object = json_decode($request['object']['payload'], true);

                if (isset($use_object['c'], $use_object['b'])) {
                    $command_code = $use_object['c'];
                    $command_buttons = $use_object['b'];

                    if ($command_code >= 0 && $command_code <= 10) {
                        //Обработка команд
                        switch ($command_code) {
                          case 4:
                             /*  	$message = "Для игры на VkCoin перейдите по ссылке:\n
                                !! Игра на вк коин времмено приостановлена, подробнее в сообществе: \n
                                https://vk.me/notsappervkc";*/
                            break;
                            case 0:
                                if (START_STOP) {
                                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                                    if (!$scan_adm) {
                                        $message = 'Технические работы! Давай прервемся на '.TIME_TECH_WORK.' минут(у)';
                                        break;
                                    }
                                }
                                show_start();
                                $message = "Привет {$user_data['first_name']}, выбери один из пунктов:";
                                break;
                            case 1:
                                if (BALANCE_STOP) {
                                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                                    if (!$scan_adm) {
                                        $message = 'Технические работы! Давай прервемся на '.TIME_TECH_WORK.' минут(у)';
                                        break;
                                    }
                                }
                                switch ($command_buttons) {
                                    case 1:
                                        $balance_show = num_word((int) $user_data['balance'], ['Серотонин', 'Серотонина', 'Серотонина']);
                                        $payment_show = number_format((float) $user_data['payment'], 0, '', ' ');
                                        $replenish_show = number_format((float) $user_data['replenishment'], 0, '', ' ');
                                        $message = show_balance($balance_show, $payment_show, $replenish_show);
                                        break;
                                    case 2:
                                        if (isset($use_object['m'])) {
                                            $mines_top = (int) $use_object['m'];
                                            $user_all = users_top_get($mines_top);
                                        } else {
                                            $mines_top = 0;
                                            $user_all = users_get_all();
                                        }

                                        $count = 0;
                                        $lines = 0;
                                        $color = ['positive', 'primary', 'negative'];
                                        $user_wins = (int) $user_data['wins'];
                                        $count_diff = count(MINES) - 1;

                                        $user_wins = $user_wins >= $count_diff ? $count_diff : $user_wins;
                                        for ($integer = 0; $integer <= $user_wins; $integer++) {
                                            if ($count >= 3) {
                                                $count = 0;
                                                $lines++;
                                            }
                                            $colors = isset($color[$lines]) ? $color[$lines] : 'default';
                                            $mines = $integer + 3;
                                            set_buttons(['c' => 1, 'b' => 2, 'm' => $mines], "{$mines} 💣", $colors, $lines);
                                            $count++;
                                        }
                                        set_buttons(['c' => 1, 'b' => 2], '🏆', 'positive', ++$lines);
                                        set_buttons(['c' => 0, 'b' => 0], 'Назад', 'default', ++$lines);

                                        $message = show_top_users($user_all, $user_data, $mines_top);
                                        break;
                                    case 3:
                                        list($message) = method_replenish();
                                        show_start();
                                        break;
                                    case 4:
                                        /*
                                        $bot_bank = (float)info()['data']['coins'];
                                        $balance_users = users_get_bank();
                                        $bank_users = (float)$balance_users[0];
                                        $bot_bank = $bot_bank - $bank_users;
                                        if (GAME_WITHDRAW && $bot_bank >= SAVE_BANK) {
                                            //  if ($user_data['replenishment'] >= USER_START_BALANCE) {
                                            $balance = (float)$user_data['balance'];
                                            if (isset($use_object['a'])) {
                                                $amount = (int)$use_object['a'];
                                            } else {
                                                $amount = (float)$balance;
                                            }
                                            $amount_show = toCoinShow($amount);
                                            $balance_show = toCoinShow($balance);
                                            if ($balance >= $amount) {
                                                if ($balance <= MAX_PAY && $amount >= MIN_PAY && $amount <= MAX_PAY) {

                                                    users_update($user_id, (int)($balance - $amount));
                                                    users_payment($user_id, (int)($user_data['payment'] + $amount));

                                                    $pay_coins = coin_send($user_id, (int)$amount);
                                                    if (!isset($pay_coins['status']) && $pay_coins['status'] == 'ok') {
                                                        users_block($user_id);
                                                        users_update($user_id, (int)$balance);
                                                        users_payment($user_id, (int)$user_data['payment']);
                                                        vk_send(ACCESS[0], "У @id{$user_id}(пользователя) возникла ошибка вывода баланса:\n" . json_encode($pay_coins) . "\n\nuser_id: {$user_id}\namount: {$amount}");

                                                        $message = "Видимо, я не отправил: {$amount_show}, я позову администратора!\n Ты переключен в режим разговора с администратором, ожидай ответа! Обработка команд отключена!";

                                                        set_clear();
                                                    } else {
                                                        $message = "Я отправил: {$amount_show}" . (COMMISSION > 0 ? ", почтовым голубем, он с дуру полетел над гоп. районом! Блин, его щеманули гопники на " . COMMISSION . "%" : ".");
                                                    }
                                                     show_start();
                                                    break;
                                                } else {
                                                    $min_pay = number_format((float)MIN_PAY, 0, ',', ' ');
                                                    $max_pay = number_format((float)MAX_PAY, 0, ',', ' ');
                                                    $message = sprintf("Выводить можно если баланс, или ваша сумма вывода от %s и до %s серотонина.\nВаш баланс: %s.\n\n", $min_pay, $max_pay, $balance_show);
                                                    if ($amount > MAX_PAY) {
                                                        users_block($user_id);
                                                        vk_send(ACCESS[0], "У @id{$user_id}(пользователя) возникла ошибка вывода баланса:\n-- Сумма больше лимита\n\nuser_id: {$user_id}\namount: {$amount}");
                                                        set_clear();
                                                        $message .= "Я позову администратора!\n Ты переключен в режим разговора с администратором, ожидай ответа! Обработка команд отключена!";
                                                    }
                                                     show_start();
                                                    break;
                                                }
                                            } else {
                                                $message = "У вас нет {$amount_show} серотонина.";
                                                 show_start();
                                                break;
                                            }
                                            //} else {
                                            //     $start_balance = number_format((float)(USER_START_BALANCE - $user_data['replenishment']), 0, ',', ' ');
                                            //      $message = "Выводы доступны только при пополнении кошелька на {$start_balance} серотонина.";
                                            //      break;
                                            //  }
                                        } else {
                                            $save_bank = number_format(SAVE_BANK, 3, ',', ' ');
                                            $message = "Выводы временно заблокированы или банк игры меньше " . $save_bank;
                                            break;
                                        }
                                        show_start(); */
                                        break;
                                    case 5:
                                        $message = method_commands();
                                        break;
                                    case 6:
                                        if (isset($user_data['bonus']) && $user_data['bonus'] <= $_SERVER['REQUEST_TIME'] || $user_data['bonus'] == 0) {
                                            $bonus = BONUS_MAX;
                                            if ((int) $user_data['wins'] > 0) {
                                                //	$all = 28800;
                                                //	$min_bonus = 30;
                                                $bonus += $user_data['wins'] * USER_WINS_BONUS;
                                            // for($i=($all/$min_bonus);$i>=0;$i-$min_bonus){
                                                //	if(!($user_data['bonus']+$all-($i*$min_bonus) <= $_SERVER['REQUEST_TIME'])){
                                                //	$bonus = $bonus/$i;
                                                //	break;
                                                //	}
                                                // }
                                            } elseif ($user_data['bonus'] == 0) {
                                                $bonus = USER_FIRST_BONUS;
                                            }
                                            users_update($request['object']['from_id'], (int) ($user_data['balance'] + $bonus));
                                            users_bonus($request['object']['from_id'], 28800);
                                            $bonus = number_format((float) $bonus, 0, '', ' ');
                                            $message = "Вы получили бонус {$bonus} серотонина сегодня!";
                                            show_start();
                                            break;
                                        } else {
                                            $message = "Вы уже получали бонус сегодня!\nСледующий раз можно через ".time_elapsed($user_data['bonus'] - $_SERVER['REQUEST_TIME']);
                                            show_start();
                                            break;
                                        }

                                }
                                break;
                            case 2:
                                if (GAME_STOP) {
                                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                                    if (!$scan_adm) {
                                        $message = 'Технические работы! Давай прервемся на '.TIME_TECH_WORK.' минут(у)';
                                        break;
                                    }
                                }
                                $user_game = games_get($user_id);

                                if ($user_game) {
                                    if (isset($user_game['map_key']) && ($command_buttons == 1 || $command_buttons == 2 || $command_buttons == 3 || $command_buttons == 9)) {
                                        $user_map = maps_get($user_game['map_key']);
                                        if ($user_map) {
                                            $map_key = $user_game['map_key'];
                                            $amount = (int) $user_game['coast'];
                                            $mine_count = (int) $user_map['mine_count'];
                                            $game_time = (int) $user_game['time'];
                                            $game_times = date('d.m.Y H:i:s', $game_time);
                                            show_user_map($user_map['map_game']);
                                            $balance_show = number_format((float) $user_data['balance'], 0, '', ' ');
                                            $message = "‼ Игра восстановлена ‼\n\n🗿 Игрок: @id{$user_id}({$user_data['first_name']} {$user_data['last_name']}) 🗿\n💰Баланс игрока: {$balance_show} 💰\n\n🤑 Ставка: {$amount} 🤑\n💣 Сложность: {$mine_count} бомб 💣\n⏳ Время начала: {$game_times} ({$game_time}) ⏳";
                                            break;
                                        }
                                    }
                                    if (($user_game['time'] - $_SERVER['REQUEST_TIME']) < 0) {
                                        $back_coast = (int) ($user_game['coast'] - ($user_game['coast'] * 10) / 100);
                                        games_delete($user_game['map_key']);
                                        users_update($user_id, (int) ($user_data['balance'] + $back_coast));
                                        $back_show = number_format((float) $back_coast, 0, '', ' ');
                                        show_start();
                                        $message = "Время игры закончилось! Вы проиграли 10% ставки\n😨 Мы вернули Вам {$back_show} серотонина. 😨";
                                        break;
                                    }
                                }
                                switch ($command_buttons) {
                                    case 0:
                                    case 9:
                                        $message = 'Ты не угадал! это не игровое поле! Попробуй нажать на пустую клетку!';
                                        break;
                                    case 1:
                                        $balance = (int) $user_data['balance'];
                                        if ($balance >= 0) {
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => 0], 'Без ставки', 'positive', 0);
                                        }
                                        if ($balance > 0) {
                                            $coast_set = (int) (($balance * 10) / 100);
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $coast_set], (string) number_format((float) $coast_set, 0, '', ' '), 'positive', 0);
                                            $coast_set = (int) (($balance * 20) / 100);
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $coast_set], (string) number_format((float) $coast_set, 0, '', ' '), 'positive', 0);
                                            $coast_set = (int) (($balance * 30) / 100);
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $coast_set], (string) number_format((float) $coast_set, 0, '', ' '), 'positive', 0);
                                            $coast_set = (int) (($balance * 40) / 100);
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $coast_set], (string) number_format((float) $coast_set, 0, '', ' '), 'primary', 1);
                                            $coast_set = (int) (($balance * 50) / 100);
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $coast_set], (string) number_format((float) $coast_set, 0, '', ' '), 'primary', 1);
                                            $coast_set = (int) (($balance * 60) / 100);
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $coast_set], (string) number_format((float) $coast_set, 0, '', ' '), 'primary', 1);
                                            $coast_set = (int) (($balance * 70) / 100);
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $coast_set], (string) number_format((float) $coast_set, 0, '', ' '), 'negative', 2);
                                            $coast_set = (int) (($balance * 80) / 100);
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $coast_set], (string) number_format((float) $coast_set, 0, '', ' '), 'negative', 2);
                                        }
                                        if ($balance > 0) {
                                            set_buttons(['c' => 2, 'b' => 2, 'a' => $balance], (string) number_format($balance, 0, '', ' '), 'negative', 2);
                                        }
                                        set_buttons(['c' => 0, 'b' => 0], 'Назад', 'default', 4);
                                        $message = 'Сделайте ставку, или введите ее числом:';
                                        break;
                                    case 2:
                                        if (isset($use_object['a'])) {
                                            $amount = (int) $use_object['a'];
                                            if ($amount > MAX_COAST || $amount < 0) {
                                                $max_coast = number_format((float) MAX_COAST, 0, '', ' ');
                                                $message = "Ваша ставка меньше 0 или больше {$max_coast} серотонина!";
                                                break;
                                            }
                                            if ($amount > $user_data['balance']) {
                                                show_start();
                                                $message = 'Вам игра не по карману.';
                                                break;
                                            } else {
                                                $count = 0;
                                                $lines = 0;
                                                $color = ['positive', 'primary', 'negative'];
                                                $user_wins = (int) $user_data['wins'];
                                                $count_diff = count(MINES) - 1;
                                                $user_max = $user_wins >= $count_diff ? $count_diff : $user_wins;

                                                for ($integer = 0; $integer <= $user_max; $integer++) {
                                                    $mines = $integer + 3;
                                                    if (($user_wins >= 30 && $mines == 3) || ($user_wins >= 50 && $mines == 4) || ($user_wins >= 70 && $mines == 5)) {
                                                        continue;
                                                    }
                                                    if ($count >= 3) {
                                                        $count = 0;
                                                        $lines++;
                                                    }
                                                    $colors = isset($color[$lines]) ? $color[$lines] : 'default';
                                                    $coast = (MINES[$mines] * 100) - 100;
                                                    set_buttons(['c' => 2, 'b' => 3, 'a' => $amount, 'm' => $mines], "{$mines} 💣 (+{$coast}%)", $colors, $lines);
                                                    $count++;
                                                }

                                                set_buttons(['c' => 0, 'b' => 0], 'Назад', 'default', ++$lines);

                                                $message = 'Выберите сложность игры:';
                                                break;
                                            }
                                        } else {
                                            show_start();
                                            $message = 'Вы не сделали ставку!';
                                            break;
                                        }
                                    case 3:
                                        if (isset($use_object['a'], $use_object['m'])) {
                                            if ($use_object['m'] > MINES_MAX || $use_object['m'] < MINES_MIN) {
                                                $message = 'Количество мин не соответствует';
                                                break;
                                            }
                                            $amount = (int) $use_object['a'];
                                            if ($amount > MAX_COAST || $amount < 0) {
                                                $max_coast = number_format((float) MAX_COAST, 0, '', ' ');
                                                $message = "Ваша ставка меньше 0 или больше {$max_coast} серотонина!";
                                                break;
                                            }
                                            if ($amount > $user_data['balance']) {
                                                show_start();
                                                $message = 'Вам игра не по карману.';
                                                break;
                                            }
                                            $game_time = $_SERVER['REQUEST_TIME'] + ($use_object['m'] * (TIME_MINE * 60)) + 120;
                                            $map_game = array_fill(0, 8, array_fill(0, 4, 0));
                                            $map_game = miner($use_object['m'], $map_game);
                                            if ($amount >= MAP_BONUS_MIN) {
                                                $map_game = get_chest($map_game);
                                            }
                                            $map_key = md5($user_id.'|'.$amount.'|'.json_encode($map_game));
                                            games_add($user_id, $game_time, $map_key, $amount);
                                            users_update($user_id, $user_data['balance'] - $amount);
                                            maps_add($map_key, $map_game, $use_object['m']);
                                            show_user_map($map_game);
                                            $game_times = date('H:i:s', $game_time);
                                            $balance_show = number_format((float) $user_data['balance'], 0, '', ' ');
                                            $amount = number_format((float) $amount, 0, '', ' ');
                                            $message = "‼ Игра началась ‼\n\n🗿 Игрок: @id{$user_id}({$user_data['first_name']} {$user_data['last_name']}) 🗿\n💰Баланс игрока: {$balance_show} 💰\n\n🤑 Ставка: {$amount} 🤑\n💣 Сложность: {$use_object['m']} бомб 💣\n"; //⏳ Время начала: {$game_times} ({$game_time}) ⏳";
                                            break;
                                        }
                                        show_start();
                                        $message = 'Вы не выбрали сложность или не сделали ставку';
                                        break;
                                    case 4:
                                        if (isset($use_object['x'], $use_object['y'])) {
                                            if (isset($user_game['map_key'])) {
                                                $user_map = maps_get($user_game['map_key']);
                                                if (isset($user_map['map_key'])) {
                                                    $x_pos = (int) $use_object['x'];
                                                    $y_pos = (int) $use_object['y'];
                                                    $map_game = $user_map['map_game'];

                                                    if ((bool) $user_data['torment_mode'] == true) {
                                                        if ($user_data['torment_cell'] !== $x_pos.$y_pos) {
                                                            $message = "Вы уверены что хотие сходить в ячейку X:$x_pos и Y:$y_pos?\nДля подтверждения повторите ход.";
                                                            users_torment_cell($user_id, (string) ($x_pos.$y_pos));
                                                            break;
                                                        }
                                                    }

                                                    $sapper_open = 0;
                                                    $sapper = sapper($x_pos, $y_pos, $map_game, $sapper_open);
                                                    $coast = (int) $user_game['coast'];
                                                    $mines = (int) $user_map['mine_count'];
                                                    $cell_open = cell_open($user_map, $sapper_open);

                                                    if ($sapper === 2) {
                                                        show_user_map($map_game);
                                                        $message = 'Вы уже ходили в X'.($x_pos + 1).';Y'.($y_pos + 1);
                                                        $time_game = time_elapsed($_SERVER['REQUEST_TIME'] - ($user_game['time'] - ($user_map['mine_count'] * (TIME_MINE * 60) + 120)));
                                                        $time_play = time_elapsed($user_game['time'] - $_SERVER['REQUEST_TIME']);
                                                        $message .= "\n💣 Сложность: ".$user_map['mine_count'];
                                                        $message .= "\n⏱ Время игры: ".$time_game;
                                                        $message .= "\n⏳ Время осталось: ".$time_play;
                                                        $message .= "\n🔥 Клеток осталось: ".($cell_open);
                                                    } elseif ($sapper === true) {
                                                        $cells_open = $user_map['cell_open'];
                                                        if ($cell_open > 0) {
                                                            maps_update($user_game['map_key'], $map_game, $cells_open + $sapper_open);
                                                            show_user_map($map_game);
                                                            $message = 'Ход выполнен на: X'.($x_pos + 1).';Y'.($y_pos + 1);
                                                            $time_game = time_elapsed($_SERVER['REQUEST_TIME'] - ($user_game['time'] - ($user_map['mine_count'] * (TIME_MINE * 60) + 120)));
                                                            $time_play = time_elapsed($user_game['time'] - $_SERVER['REQUEST_TIME']);
                                                            $message .= "\n💣 Сложность: ".$user_map['mine_count'];
                                                            $message .= "\n⏱ Время игры: ".$time_game;
                                                            $message .= "\n⏳ Время осталось: ".$time_play;
                                                            $message .= "\n🔥 Клеток осталось: ".($cell_open);
                                                        }
                                                    } else {
                                                        games_delete($user_game['map_key']);
                                                        if ($user_map['cell_open'] == 0) {
                                                            users_update($user_id, (int) ($user_data['balance'] + $coast));
                                                            set_buttons(['c' => 2, 'b' => 3, 'a' => $coast, 'm' => $mines], 'Пересоздать', 'positive', 0);
                                                            set_buttons(['c' => 0, 'b' => 0], 'Выйти', 'default', 1);
                                                            $coasts = number_format((float) $coast, 0, '', ' ');
                                                            $message = "Ахах, Вы подорвались на первой мине!!\n😂 Мы вернули Вам {$coasts} серотонина. 😂";
                                                            break;
                                                        } else {
                                                            show_map_end($map_game);
                                                            if ($user_game['coast'] > 100) { //ENTR TOP
                                                                $user_top_data = users_top($user_id, $mines);
                                                                if (!isset($user_top_data['mines'])) {
                                                                    users_top_add($user_id, $mines);
                                                                    $user_top_data = users_top($user_id, $mines);
                                                                }

                                                                users_top_death($user_id, $mines, (int) ($user_top_data['sum_death'] + $coast), (int) ($user_top_data['death'] + 1));
                                                            }
                                                            $time_game = time_elapsed($_SERVER['REQUEST_TIME'] - ($user_game['time'] - ($user_map['mine_count'] * (TIME_MINE * 60) + 120)));

                                                            $message = '😫 Вы подорвались. 😫';
                                                            $message .= "\n⏱ Время игры: ".$time_game;
                                                            $message .= "\nВы сделали ход на X".($x_pos + 1).';Y'.($y_pos + 1);
                                                            list($message_check, $result_check) = check_map($user_map['map_game'], $user_game, $user_data);
                                                            $message .= $message_check;
                                                            break;
                                                        }
                                                    }
                                                    if ($cell_open <= 0 || (32 - $mines) <= $user_map['cell_open']) {
                                                        games_delete($user_game['map_key']);
                                                        // $bot_bank = (float)info()['data']['coins'];
                                                        $balance_users = users_get_bank();
                                                        $bank_users = (float) $balance_users[0];
                                                        // $bot_bank = $bot_bank - $bank_users;

                                                        //  if ($bot_bank >= SAVE_BANK) {
                                                        $coast = (MINES[$user_map['mine_count']] * $coast);
                                                        // } else {
                                                        //      $coast = MINES[$user_map['mine_count']];
                                                        //  }
                                                        $bonus = 0;
                                                        if ($coast >= MAP_BONUS_MIN) {
                                                            $bonus = get_bonus($user_map['map_game']);
                                                        }
                                                        list($message_check, $result_check) = check_map($user_map['map_game'], $user_game, $user_data);
                                                        if ($result_check) {
                                                            users_update($user_id, (int) ($user_data['balance'] + $coast + $bonus));
                                                            $coast_full = $coast - $user_game['coast'];
                                                            show_map_end($map_game);
                                                            if ($coast_full > 0) {
                                                                $user_top_data = users_top($user_id, $mines);
                                                                if (!isset($user_top_data['mines'])) {
                                                                    users_top_add($user_id, $mines);
                                                                    $user_top_data = users_top($user_id, $mines);
                                                                }
                                                                $win_amount = (int) ($user_data['sum_wins'] + $coast_full + $bonus);
                                                                $win_amount = $win_amount > 0 ? $win_amount : 0;
                                                                users_wins($user_id, $win_amount, (int) ($user_data['wins'] + 1));

                                                                users_top_win($user_id, $mines, $win_amount, (int) ($user_top_data['wins'] + 1));
                                                                $time_game = time_elapsed($_SERVER['REQUEST_TIME'] - ($user_game['time'] - ($user_map['mine_count'] * (TIME_MINE * 60) + 120)));
                                                                $coast_show = toCoinShow($coast_full);
                                                                $message = "👑 Вы выиграли целых: {$coast_show} 👑";
                                                                if ($user_game['coast'] >= MAP_BONUS_MIN) {
                                                                    $bonus = toCoinShow($bonus);
                                                                    $message .= "\nВам выдан бонус: {$bonus}, за прохождение карты!";
                                                                }
                                                            } else {
                                                                $message = '👑 Вы выиграли 👑';
                                                            }
                                                        }
                                                        $message .= "\n⏱ Время игры: ".$time_game;
                                                        $message .= "\nВы сделали ход на X".($x_pos + 1).';Y'.($y_pos + 1);
                                                        $message .= $message_check;
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                        show_start();
                                        $message = "Привет {$user_data['first_name']}, выбери один из пунктов:";
                                        break;
                                    case 5:
                                        if (isset($user_game['map_key'])) {
                                            $map_key = $user_game['map_key'];
                                            if (isset($user_game['map_key']) && ($user_game['map_key'] == $map_key) && ($user_game['user_id'] == $user_id)) {
                                                if (isset($use_object['y']) && $use_object['y'] == 1) {
                                                    games_delete($map_key);
                                                    show_start();
                                                    $message = "Игра \"{$map_key}\" окончена, вы проиграли.";
                                                    break;
                                                }
                                                set_buttons(['c' => 2, 'b' => 9], 'Нет', 'default', 0);
                                                set_buttons(['c' => 2, 'b' => 5, 'y' => 1], 'Да', 'negative', 0);
                                                $message = "Вы уверены?\nВаша ставка будет утеряна...";
                                                break;
                                            }
                                        } else {
                                            show_start();
                                            $message = 'Ваша игра не закрыта.';
                                            break;
                                        }
                                        break;
                                    case 6:
                                        break;
// if (isset($user_game['map_key']) && $user_game['user_id'] == $user_id) {
// $user_map = maps_get($user_game['map_key']);
// $map_key = $user_game['map_key'];
// $mines = (int)$user_map['mine_count'];
// $map_game = $user_map['map_game'];
// $help_price = (((MINES[$mines] * 100) - 100) * HELP_PRICE) / 100;
// $amount = (int)($user_game['coast'] - (($user_game['coast'] / 100) * $help_price));
// if ($amount >= TIP_MAX_COAST || $amount <= TIP_MIN_COAST) {
// $max_coast = number_format((float)TIP_MAX_COAST, 0, '', ' ');
// $min_coast = number_format((float)TIP_MIN_COAST, 0, '', ' ');
// $message = "Подсказка не сработала\nВаша ставка меньше {$min_coast} или больше {$max_coast} серотонина!\nВаша ставка: {$amount} (с учетом цены подсказки " . $help_price . "%)";
// break;
// }
// if (isset($use_object['x'], $use_object['y']) && $user_map['help'] == 1) {
// $x_pos = (int)$use_object['x'];
// $y_pos = (int)$use_object['y'];
// $sapper = $map_game[$y_pos][$x_pos];
// if (is_string($sapper)) {
// $message = 'Вы уже открыли X' . ($x_pos + 1) . ';Y' . ($y_pos + 1);
// $attachments = ['doc-181694043_501528590'];
// break;
// } elseif ($sapper == 9) {
// maps_help($user_game['map_key']);
// $map_game[$y_pos][$x_pos] = 10;
// show_user_map($map_game, $user_game['time'], $user_map['mine_count'], $user_map['cell_open']);
// $message = "Остановись!\n В X" . ($x_pos + 1) . ';Y' . ($y_pos + 1) . ', накидали гнилых семечек! Еще чуть-чуть и ты бы подорвался!';
// $attachments = ['doc-181694043_502288704'];
// break;
// } else {
// maps_help($user_game['map_key']);
// $map_game[$y_pos][$x_pos] = 11;
// show_user_map($map_game, $user_game['time'], $user_map['mine_count'], $user_map['cell_open']);
// $message = 'На: X' . ($x_pos + 1) . ';Y' . ($y_pos + 1) . ', чисто, я проверил.';
// $attachments = ['doc-181694043_502288590'];
// break;
// }
// }
// if (isset($use_object['yes']) && $use_object['yes'] == 1) {
// if ($map_game) {
// maps_help($user_game['map_key'], 1);
// show_user_map($map_game, $user_game['time'], $user_map['mine_count'], $user_map['cell_open'], 6);
// $message = "Вы воспользовались подсказкой, ее цена: " . $help_price . "% от вашей ставки. Нажмите на любую клетку, а я скажу есть ли там мина!\nСтавка изменена на: " . $amount;
// break;
// } else {
// $message = "Вот блин! Я не могу тут помочь, попробуй еще разок!";
// break;
// }
// } else {
// set_buttons(['c' => 2, 'b' => 9], 'Нет', 'default', 0);
// set_buttons(['c' => 2, 'b' => 6, 'yes' => 1], 'Да', 'negative', 0);
// $message = "Вы уверены?\nЦена одной подсказки на этом уровне {$help_price}% от вашей ставки...";
// }
// } else {
// show_start();
// $message = 'Подсказка не сработала.(';
// }
// break;
                                }
                        }
                    }
                } elseif (isset($use_object['mailing_action'])) {
                    $message = 'вы отписались от рассылок, но может хотите поиграть? Какую валюту предпочитаете?';
                    show_start2();
                } else {
                    show_start();
                    $arr_message = ['Дарова черт', 'Хе-хе-хе, епт'];
                    $message = $arr_message[array_rand($arr_message)];
                }
            } else {
                if (GAME_STOP) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                    if (!$scan_adm) {
                        $message = 'Технические работы! Давай прервемся на '.TIME_TECH_WORK.' минут(у)';
                        break;
                    }
                }
                $user_id = (int) $request['object']['from_id'];
                $use_object = $request['object']['text'];
                if (preg_match('/^(хелп|начать|помощь|старт|help|играть|меню|menu)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    if ($request['user']['block'] == false) {
                        show_start();
                        $user_map = games_get($request['object']['from_id']);
                        $message = $request['user']['first_name'].', что будем делать?';
                        if (isset($user_map['map_key'])) {
                            $message .= "\n🆘 У вас есть незакрытая игра, нажмите 'играть', чтобы завершить её! ";
                        }
                    }
                } elseif (preg_match('/^(sms|msg|смс|сообщение) (https:\/\/vk\.com\/|\[|#|)([a-z0-9\-\.\_]+)(\|.*\]|)( .*|)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    if ($request['user']['block'] == false) {
                        $user_id = $matches[3][0];
                        if ($user_id > 0 || $user_id != '') {
                            $message = $matches[5][0] ? $matches[5][0] : '';
                            $user2_data = user_info($user_id);
                            $user2_data = users_get((int) $user2_data['id']);
                            if (isset($user2_data['user_id'])) {
                                if (isset($request['object']['attachments']) && !empty($request['object']['attachments'])) {
                                    $message = "💭 @id{$user_data['user_id']}({$user_data['first_name']} {$user_data['last_name']}): ".$message;
                                    $message .= "\n\nсмс {$user_data['user_id']} текст ответа.";
                                    vk_send((int) $user2_data['user_id'], $message, $attachments, $request['object']['id']);
                                    $message = "@id{$user2_data['user_id']}({$user2_data['first_name']} {$user2_data['last_name']}). получил сообщение.";
                                } elseif (isset($request['object']['fwd_messages']) && !empty($request['object']['fwd_messages'])) {
                                    $message = "💭 @id{$user_data['user_id']}({$user_data['first_name']} {$user_data['last_name']}): ".$message;
                                    $message .= "\n\nсмс {$user_data['user_id']} текст ответа.";
                                    vk_send((int) $user2_data['user_id'], $message, $attachments, $request['object']['id']);
                                    $message = "@id{$user2_data['user_id']}({$user2_data['first_name']} {$user2_data['last_name']}). получил сообщение.";
                                } elseif ($message != '') {
                                    $message = "💭 @id{$user_data['user_id']}({$user_data['first_name']} {$user_data['last_name']}): ".$message;
                                    $message .= "\n\nсмс {$user_data['user_id']} текст ответа.";
                                    vk_send((int) $user2_data['user_id'], $message);
                                    $message = "@id{$user2_data['user_id']}({$user2_data['first_name']} {$user2_data['last_name']}) получил сообщение.";
                                } elseif ($message == '') {
                                    $message = 'Сообщение не отправлено.';
                                }
                            } else {
                                $message = 'Сообщение не отправлено.';
                            }
                        } else {
                            $message = 'Сообщение не отправлено.';
                        }
                    }
                } elseif (preg_match('/^(клава|key) ([0-9]+)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                    if ($scan_adm) {
                        $user_id = (int) $matches[2][0];
                        show_start();
                        $message = '@notsapper: вы вернулись в главное меню!';
                        vk_send($user_id, $message, $attachments);
                        set_clear(false);
                        $message = "@id{$user_id}(Пользователь) отправлен в меню.";
                    }
                } elseif (preg_match('/^(clear|клир|очистить) ([0-9]+)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                    if ($scan_adm) {
                        $user_id = (int) $matches[2][0];
                        set_clear();
                        $message = '@notsapper: вашу клавиатуру украл гопник!';
                        vk_send($user_id, $message, $attachments);
                        set_clear(false);
                        $message = "@id{$user_id}(Пользователь) потерял клавиатуру.";
                    }
                } elseif (preg_match('/^(zver|user|пользователь|игрок|зверь) ([0-9]+)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                    if ($scan_adm) {
                        $user_id = (int) $matches[2][0];
                        $message = method_user($user_id);
                    }
                } elseif (preg_match('/^(наблюдать|глаз|чит) ([0-9]+) (0|1)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                    if ($scan_adm) {
                        $type_spectator = (bool) $matches[3][0];
                        $spectator_id = $type_spectator ? (int) $request['object']['from_id'] : 0;
                        $user_id = (int) $matches[2][0];
                        $message = method_user($user_id);
                        users_spectator($user_id, $spectator_id, $_SERVER['REQUEST_TIME'] + SPECTATOR_TIME);
                        $user_message = '@notsapper: '.($type_spectator ? "За вашими действиями наблюдает @id{$spectator_id} ({$request['user']['first_name']} {$request['user']['last_name']})." : "Пользователь @id{$spectator_id} ({$request['user']['first_name']} {$request['user']['last_name']}) закончил наблюдение.");
                        vk_send($user_id, $user_message, $attachments);
                        if (!$type_spectator) {
                            show_start();
                        }
                        $message .= "\nВы ".($type_spectator ? 'перешли в режим наблюдения' : 'вышли из режима наблюдения')." за @id{$user_id}(пользователем).";
                    }
                } elseif (preg_match('/^ban ([0-9]+) (0|1)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                    if ($scan_adm) {
                        $user_id = (int) $matches[1][0];
                        $type_block = (bool) $matches[2][0];
                        users_block($user_id, $type_block);
                        if ($type_block) {
                            set_clear();
                        } else {
                            show_start();
                        }
                        $message = '@notsapper: Ваш аккаунт '.($type_block ? 'заблокирован' : 'разблокирован').' администратором.';
                        vk_send($user_id, $message, $attachments);
                        set_clear(false);
                        $message = "@id{$user_id}(Пользователь) ".($type_block ? 'заблокирован.' : 'разблокирован.');
                    }
                } elseif (preg_match('/^(вывод|забрать) ([0-9]+)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $amount = (int) $matches[2][0];
                    $amount_show = toCoinShow($amount);
                    if ($request['user']['block'] == false) {
                        if (GAME_WITHDRAW) {
                            $balance = (float) $request['user']['balance'];

                            $balance_show = toCoinShow($balance);
                            if ($balance >= $amount) {
                                if ($amount >= MIN_PAY && $amount <= MAX_PAY) {
                                    set_buttons(['c' => 1, 'b' => 4, 'a' => $amount], $amount_show, 'positive');
                                    set_buttons(['c' => 0, 'b' => 0], 'Назад', 'default', 1);
                                    $message = "Вы хотите вывести: {$amount_show}";
                                } else {
                                    $min_pay = number_format((float) MIN_PAY, 0, ',', ' ');
                                    $max_pay = number_format((float) MAX_PAY, 0, ',', ' ');
                                    $message = sprintf("Выводить можно только от %s и до %s серотонина.\nВаш баланс: %s.\n\n", $min_pay, $max_pay, $balance_show);
                                }
                            } else {
                                $message = "У вас нет {$amount_show} серотонина.";
                            }
                        } else {
                            $message = 'Выводы временно заблокированы.';
                        }
                    }
                    $message = method_commands();
                } elseif (preg_match('/^(пополнить|положить)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    if ($request['user']['block'] == false) {
                        list($message, $attachments) = method_replenish();
                    }
                } elseif (preg_match('/^(bal|balance) ([0-9]+) ([0-9]+)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                    if ($scan_adm) {
                        $user_id = (int) $matches[2][0];
                        $balance = (int) $matches[3][0];
                        users_update($user_id, $balance);
                        $message = '@notsapper: Ваш баланс был изменен на: '.$balance.' серотонина.';
                        vk_send($user_id, $message, $attachments);
                        $message = "Баланс @id{$user_id}(пользователя) изменен на ".$balance;
                    }
                }// elseif (preg_match('/^ежедневный бонус|бонус|подарок|bonus/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {

                //   if (isset($request['user']['bonus']) && $request['user']['bonus'] <= $_SERVER['REQUEST_TIME'] || $request['user']['bonus'] == 0) {
                //        $bonus = BONUS_MAX;
                //        if ((int)$request['user']['wins'] > 0) {
                //             $bonus += $request['user']['wins'] * USER_WINS_BONUS;
                //        } elseif ($request['user']['bonus'] == 0) {
                //            $bonus = USER_FIRST_BONUS;
                //        }
                //         users_update($request['object']['from_id'], (int)($request['user']['balance'] + $bonus));
                //         users_bonus($request['object']['from_id']);
                //           $bonus = number_format((float)$bonus, 0, '', ' ');
                //          $message = "Вы получили бонус {$bonus} серотонина сегодня!";
                //       } else {
                //          $message = "Вы уже получали бонус сегодня!\nСледующий раз можно через " . time_elapsed($request['user']['bonus'] - $_SERVER['REQUEST_TIME']);
                //        }
                //    }
                elseif (preg_match('/^spermbank|bank|банк|balance|баланс|профиль/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;

                    if ($request['user']['block'] == false) {
                        $balance_show = num_word((int) $request['user']['balance'], ['Серотонин', 'Серотонина', 'Серотонина']);
                        $payment_show = number_format((float) $request['user']['payment'], 0, '', ' ');
                        $replenish_show = number_format((float) $request['user']['replenishment'], 0, '', ' ');
                        $message = show_balance($balance_show, $payment_show, $replenish_show);
                    }
                } elseif (preg_match('/^флаг|флажок|подтверждение|flag|актив/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    if ($request['user']['block'] == false) {
                        users_torment_mode($request['object']['from_id'], ((bool) $user_data['torment_mode'] ? false : true));
                        $message = 'Режим подтверждения действий '.((bool) $user_data['torment_mode'] == true ? 'деактивирован.' : 'активирован.');
                    }
                } elseif (preg_match('/^Отписаться от рассылок$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $message = 'вы отписались от рассылок, но может хотите поиграть? Какую валюту предпочитаете?';
                    show_start2();
                } elseif (preg_match('/^([0-9]+)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    if ($request['user']['block'] == false) {
                        $user_map = games_get($request['object']['from_id']);
                        if (!isset($user_map['map_key'])) {
                            $amount = (int) $matches[1][0];
                            if ($amount > MAX_COAST || $amount < 0) {
                                $max_coast = number_format((float) MAX_COAST, 0, '', ' ');
                                $message = "Не удалось создать игру!\nВаша ставка меньше 0 или больше {$max_coast} серотонина!";
                            } else {
                                $amount_show = toCoinShow($amount);
                                set_buttons(['c' => 2, 'b' => 2, 'a' => (int) $matches[1][0]], $amount_show, 'positive');
                                set_buttons(['c' => 0, 'b' => 0], 'Назад', 'default', 1);
                                $message = "Ваша ставка: {$amount_show}";
                            }
                        } else {
                            $message = 'Ставка недоступна при активной игре!';
                        }
                    }
                } elseif (preg_match('/^xray ([0-9]+) (1|0)$/iu', $use_object, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $scan_adm = array_search($request['object']['from_id'], ACCESS) !== false ? true : false;
                    if ($scan_adm) {
                        $user_id = (int) $matches[1][0];
                        $mode_getting = (bool) $matches[2][0];
                        $user_game = games_get($user_id);
                        if (isset($user_game['map_key'])) {
                            $user_map = maps_get($user_game['map_key']);
                            $message = "Карта @id{$user_id}(пользователя) (\"{$user_game['map_key']}\"):";
                            $swc_cell = function ($cell_info) {
                                switch ($cell_info) {
                                    case 0:
                                        return '0⃣';
                                        break;
                                    case 1:
                                        return '1⃣';
                                        break;
                                    case 2:
                                        return '2⃣';
                                        break;
                                    case 3:
                                        return '3⃣';
                                        break;
                                    case 4:
                                        return '4⃣';
                                        break;
                                    case 5:
                                        return '5⃣';
                                        break;
                                    case 6:
                                        return '6⃣';
                                        break;
                                    case 7:
                                        return '7⃣';
                                        break;
                                    case 8:
                                        return '8⃣';
                                        break;
                                    case 9:
                                        return '💣';
                                        break;
                                    case 33:
                                        return '💰';
                                        break;
                                    default:
                                        return $cell_info;
                                        break;
                                }
                            };
                            if (is_array($user_map['map_game'])) {
                                foreach ($user_map['map_game'] as $pos_y => $value) {
                                    $message .= "\n";

                                    foreach ($value as $pos_x => $cell_info) {
                                        if ($mode_getting) {
                                            $label = $swc_cell($cell_info);
                                        } else {
                                            $label = is_string($cell_info) ? $swc_cell($cell_info) : '🆓';
                                        }
                                        $button_cell['x'] = $pos_x;
                                        $button_cell['y'] = $pos_y;
                                        $message .= "$label";
                                    }
                                }
                            }
                        } else {
                            $message = "@id{$user_id}(Пользователь) не играет!";
                        }
                    }
                }
            }
            $user_wins = isset($request['user']['wins']) ? $request['user']['wins'] : 0;
            $user_member = isset($user_data['is_member']) ? $user_data['is_member'] : 0;

            if ($user_wins > 0 && $user_member - $_SERVER['REQUEST_TIME'] <= 0) {
                $is_member = vk_api_call('groups.isMember', ['group_id' => GROUP_ID, 'user_id' => $request['object']['from_id'], 'extended' => true]);
                $is_member = $is_member['member'];
                if ($is_member != 1) {
                    $message .= "\n@notsapper (Вы не подписались)!";
                } else {
                    users_member($request['object']['from_id'], $_SERVER['REQUEST_TIME'] + 10800);
                }
            }

            if ($message || $attachments) {
                if ($message == $start_message) {
                    show_start();
                }
                vk_send($request['object']['peer_id'], $message, $attachments);
                if (isset($request['user']['spectator']) && $request['user']['spectator'] > 0) {
                    usleep(10);
                    $user_time = $request['user']['spectator_time'] - $_SERVER['REQUEST_TIME'];
                    $spectator_time = time_elapsed($user_time);
                    if ($user_time <= 0) {
                        users_spectator($request['object']['from_id'], 0, 0);
                        show_start();
                        $message = 'Режим просмотра завершен';
                        vk_send((int) $request['user']['spectator'], $message, $attachments);
                    } else {
                        if ($message == $start_message) {
                            $message = '🗯: '.$request['object']['text'];
                        }
                        $message .= "\nВремя до конца просмотра: {$spectator_time}\nДля выхода введите:\nчит {$request['object']['from_id']} 0";
                        vk_send((int) $request['user']['spectator'], $message, $attachments);
                    }
                }
            }
            exit('ok');
            break;
        case 'confirmation':
            exit(CONFIRMATION_TOKEN);
            break;
    }
    exit('ok');
} catch (Throwable $exception) {
    $error = '[Exception]: возникла ошибка '.date('d-m-Y h:i:s').':';
    $error .= "\r\n[Exception]: текст: {$exception->getMessage()}";
    $error .= "\r\n[Exception]: код ошибки: {$exception->getCode()}";
    $error .= "\r\n[Exception]: файл: {$exception->getFile()}:{$exception->getLine()}";
    $error .= "\r\n[Exception]: путь ошибки: {$exception->getTraceAsString()}\r\n";
    $file_log = fopen('errors/error_log'.date('d-m-Y_h').'.log', 'a');
    fwrite($file_log, $error);
    fclose($file_log);
    exit('ok');
}
