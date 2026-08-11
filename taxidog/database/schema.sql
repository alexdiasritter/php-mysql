-- Estrutura do banco do Táxi Dog.
-- Reflete o que o código realmente usa hoje.

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  -- Cookie "lembrar-me": o cookie guarda selector:validator,
  -- o banco guarda só o hash do validator.
  `remember_selector` varchar(32) DEFAULT NULL,
  `remember_token_hash` varchar(64) DEFAULT NULL,
  `remember_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `remember_selector` (`remember_selector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `rides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `client` varchar(100) NOT NULL DEFAULT 'Cliente',
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `period` enum('dia','noite') NOT NULL,
  `animal_quantity` int(11) NOT NULL DEFAULT 1,
  `ride_date` datetime NOT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_status_date` (`user_id`, `status`, `ride_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuário inicial. Gere o hash com:  php tools/gerar_senha.php admin suaSenha
-- (o exemplo abaixo corresponde à senha "123456")
INSERT INTO `users` (`username`, `password_hash`)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
