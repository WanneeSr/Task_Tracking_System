-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2025 at 06:25 PM
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
-- Database: `project_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 14, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-16 03:15:28'),
(2, 12, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 03:20:56'),
(3, 12, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 03:24:49'),
(4, 12, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 03:26:40'),
(5, 12, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 03:27:14'),
(6, 12, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-16 03:29:08'),
(7, 14, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 03:29:19'),
(8, 14, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-16 03:30:22'),
(9, 14, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 03:30:26'),
(10, 14, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-16 03:30:39'),
(11, 12, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 03:30:43'),
(12, 12, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-16 03:30:47'),
(13, 13, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 03:30:52'),
(14, 13, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-16 03:47:26'),
(15, 14, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-16 05:01:59'),
(21, 17, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:03:51'),
(22, 17, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 13:06:56'),
(23, 17, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:07:06'),
(24, 17, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 13:07:22'),
(25, 18, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:07:59'),
(26, 18, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 13:12:19'),
(29, 17, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:13:23'),
(30, 17, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 13:13:39'),
(31, 18, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:13:47'),
(32, 18, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 13:13:57'),
(35, 18, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:15:31'),
(36, 18, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 13:33:13'),
(39, 18, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:33:43'),
(40, 18, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 13:34:26'),
(43, 18, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:36:10'),
(44, 18, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 13:36:20'),
(45, 17, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 13:36:26'),
(46, 17, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 14:51:46'),
(47, 20, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 15:04:06'),
(49, 18, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 15:13:41'),
(50, 18, 'LOGOUT', 'ออกจากระบบ', '::1', NULL, '2025-02-17 15:15:06'),
(51, 18, 'LOGIN', 'เข้าสู่ระบบสำเร็จ', '::1', NULL, '2025-02-17 15:18:01'),
(52, 17, 'delete_user', 'ลบผู้ใช้ ID: 19 ออกจากระบบ', NULL, NULL, '2025-02-17 16:33:28'),
(53, 17, 'delete_user', 'ลบผู้ใช้ ID: 21 ออกจากระบบ', NULL, NULL, '2025-02-17 16:33:35'),
(54, 17, 'delete_user', 'ลบผู้ใช้ ID: 16 ออกจากระบบ', NULL, NULL, '2025-02-17 16:44:55'),
(55, 17, 'add_user', 'เพิ่มผู้ใช้ใหม่: min (ID: 27)', NULL, NULL, '2025-02-17 16:47:27'),
(56, 17, 'add_user', 'เพิ่มผู้ใช้ใหม่: minn', NULL, NULL, '2025-02-17 16:51:02'),
(57, 17, 'add_user', 'เพิ่มผู้ใช้ใหม่: kao', NULL, NULL, '2025-02-17 16:53:13'),
(58, 17, 'delete_user', 'ลบผู้ใช้ ID: 29 ออกจากระบบ', NULL, NULL, '2025-02-17 16:53:24'),
(59, 17, 'delete_user', 'ลบผู้ใช้ ID: 28 ออกจากระบบ', NULL, NULL, '2025-02-17 16:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `attach_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comm_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deadlines`
--

CREATE TABLE `deadlines` (
  `deadline_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'ฝ่ายพัฒนาซอฟต์แวร์', 'พัฒนาและดูแลระบบซอฟต์แวร์', '2025-02-17 10:07:49', '2025-02-17 10:07:49'),
(2, 'ฝ่ายการตลาด', 'วางแผนและดำเนินการด้านการตลาด', '2025-02-17 10:07:49', '2025-02-17 10:07:49'),
(3, 'ฝ่ายบัญชี', 'จัดการด้านการเงินและบัญชี', '2025-02-17 10:07:49', '2025-02-17 10:07:49'),
(4, 'ฝ่ายทรัพยากรบุคคล', 'บริหารจัดการบุคลากร', '2025-02-17 10:07:49', '2025-02-17 10:07:49'),
(5, 'ร้านตัดผม', 'ตัดผมชาย', '2025-02-17 10:08:10', '2025-02-17 10:08:10'),
(6, 'ร้านตัดผม', 'ตัดผมชาย', '2025-02-17 10:08:23', '2025-02-17 10:08:23');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `noti_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 12, '6b6245574347a86120560e03805f039a68e78e773a3076b3de2efd3013359430', '2025-02-15 07:28:29', '2025-02-15 05:28:29'),
(2, 12, '747a7eed07b49d715eee5e70da33c83d960562e367e75659cd76893746cace7b', '2025-02-15 07:32:08', '2025-02-15 05:32:08'),
(3, 12, '92ff97e6791cf4f633732a3077d67550fe78743a75f29aeea8025161dd4c4eed', '2025-02-15 07:32:17', '2025-02-15 05:32:17');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `status_id` int(11) NOT NULL,
  `name` enum('pending','in_progress','completed','canceled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `title`, `description`, `status`, `priority`, `due_date`, `created_at`, `updated_at`, `created_by`, `assigned_to`, `project_id`, `assigned_by`) VALUES
(80, 'dfsb', 'ghrhedhf', 'completed', 'low', '2025-02-19', '2025-02-17 13:32:26', '2025-02-17 17:24:58', 18, 15, NULL, 18),
(84, 'งานโรงเรียน', 'ไปชมผอ', 'completed', 'urgent', '2025-02-20', '2025-02-17 14:28:20', '2025-02-17 17:24:58', 17, 18, NULL, 17),
(86, 'สววว', 'ืกดเิดหิ', 'pending', 'high', '2025-02-18', '2025-02-17 15:29:14', '2025-02-17 17:24:58', 18, 12, NULL, 18),
(88, 'ดแฟแ', 'กดเฟเ', 'completed', 'high', '2025-02-20', '2025-02-17 17:41:54', '2025-02-17 17:56:40', 18, 20, NULL, NULL),
(89, 'อฟกอฟก', 'ฟเหกอิิฟกอ', 'completed', 'high', '2025-02-19', '2025-02-17 17:57:18', '2025-02-17 18:05:58', 18, 20, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `comment_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `token` varchar(64) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `profile_image` varchar(255) DEFAULT 'default-profile.png',
  `department_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `token`, `token_expires_at`, `created_at`, `updated_at`, `is_active`, `profile_image`, `department_id`, `phone`) VALUES
(12, 'lookthor2', 'lookthor@gmail.com', '$2y$10$851Ky/UlE0V2Xgo0rJHt8e6D39kugz/7vJVE1raotZXHRPEwL97UG', 'employee', '5fdf8aa6186c72b48ec6c5e4714a1a45ed660f257375bc9da977383f1b103e3f', '2025-02-15 22:30:43', '2025-02-14 17:09:43', '2025-02-16 03:30:43', 1, 'default-profile.png', NULL, NULL),
(13, 'rainny', 'rainny@gmail.com', '$2y$10$T77uM0kgYMTf57pqMBnHFOH4IuNr5oDk7BgWN1uqWjd1wBzeyVQ7y', 'manager', '21b022b76e5e9a0db13996da5c2143dc48e052586b482efd217e76f137af54f7', '2025-02-15 22:30:52', '2025-02-14 18:53:12', '2025-02-16 03:30:52', 1, 'default-profile.png', NULL, NULL),
(14, 'admin', 'admin@gmail.com', '$2y$10$8x.8gE3h5eziQ9Qg6wHfYOyPB3vSqQwBIEVzN3JWtMprTGQX4U.Hy', 'admin', 'c265073f528b9c06cc28ce4993ebf70e7100e268914e9d92ba3377bac7ccf887', '2025-02-16 00:01:59', '2025-02-14 21:05:22', '2025-02-17 16:17:02', 1, 'default-profile.png', NULL, NULL),
(15, 'jokjok', 'jokjok@gmail.com', '$2y$10$9A/LHNysneHBi2r6FG2k8e2ugvMJaFIIfXk6bD28bh8W9rSdQqoQ.', 'employee', NULL, NULL, '2025-02-15 20:04:12', '2025-02-16 03:04:12', 1, 'default-profile.png', NULL, NULL),
(17, 'k1', 'k1@gmail.com', '$2y$10$.ZL8PPQzwU0TY8wTAncXneOOvHhVQtvtu/YBg/M7uWgG/2SDytSvW', 'admin', 'ff5d372fc93dc5569debb5e663ca403b5d046ae591944b90db42771b6d0de344', '2025-02-17 08:36:26', '2025-02-17 06:03:05', '2025-02-17 17:16:18', 1, 'profile_17_1739787371.PNG', 2, '0917844184'),
(18, 'k2', 'k2@gmail.com', '$2y$10$/zPiesG7XSWZUOaqiDNUM.84E1qISKQova9m1CNxnNgHLvPF1tcnO', 'manager', '4fc42524bd52226567c2fb9f2b2f4f868309a48c093d8311ee389cddbbc9fa30', '2025-02-17 10:18:01', '2025-02-17 06:07:45', '2025-02-17 15:18:01', 1, '18_1739780103.PNG', NULL, NULL),
(20, 'k4', 'k4@gmail.com', '$2y$10$9TQAFrhi7WJ.1NtDHHWLIOLEtrN0VsNMpo7YB8Jz2GX.aTDMKUPWe', 'employee', NULL, NULL, '2025-02-17 07:56:32', '2025-02-17 18:05:32', 1, 'profile_20_1739790332.PNG', NULL, ''),
(27, 'min', 'min@gmail.com', '$2y$10$uRQ7d9VRrDRb/6Qmz8COn./1iiwsJhYc0CbNKkomcdyUoAdb1gqn6', 'manager', NULL, NULL, '2025-02-17 09:47:27', '2025-02-17 16:47:27', 1, 'default-profile.png', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`attach_id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comm_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deadlines`
--
ALTER TABLE `deadlines`
  ADD PRIMARY KEY (`deadline_id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`noti_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `attach_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comm_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deadlines`
--
ALTER TABLE `deadlines`
  MODIFY `deadline_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `noti_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
