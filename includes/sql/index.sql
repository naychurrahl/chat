CREATE TABLE `chat_users` (
  `id`   int(4)        NOT NULL,
  `user` varchar(2050) NOT NULL,
  `time` varchar(16)   NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `chat_init` (
  `id`      int(4)        NOT NULL,
  `target`  int(4)        NOT NULL,
  `message` varchar(2050) NOT NULL,
  `time`    varchar(16)   NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`target`) REFERENCES `chat_users`(`id`)
  ON UPDATE CASCADE
  ON DELETE CASCADE
) ENGINE=InnoDB;

---------------------------------------------------------
---------   Tonigh  ------------------------------------- 
---------------------------------------------------------
CREATE TABLE `chat_test` (
  `id`      int(4)        NOT NULL,
  `target`  varchar(2050) NOT NULL,
  `message` varchar(2050) NOT NULL,
  `time`    varchar(16)   NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
---------------------------------------------------------
---------------------------------------------------------

CREATE TABLE `chat_messages` (
  `id`      int(4)        NOT NULL,
  `reciver` int(4)        NOT NULL,
  `message` varchar(2050) NOT NULL,
  `time`    varchar(16)   NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`reciver`) REFERENCES `chat_users`(`id`)
  ON UPDATE CASCADE
  ON DELETE CASCADE
) ENGINE=InnoDB;

/*INSERT INTO `chat_messages` (`reciver`, `message`, `time`) VALUES
  ('2', 'laptop', '0'),
  ('1', 'tanktop', '0'),
  ('1', 'ontop', '0'),
  ('2', 'intop', '0');

/*
CREATE TABLE `prod_desc` (
  `description` varchar(100) NOT NULL,
  `id`          varchar(100) NOT NULL,
  `price`       int(7)       NOT NULL,
  `product`     varchar(100) NOT NULL,
  `status`      tinyint(1)   NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`description`) REFERENCES `descriptions`(`id`),
  FOREIGN KEY (`product`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

INSERT INTO `chat_teste` (`t`) VALUES
  ('a'),
  ('a');
*/