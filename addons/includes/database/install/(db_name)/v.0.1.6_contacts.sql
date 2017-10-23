CREATE TABLE `contacts` (
`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
`user_id` int(10) UNSIGNED NOT NULL,
`key` varchar(100) NOT NULL,
`value` varchar(100) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
`desc` varchar(1000) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
`login` bit(1) NULL DEFAULT NULL,
`verify` bit(1) NULL DEFAULT NULL,
`datecreated` timestamp DEFAULT CURRENT_TIMESTAMP,
`datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
CONSTRAINT `contact_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;