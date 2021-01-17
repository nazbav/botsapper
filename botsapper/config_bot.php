<?php

declare(strict_types=1);
const ACCESS = []; //айди админов через запятую
const REGUL = 1.10;
const MINES = [3 => 1.02 + REGUL, 1.06 + REGUL, 1.15 + REGUL, 1.33 + REGUL, 1.70 + REGUL, 2.35 + REGUL, 3.50 + REGUL, 5.90 + REGUL];
const MINES_MAX = 25;
const MINES_MIN = 3;
const START_STOP = false;
const BALANCE_STOP = false;
const GAME_STOP = false;
const GAME_WITHDRAW = false;
const MAX_COAST = 1e8;
const MAX_PAY = 1e8;
const MIN_PAY = 1e2;
const COMMISSION = 0;
const USER_START_BALANCE = 100;
const MAP_BONUS_MIN = 200000;
const BONUS_MIN = 10000;
const BONUS_MAX = 200000;
const HELP_PRICE = 25;
const CHEST_CHANCE = 2;
const TIP_MAX_COAST = MAX_COAST;
const TIP_MIN_COAST = MIN_PAY;
const TIME_TECH_WORK = 228;
const USER_WINS_BONUS = 777;
const USER_FIRST_BONUS = 100000;
const SPECTATOR_TIME = 600;
const SAVE_BANK = 1e3;
const TIME_MINE = 1;
