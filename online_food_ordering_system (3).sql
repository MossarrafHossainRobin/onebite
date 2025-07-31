-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2025 at 12:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_food_ordering_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_id`, `stock`, `last_updated`) VALUES
(1, 10, 90, '2025-05-16 07:38:19'),
(2, 2, 3, '2025-05-16 07:38:10'),
(3, 12, 90, '2025-05-17 12:17:23');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`item_id`, `name`, `description`, `price`, `stock`, `created_at`, `image`, `category`) VALUES
(2, 'Kala Vuna', 'Authentic Kala vuna made with 100 species.', 160.00, 38, '2024-12-21 14:36:45', 'kalavuna.jpg', NULL),
(10, 'Golden Onion Rings', 'Golden Onion Rings', 300.00, 25, '2025-05-15 17:33:08', 'starter3.jpg', NULL),
(11, 'Golden Onion Rings', 'Golden Onion Rings', 30.00, 293, '2025-05-15 17:33:26', 'starter2.jpg', NULL),
(12, 'Pasta Mania', 'Pasta Mania', 300.00, 89, '2025-05-16 06:44:46', 'pastamania.jpg', NULL),
(14, 'Pasta Mania', 'Pasta Manai', 900.00, 89, '2025-05-17 13:07:42', 'pastamania.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Confirmed','Delivered','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_name`, `user_id`, `total_amount`, `order_date`, `status`) VALUES
(37, '', 17, 160.00, '2025-05-15 23:22:19', 'Pending'),
(38, '', 17, 300.00, '2025-05-15 23:51:10', 'Pending'),
(39, '', 20, 1550.00, '2025-05-16 00:18:47', 'Pending'),
(40, '', 20, 1220.00, '2025-05-16 00:22:04', 'Pending'),
(41, '', 20, 1220.00, '2025-05-16 00:22:29', 'Pending'),
(42, '', 21, 1220.00, '2025-05-16 01:23:38', 'Pending'),
(43, '', 20, 160.00, '2025-05-16 01:42:21', 'Pending'),
(44, '', 20, 30.00, '2025-05-16 01:48:25', ''),
(47, '', 17, 300.00, '2025-05-16 02:46:22', 'Pending'),
(48, '', 16, 160.00, '2025-05-16 03:29:52', 'Pending'),
(50, '', 22, 300.00, '2025-05-17 08:10:44', 'Pending'),
(54, '', 16, 160.00, '2025-05-17 09:20:51', 'Pending'),
(55, '', 16, 460.00, '2025-05-17 12:12:19', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_id`, `item_id`, `quantity`, `price`) VALUES
(37, 2, 1, 160.00),
(38, 10, 1, 300.00),
(39, 10, 1, 300.00),
(39, 11, 1, 30.00),
(43, 2, 1, 160.00),
(44, 11, 1, 30.00),
(47, 12, 1, 300.00),
(48, 2, 1, 160.00),
(54, 2, 1, 160.00),
(55, 2, 1, 160.00),
(55, 10, 1, 300.00);

--
-- Triggers `order_details`
--
DELIMITER $$
CREATE TRIGGER `update_stock_after_order` AFTER INSERT ON `order_details` FOR EACH ROW BEGIN
    UPDATE menu
    SET stock = stock - NEW.quantity
    WHERE item_id = NEW.item_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `user_id`, `amount`, `payment_method`, `payment_date`) VALUES
(1, 39, 20, 1550.00, '0', '2025-05-16 06:19:23'),
(2, 40, 20, 1220.00, '0', '2025-05-16 06:23:03'),
(3, 37, 17, 160.00, '0', '2025-05-16 08:45:56'),
(4, 48, 16, 160.00, '0', '2025-05-16 09:30:09'),
(5, 50, 22, 300.00, '0', '2025-05-17 14:11:44'),
(6, 54, 16, 160.00, '0', '2025-05-17 15:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_name` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `status` varchar(10) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `password`, `email`, `phone`, `created_at`, `user_name`, `role`, `status`) VALUES
(12, '$2y$10$4LJXfYTYjKlfNqni9MBxJu9PaE5lmjiniMoWnMZQF149/Sl5fWd2a', 'siamtalukdar3@gmail.com', NULL, '2024-12-22 03:40:50', 'Siam', 'customer', 'inactive'),
(13, '$2y$10$73c2Ila8fhqHS2uZK1yqDO6a0g6T7sFH48QIxJ5o8ruSnanF24Uty', 'irfan@gmail.com', NULL, '2024-12-22 11:54:58', 'Irfan', 'customer', 'inactive'),
(14, '$2y$10$PKG4j2S2254UgVukgPZeIuySWDrnq6cmAiQTTodKCPyvtg5wNKmPK', 'ferdous@gmail.com', NULL, '2024-12-22 17:42:46', 'Ferdous', 'customer', 'inactive'),
(16, '$2y$10$qB3qlKbDtgCg7gNQGqs7z.xDpoLDIn9BBYvhh8PDGdtvfTqc7B4rO', 'rh503648@gmail.com', NULL, '2025-05-15 10:21:54', 'robinhossain', 'customer', 'active'),
(17, '$2y$10$GP4c7hmyUzV47GVxw0bAlusMauY4mPf8YLM8GV0MYhIEX1Zhn0U5m', 'rahat@gmail.com', '+8801312427030', '2025-05-15 15:23:16', 'robinhossain', 'customer', 'active'),
(19, '$2y$10$nDkY2a5CTSlzE.4u2J1O0.O/z945B0STPn./3feL/mP0nNBlv97Iq', 'admin@gmail.com', '+8801312427030', '2025-05-15 15:34:41', 'admin', 'admin', 'active'),
(20, '$2y$10$7C8q4thATAV8aTa8SB0KmONXQLcZPQUGSeo.qWfBOEVs.Vct/53/a', 'rupom@gmail.com', '+8801312427039', '2025-05-16 04:04:15', 'Rupom', 'customer', 'inactive'),
(21, '$2y$10$QW2DlqaYsDzhwHAC6k1JY.o9ksff5cz6h6yheDaXofuvfu.vDLN.q', 'tusar@gmail.com', '+8801312427038', '2025-05-16 05:23:00', 'Tushar', 'customer', 'active'),
(22, '$2y$10$ujk1UpL9wSaZ8Sm81GwiB.shirfzHtrRbkpwhimgltOZ.eUjIOZ4O', 'saklain@gmail.com', '+880131242703', '2025-05-17 12:06:37', 'saklain70', 'customer', 'inactive');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD KEY `item_id` (`item_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu` (`item_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu` (`item_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
