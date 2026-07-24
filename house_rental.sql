-- house_rental SQL dump (small). Import this in phpMyAdmin.

DROP TABLE IF EXISTS `house_images`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `houses`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `houses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `bedrooms` INT NOT NULL,
  `bathrooms` INT NOT NULL,
  `description` TEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `house_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `house_id` INT NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `house_id` INT NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `message` TEXT,
  `check_in` DATE DEFAULT NULL,
  `check_out` DATE DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `booking_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sample admin (password: admin123) and sample user (password: user123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
('Admin', 'admin@house.com', '0755123456', 'admin123', 'admin'),
('Test User', 'user@example.com', '0755000000', 'user123', 'user');

-- sample house
INSERT INTO `houses` (`title`, `location`, `price`, `bedrooms`, `bathrooms`, `description`, `image`) VALUES
('Modern Family House', 'Dar es Salaam', 450000, 3, 2, 'Bright and spacious house with a garden and parking.', 'house1.jpg');

-- sample house image
INSERT INTO `house_images` (`house_id`, `image`, `caption`) VALUES
(1, 'house1.jpg', 'Front view');
