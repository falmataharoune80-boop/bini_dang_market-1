-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 18 mai 2026 à 10:36
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bini_dang_market`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'store',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `is_active`) VALUES
(1, 'Women\'s Fashion', 'tshirt', 1),
(2, 'Men\'s Fashion', 'tshirt', 1),
(3, 'Matériels Electroniques', 'microchip', 1),
(4, 'Home & Lifestyle', 'home', 1),
(5, 'Produits Pharmaceutique', 'capsules', 1),
(6, 'Sports & Fitness', 'futbol', 1),
(7, 'Baby\'s Fashion', 'baby', 1),
(8, 'Produits Alimentaires', 'cart-shopping', 1),
(9, 'Santé & Beauté', 'spa', 1),
(10, 'Smartphones', 'mobile-alt', 1),
(11, 'Ordinateurs', 'laptop', 1),
(12, 'SmartWatch', 'clock', 1),
(13, 'Camera', 'camera', 1),
(14, 'Airpods & kits', 'headphones', 1),
(15, 'Gaming', 'gamepad', 1);

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(15, 13, 12, 'bonjour monsieur ce produit est toujours disponible?', 0, '2026-05-14 09:20:17'),
(16, 12, 13, 'oui bonjour monsieur mbai  le produit est disponible nous somme situé à bini loin de LMD merci d\'y vous rendre', 1, '2026-05-14 09:45:57'),
(17, 13, 12, 'bonjour monsieur cet article est disponible?', 0, '2026-05-14 09:59:04');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','orange_money','stripe') NOT NULL,
  `stripe_payment_intent` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `delivery_method` enum('pickup','delivery') DEFAULT 'pickup',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `admin_confirmed` tinyint(1) DEFAULT 0,
  `confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `reference`, `buyer_id`, `seller_id`, `product_id`, `amount`, `payment_method`, `stripe_payment_intent`, `status`, `delivery_method`, `created_at`, `updated_at`, `payment_status`, `admin_confirmed`, `confirmed_at`) VALUES
(8, 'CMD-20260515-6a06d0f79f2a9', 13, 13, 13, 1000.00, 'orange_money', NULL, 'completed', 'pickup', '2026-05-15 07:53:27', '2026-05-17 17:46:37', 'paid', 1, '2026-05-17 17:46:37'),
(11, 'CMD-20260517-6a09771799740', 12, 2, 1, 45000.00, 'orange_money', NULL, 'completed', 'pickup', '2026-05-17 08:06:47', '2026-05-18 07:53:35', 'paid', 1, '2026-05-18 07:53:35'),
(12, 'CMD-20260518-6a0ac64a0fadf', 17, 17, 19, 15000.00, 'orange_money', NULL, 'pending', 'pickup', '2026-05-18 07:56:58', '2026-05-18 07:56:58', 'pending', 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('orange_money','stripe','cash') NOT NULL,
  `account_identifier` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('available','sold','reserved') DEFAULT 'available',
  `original_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `seller_id` int(11) NOT NULL,
  `location` varchar(100) DEFAULT 'Bini-Dang',
  `is_flash_sale` tinyint(1) DEFAULT 0,
  `flash_sale_discount` int(11) DEFAULT 0,
  `rating` decimal(2,1) DEFAULT 0.0,
  `num_reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `title`, `description`, `price`, `status`, `original_price`, `category_id`, `seller_id`, `location`, `is_flash_sale`, `flash_sale_discount`, `rating`, `num_reviews`, `created_at`) VALUES
(1, 'Robe de soirée', 'Robe élégante pour soirée', 45000.00, 'sold', NULL, 1, 2, 'Bini-Dang', 0, 0, 4.5, 0, '2026-05-08 22:12:56'),
(2, 'Sac à main en cuir', 'Sac femme en cuir véritable', 35000.00, 'available', NULL, 1, 2, 'Bini-Dang', 0, 0, 4.7, 0, '2026-05-08 22:12:56'),
(3, 'Costume 3 pièces', 'Costume homme luxe', 85000.00, 'available', NULL, 2, 2, 'Bini-Dang', 0, 0, 4.8, 0, '2026-05-08 22:12:56'),
(8, 'Apple', 'ordinateurs haute gamme', 200006.00, 'available', NULL, 11, 13, 'Bini-Dang', 0, 0, 0.0, 0, '2026-05-11 11:34:32'),
(9, 'PS 5', 'nouvelle generation', 443.00, 'available', NULL, 15, 12, 'Bini', 0, 0, 0.0, 0, '2026-05-12 09:59:32'),
(10, 'P4', 'FYGYUF87Y', 99994.00, 'available', NULL, 15, 12, 'Bini', 0, 0, 0.0, 0, '2026-05-12 10:00:45'),
(11, 'Riz Cam', 'riz naturel 100% CAMEROUNAIS', 11994.00, 'available', NULL, 8, 12, 'Bini', 0, 0, 0.0, 0, '2026-05-12 10:13:05'),
(12, 'Ballon de Foot', 'leger rapide omplot 4 disponible à bini stock limité', 25000.00, 'available', NULL, 6, 12, 'Bini', 0, 0, 0.0, 0, '2026-05-12 11:51:37'),
(13, 'Paracétamol', '100%', 5000.00, 'sold', NULL, 5, 13, 'Dang', 0, 0, 0.0, 0, '2026-05-14 07:42:23'),
(18, 'écouteur', 'fine,résistant dans l\'eau,ultra base', 5000.00, 'available', NULL, 14, 13, 'Bini', 0, 0, 0.0, 0, '2026-05-16 07:41:29'),
(19, 'Robe soirée', 'linge et coton sexy emincé disponible à Dang', 15000.00, 'available', NULL, 1, 17, 'Bini-Dang', 0, 0, 0.0, 0, '2026-05-17 17:16:58'),
(20, 'MONTRE', 'les nouveaux montre', 50000.00, 'available', NULL, 12, 17, 'Bini', 0, 0, 0.0, 0, '2026-05-18 08:01:14');

-- --------------------------------------------------------

--
-- Structure de la table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`) VALUES
(20, 1, 'uploads/1778915956_robe.jpg'),
(21, 2, 'uploads/1778915922_sac à main.jpg'),
(22, 3, 'uploads/1778916015_avatar_13_1778839972.jpg'),
(27, 8, 'uploads/1778915996_ordinateurs portable.jpg'),
(28, 9, 'uploads/1778915905_manette P5.jpg'),
(29, 10, 'uploads/1778916051_smartwatch.jpg'),
(30, 11, 'uploads/1778915873_riz 10kg.jpg'),
(31, 12, 'uploads/1778915745_ballon foot.jpg'),
(44, 18, 'uploads/1778917496_6a0820780b996_ecouteur intra.jpg'),
(45, 13, 'uploads/1779003102_6a096ede0dd1e_paracetamol.jpg'),
(63, 19, 'uploads/1779038218_6a09f80a659d1_1778915956_robe.jpg'),
(64, 20, 'uploads/1779091274_6a0ac74ad5870_1778916749_smartwatch.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `purchase_history`
--

CREATE TABLE `purchase_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) DEFAULT 'completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewed_user_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `transaction_id`, `amount`, `payment_method`, `status`, `created_at`) VALUES
(1, 8, NULL, 1000.00, 'orange_money', 'success', '2026-05-15 07:53:27'),
(2, 9, NULL, 500000.00, 'orange_money', 'pending', '2026-05-16 06:58:34'),
(4, 11, NULL, 45000.00, 'orange_money', 'pending', '2026-05-17 08:06:47'),
(5, 12, NULL, 15000.00, 'orange_money', 'pending', '2026-05-18 07:56:58');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(100) DEFAULT 'Bini-Dang',
  `profile_picture` varchar(255) DEFAULT '/uploads/avatar-default.jpg',
  `payment_preference` enum('cash','orange_money','stripe') DEFAULT 'cash',
  `role` enum('user','admin') DEFAULT 'user',
  `rating` decimal(2,1) DEFAULT 0.0,
  `num_reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `location`, `profile_picture`, `payment_preference`, `role`, `rating`, `num_reviews`, `created_at`) VALUES
(2, 'Jean Dupont', 'jean@example.com', '$2y$10$eaAbZ4OqXmAVJR3c/fAoY.XYJZAt0tCgIHQVtWknvkiaSnVRMUvhy', '+237 612345678', 'Bini-Dang', '/uploads/avatar-default.jpg', 'cash', 'user', 0.0, 0, '2026-05-08 22:12:56'),
(5, 'franklin', 'franklin@gmail.com', '$2y$10$wG.kTJQPJh3w0QQ1f9XVa.gwCm4i5Rko0txtrYsvfBwvCc2pustaS', '658144012', 'Dang', '/uploads/avatar-default.jpg', 'cash', 'user', 0.0, 0, '2026-05-09 14:10:00'),
(7, 'Gustavo', 'gustavo@gmail.com', '$2y$10$IX6qIBOHiXoT6v5ix3rmR.ApFElIfJEJJNXkD1nwtYHPa3M5730lG', '237656320676', 'Bini', '/uploads/avatar-default.jpg', 'cash', 'user', 0.0, 0, '2026-05-10 16:04:58'),
(8, 'Gustave', 'gustave@gmail.com', '$2y$10$TVMyX4vdvQE.kqCDR93H6ey53tUhs/98mqHdwm4bPMvV8nySpQGW6', '656320676', 'Bini', '/uploads/avatar-default.jpg', 'cash', 'user', 0.0, 0, '2026-05-11 09:12:54'),
(12, 'l3info', 'l3info@gmail.com', '$2y$10$v0o6qVEwWL0NvYTLZRmYgOBfRXfTw3zQqPBJqtQVjRUEOY/gizd7S', '656320677', 'Bini-Dang', '/uploads/avatar-default.jpg', 'cash', 'admin', 0.0, 0, '2026-05-11 10:12:02'),
(13, 'mbai', 'mbai@gmail.com', '$2y$10$JWsojs5bhvrH9XJM9zxvQuwzJR8meOgEtsJzG9cyM7yCHVSj.skkG', '655891232', 'Dang', '/uploads/avatar_13_1778840006.jpg', 'cash', 'user', 0.0, 0, '2026-05-11 10:41:22'),
(14, 'falmata', 'falmata@gmail.com', '$2y$10$YY1n4/UASnZcZQ.odxgJVOBZbnsFUdHiLtbw4W9dRnPhtlAwRvE7u', '692773799', 'Bini', '/uploads/avatar-default.jpg', 'cash', 'user', 0.0, 0, '2026-05-14 07:46:36'),
(17, 'prince', 'prince@gmail.com', '$2y$10$fmtW8yRIcf3wmLw8tlVSlegFfHsu6zHTbDqUc1jygmTARhnSSaD7i', '687452710', 'Dang', '/uploads/avatar-default.jpg', 'cash', 'admin', 0.0, 0, '2026-05-17 17:15:07');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Index pour la table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `purchase_history`
--
ALTER TABLE `purchase_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewed_user_id` (`reviewed_user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT pour la table `purchase_history`
--
ALTER TABLE `purchase_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Contraintes pour la table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `purchase_history`
--
ALTER TABLE `purchase_history`
  ADD CONSTRAINT `purchase_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchase_history_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `purchase_history_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Contraintes pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewed_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
