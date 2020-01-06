CREATE TABLE IF NOT EXISTS `games` (
  `user_id` int(11) NOT NULL,
  `time` int(11) DEFAULT NULL,
  `map_key` varchar(255) NOT NULL,
  `coast` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `maps` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `coast` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `map_key` varchar(255) NOT NULL,
  `mine_count` int(11) DEFAULT NULL,
  `cell_open` int(11) DEFAULT NULL,
  `map_game` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `first_name` varchar(225) NOT NULL DEFAULT '0',
  `last_name` varchar(225) NOT NULL DEFAULT '0',
  `is_member` int(11) DEFAULT '0',
  `balance` int(11) DEFAULT '0',
  `bonus` int(11) NOT NULL DEFAULT '0',
  `block` tinyint(1) NOT NULL DEFAULT '0',
  `sum_wins1` int(11) NOT NULL DEFAULT '0',
  `wins` int(11) NOT NULL DEFAULT '0',
  `payment` int(11) NOT NULL DEFAULT '0',
  `replenishment` int(11) NOT NULL DEFAULT '0',
  `spectator` int(11) NOT NULL DEFAULT '0',
  `spectator_time` int(11) DEFAULT '0',
  `torment_mode` tinyint(1) NOT NULL DEFAULT '0',
  `torment_cell` varchar(5) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users_top` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mines` int(11) NOT NULL DEFAULT '0',
  `sum_wins` int(11) NOT NULL DEFAULT '0',
  `sum_death` int(11) NOT NULL DEFAULT '0',
  `wins` int(11) NOT NULL DEFAULT '0',
  `death` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `games`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `map_key` (`map_key`),
  ADD UNIQUE KEY `map_key_2` (`map_key`);

ALTER TABLE `maps`
  ADD UNIQUE KEY `map_key` (`map_key`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

ALTER TABLE `users_top`
  ADD PRIMARY KEY (`id`);
