-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2024 at 11:11 AM
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
-- Database: `inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `money_expenses`
--

CREATE TABLE `money_expenses` (
  `expenses_id` int(11) NOT NULL,
  `Catogory` varchar(100) NOT NULL,
  `Product_name` varchar(100) NOT NULL,
  `Amount` int(11) NOT NULL,
  `description` text NOT NULL,
  `expense_Date` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `money_expenses`
--

INSERT INTO `money_expenses` (`expenses_id`, `Catogory`, `Product_name`, `Amount`, `description`, `expense_Date`, `created_at`) VALUES
(56, 'Necessary Items', 'Soap', 1000, '', '2024-03-31 15:20:32', '2024-03-31 13:20:55'),
(57, 'Necessary Items', 'Lisole', 750, '', '2024-03-31 15:20:55', '2024-03-31 13:21:10'),
(58, 'Necessary Items', 'shampoo', 5000, '', '2024-03-31 15:21:12', '2024-03-31 13:21:33'),
(59, 'Necessary Items', 'Colgate ', 1000, '', '2024-03-31 15:21:33', '2024-03-31 13:21:58'),
(60, 'groccery', 'Rice', 400, '', '2024-03-31 15:21:59', '2024-03-31 13:22:30'),
(61, 'groccery', 'Sugar', 300, '', '2024-03-31 15:22:31', '2024-03-31 13:22:53'),
(62, 'groccery', 'Oil', 1000, '', '2024-03-31 15:22:53', '2024-03-31 13:23:08'),
(63, 'Vegetables', 'Tomato', 300, '', '2024-03-31 15:23:08', '2024-03-31 13:23:39'),
(64, 'Vegetables', 'Potato', 600, '', '2024-03-31 15:23:40', '2024-03-31 13:23:57'),
(65, 'Vegetables', 'Onion', 800, '', '2024-03-31 15:23:58', '2024-03-31 13:24:20'),
(66, 'Electricity Bill', 'March Month Electricity Bill', 1000, 'March Month Electricity Bill', '2024-03-31', '2024-03-31 13:29:35'),
(67, 'Saop', 'lux', 2000, '', '2024-04-15 14:36:16', '2024-04-15 12:37:25'),
(68, 'sallery', 'yuvraj', 10000, 'description', '2024-04-14', '2024-04-15 12:38:27');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(50) NOT NULL,
  `quantity` int(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `category_id` int(50) NOT NULL,
  `date_added` varchar(100) NOT NULL,
  `Add_by` varchar(100) NOT NULL,
  `edit_by` varchar(100) NOT NULL,
  `lastedit_date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `quantity`, `category`, `category_id`, `date_added`, `Add_by`, `edit_by`, `lastedit_date`) VALUES
(98, 'Soap', 20, 50, '', 25, '2024-03-31 15:20:32', 'admin', '', ''),
(99, 'Lisole', 50, 15, '', 25, '2024-03-31 15:20:55', 'admin', '', ''),
(100, 'shampoo', 100, 50, '', 25, '2024-03-31 15:21:12', 'admin', '', ''),
(101, 'Colgate ', 20, 50, '', 25, '2024-03-31 15:21:33', 'admin', '', ''),
(102, 'Rice', 20, 20, '', 24, '2024-03-31 15:21:59', 'admin', '', ''),
(103, 'Sugar', 20, 15, '', 24, '2024-03-31 15:22:31', 'admin', '', ''),
(104, 'Oil', 100, 10, '', 24, '2024-03-31 15:22:53', 'admin', '', ''),
(105, 'Tomato', 30, 10, '', 23, '2024-03-31 15:23:08', 'admin', '', ''),
(106, 'Potato', 20, 30, '', 23, '2024-03-31 15:23:40', 'admin', '', ''),
(107, 'Onion', 40, 20, '', 23, '2024-03-31 15:23:58', 'admin', '', ''),
(108, 'lux', 20, 100, '', 27, '2024-04-15 14:36:16', 'admin', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `products_category`
--

CREATE TABLE `products_category` (
  `id` int(11) NOT NULL,
  `category_value` varchar(100) NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products_category`
--

INSERT INTO `products_category` (`id`, `category_value`, `isActive`, `created_at`) VALUES
(23, 'Vegetables', 1, '2024-03-11 11:08:58'),
(24, 'groccery', 1, '2024-03-11 11:09:28'),
(25, 'Necessary Items', 1, '2024-03-11 11:09:53'),
(27, 'Saop', 1, '2024-04-15 12:35:54');

-- --------------------------------------------------------

--
-- Table structure for table `product_balances`
--

CREATE TABLE `product_balances` (
  `Product_Balances_id` int(11) NOT NULL,
  `Product_name` varchar(100) NOT NULL,
  `Product_catogory_id` int(11) NOT NULL,
  `Balance_Quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_balances`
--

INSERT INTO `product_balances` (`Product_Balances_id`, `Product_name`, `Product_catogory_id`, `Balance_Quantity`) VALUES
(39, 'Soap', 25, 49),
(40, 'Lisole', 25, 15),
(41, 'shampoo', 25, 50),
(42, 'Colgate ', 25, 40),
(43, 'Rice', 24, 20),
(44, 'Sugar', 24, 15),
(45, 'Oil', 24, 10),
(46, 'Tomato', 23, 10),
(47, 'Potato', 23, 30),
(48, 'Onion', 23, 20),
(49, 'lux', 27, 99);

-- --------------------------------------------------------

--
-- Table structure for table `product_expenses`
--

CREATE TABLE `product_expenses` (
  `Product_Expenses_id` int(11) NOT NULL,
  `Product_id` int(11) NOT NULL,
  `Expenses_quantity` int(11) NOT NULL,
  `Expenses_by` varchar(100) NOT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_expenses`
--

INSERT INTO `product_expenses` (`Product_Expenses_id`, `Product_id`, `Expenses_quantity`, `Expenses_by`, `Created_at`) VALUES
(118, 42, 10, 'YUVRJ SINGH', '2024-03-31 13:30:14'),
(119, 39, 1, 'YUVRJ SINGH', '2024-04-15 12:35:45'),
(120, 49, 1, 'yuvraj', '2024-04-15 12:37:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', 'sahillaskar137@gmail.com', 'admin@123', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `money_expenses`
--
ALTER TABLE `money_expenses`
  ADD PRIMARY KEY (`expenses_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products_category`
--
ALTER TABLE `products_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_balances`
--
ALTER TABLE `product_balances`
  ADD PRIMARY KEY (`Product_Balances_id`);

--
-- Indexes for table `product_expenses`
--
ALTER TABLE `product_expenses`
  ADD PRIMARY KEY (`Product_Expenses_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `money_expenses`
--
ALTER TABLE `money_expenses`
  MODIFY `expenses_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `products_category`
--
ALTER TABLE `products_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product_balances`
--
ALTER TABLE `product_balances`
  MODIFY `Product_Balances_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `product_expenses`
--
ALTER TABLE `product_expenses`
  MODIFY `Product_Expenses_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
