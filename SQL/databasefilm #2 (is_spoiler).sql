-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2026 at 04:37 AM
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
-- Database: `databasefilm`
--

-- --------------------------------------------------------

--
-- Table structure for table `cast_members`
--

CREATE TABLE `cast_members` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `actor_name` varchar(255) NOT NULL,
  `actor_photo` varchar(500) DEFAULT NULL,
  `character_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cast_members`
--

INSERT INTO `cast_members` (`id`, `movie_id`, `actor_name`, `actor_photo`, `character_name`) VALUES
(10, 17, 'Tom Hollan', 'uploads/actors/1780755412_Frame 1 (6).png', 'Peket pake'),
(11, 17, 'Martin', 'uploads/actors/1780756162_6a242ec22a410_profile.jpg', 'Matrix');

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `poster` text NOT NULL,
  `trailer` text NOT NULL,
  `watchLink` text DEFAULT NULL,
  `year` varchar(10) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `genre` text NOT NULL,
  `director` varchar(255) DEFAULT NULL,
  `directorPhoto` text DEFAULT NULL,
  `plot` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `rating`, `poster`, `trailer`, `watchLink`, `year`, `duration`, `genre`, `director`, `directorPhoto`, `plot`, `created_at`) VALUES
(6, 'Avengers: Endgame', 8.4, 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg', 'https://www.youtube.com/embed/TcMBFSGVi1c', 'https://www.youtube.com', '2019', '3h 1m', '[\"Action\",\"Adventure\",\"Sci-Fi\"]', 'Anthony Russo, Joe Russo', NULL, 'After the devastating events of Avengers: Infinity War, the universe is in ruins due to the efforts of the Mad Titan, Thanos.', '2025-12-06 14:37:19'),
(7, 'Spider-Man: No Way Home', 8.2, 'https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg', 'https://www.youtube.com/embed/JfVOs4VSpmA', '#', '2021', '2h 28m', '[\"Action\",\"Adventure\",\"Sci-Fi\"]', 'Jon Watts', NULL, 'Peter Parker is unmasked and no longer able to separate his normal life from the high-stakes of being a super-hero.', '2025-12-06 14:37:19'),
(8, 'Inception', 8.8, 'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg', 'https://www.youtube.com/embed/YoHD9XEInc0', 'https://youtube.com', '2010', '2h 28m', '[\"Action\",\"Sci-Fi\",\"Thriller\"]', 'Christopher Nolan', NULL, 'Inception (2010) mengikuti kisah Dom Cobb, seorang pencuri ulung yang memiliki kemampuan langka: ia dapat menyusup ke dalam mimpi seseorang untuk mencuri rahasia terdalam dari alam bawah sadar mereka. Keahliannya membuat Cobb menjadi aset berharga dalam dunia spionase modern, namun juga menjadikannya buronan internasional dan memaksanya hidup jauh dari anak-anaknya. Kesempatan untuk menebus masa lalunya datang ketika seorang pengusaha kaya menawarkan misi yang berbeda dari biasanya—bukan mencuri ide, tetapi menanamkan ide ke dalam pikiran target. Proses ini dikenal sebagai inception, sebuah teknik yang jauh lebih berbahaya karena mengharuskan tim memasuki mimpi berlapis-lapis hingga ke level terdalam pikiran manusia.rnrnCobb membentuk tim elit berisi arsitek mimpi, pemalsu identitas, ahli sedatif, dan operator taktis. Bersama-sama mereka merancang sebuah rencana kompleks untuk mempengaruhi pewaris perusahaan besar agar mengambil keputusan yang dapat mengubah dunia bisnis global. Namun semakin dalam mereka masuk ke dunia mimpi, semakin kabur batas antara realitas dan ilusi. Cobb juga dihantui oleh bayangan istrinya, Mal, yang muncul di dalam mimpi sebagai manifestasi rasa bersalahnya dan mengancam seluruh misi. Ketika waktu semakin menipis dan setiap lapisan mimpi membawa risiko kematian atau terperangkap selamanya di limbo, Cobb dan timnya harus melawan ancaman dari luar maupun dari pikiran mereka sendiri. Inception menghadirkan perjalanan psikologis penuh teka-teki, aksi intens, serta pertanyaan filosofis tentang mimpi, realitas, dan kekuatan pikiran manusia.', '2025-12-06 14:37:19'),
(9, 'The Dark Knight', 9.0, 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg', 'https://www.youtube.com/embed/EXeTwQWrcwY', '#', '2008', '2h 32m', '[\"Action\",\"Crime\",\"Drama\",\"Thriller\"]', 'Christopher Nolan', NULL, 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest tests.', '2025-12-06 14:37:19'),
(10, 'Interstellar', 8.6, 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', 'https://www.youtube.com/embed/zSWdZVtXT7E', '#', '2014', '2h 49m', '[\"Adventure\",\"Drama\",\"Sci-Fi\"]', 'Christopher Nolan', NULL, 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity survival.', '2025-12-06 14:37:19'),
(11, 'The Shawshank Redemption', 9.3, 'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg', 'https://www.youtube.com/embed/6hB3S9bIaco', '#', '1994', '2h 22m', '[\"Drama\",\"Crime\"]', 'Frank Darabont', NULL, 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.', '2025-12-06 14:37:19'),
(12, 'Pulp Fiction', 8.9, 'https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg', 'https://www.youtube.com/embed/s7EdQ4FqbhY', '#', '1994', '2h 34m', '[\"Crime\",\"Drama\",\"Thriller\"]', 'Quentin Tarantino', NULL, 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine in four tales of violence and redemption.', '2025-12-06 14:37:19'),
(13, 'The Matrix', 8.7, 'https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg', 'https://www.youtube.com/embed/vKQi3bBA1y8', '#', '1999', '2h 16m', '[\"Action\",\"Sci-Fi\"]', 'Lana Wachowski, Lilly Wachowski', NULL, 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.', '2025-12-06 14:37:19'),
(14, 'Forrest Gump', 8.8, 'https://image.tmdb.org/t/p/w500/saHP97rTPS5eLmrLQEcANmKrsFl.jpg', 'https://www.youtube.com/embed/bLvqoHBptjg', '#', '1994', '2h 22m', '[\"Drama\",\"Romance\"]', 'Robert Zemeckis', NULL, 'The presidencies of Kennedy and Johnson, the events of Vietnam, Watergate and other historical events unfold through the perspective of an Alabama man.', '2025-12-06 14:37:19'),
(17, 'tes', 0.1, 'uploads/posters/1780763183_6a244a2f9943b_Screenshot 2026-05-18 230436.png', 'htttps://youtube.com', '', '2222', '2h 30m', '[\"Romance\"]', 'Martin TInzzx', '', 'asd', '2026-06-06 14:16:52');

-- --------------------------------------------------------

--
-- Table structure for table `movie_comments`
--

CREATE TABLE `movie_comments` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `is_spoiler` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movie_comments`
--

INSERT INTO `movie_comments` (`id`, `movie_id`, `user_id`, `user_name`, `comment`, `rating`, `is_spoiler`, `created_at`) VALUES
(1, 7, 2, 'user 123', 'haloo', 9.9, 0, '2025-12-06 14:38:45'),
(2, 7, 2, 'user 123', 'halo halo', 9.9, 0, '2025-12-10 13:32:22'),
(3, 17, 6, 'ZAN TZY', 'Film nya gajelas masa gambar nya COC', 7.0, 0, '2026-06-06 15:47:29'),
(4, 17, 6, 'ZAN TZY', 'tes', 2.0, 0, '2026-06-06 15:47:50'),
(5, 17, 6, 'ZAN TZY', 'tes', 9.9, 0, '2026-06-06 15:52:27'),
(6, 10, 1, 'Admin PoncolVerse', 'Entitas yang disangka hantu diawal film ternyata adalah sang MC sendiri yang terjebak didalam Blackhole', 7.0, 0, '2026-06-07 02:14:40'),
(7, 10, 6, 'ZAN TZY', 'tes', 7.0, 0, '2026-06-07 02:15:13');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `features` text NOT NULL,
  `max_profiles` int(11) DEFAULT 1,
  `video_quality` varchar(20) DEFAULT '720p',
  `concurrent_streams` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `price`, `duration_days`, `features`, `max_profiles`, `video_quality`, `concurrent_streams`, `created_at`) VALUES
(1, 'Basic', 54000.00, 30, 'Akses Film Terbatas,Kualitas 720p,1 Profil,1 Perangkat', 1, '720p', 1, '2025-11-26 14:59:45'),
(2, 'Standard', 120000.00, 30, 'Akses Semua Film,Kualitas 1080p,3 Profil,2 Perangkat,Download Offline', 3, '1080p', 2, '2025-11-26 14:59:45'),
(3, 'Premium', 186000.00, 30, 'Akses Semua Film,Kualitas 4K UHD,5 Profil,4 Perangkat,Download Offline,Prioritas Support', 5, '4K', 4, '2025-11-26 14:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_plan_id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `gross_amount` decimal(10,2) NOT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `midtrans_response` text DEFAULT NULL,
  `transaction_status` varchar(50) DEFAULT 'pending',
  `transaction_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `subscription_plan_id`, `order_id`, `transaction_id`, `gross_amount`, `payment_type`, `payment_method`, `midtrans_response`, `transaction_status`, `transaction_time`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'ORDER-2-1765572411', NULL, 54000.00, 'midtrans', NULL, NULL, 'pending', NULL, '2025-12-12 20:46:51', '2025-12-12 20:46:51'),
(2, 2, 2, 'ORDER-2-1765572425', NULL, 120000.00, 'midtrans', NULL, NULL, 'settlement', NULL, '2025-12-12 20:47:05', '2025-12-12 20:47:46'),
(3, 2, 1, 'ORDER-2-1765572474', NULL, 54000.00, 'midtrans', NULL, NULL, 'settlement', NULL, '2025-12-12 20:47:55', '2025-12-12 20:48:59'),
(4, 5, 2, 'ORDER-5-1780757434', NULL, 120000.00, 'midtrans', NULL, NULL, 'pending', NULL, '2026-06-06 14:50:35', '2026-06-06 14:50:35'),
(5, 5, 2, 'ORDER-5-1780757518', NULL, 120000.00, 'midtrans', NULL, NULL, 'settlement', NULL, '2026-06-06 14:51:59', '2026-06-06 14:52:24'),
(6, 6, 3, 'ORDER-6-1780798704', NULL, 186000.00, 'midtrans', NULL, NULL, 'settlement', NULL, '2026-06-07 02:18:24', '2026-06-07 02:18:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_photo` varchar(500) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `recoveryEmail` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `subscription_plan_id` int(11) DEFAULT NULL,
  `subscription_start` date DEFAULT NULL,
  `subscription_end` date DEFAULT NULL,
  `subscription_status` enum('active','expired','none') DEFAULT 'none',
  `status` varchar(50) DEFAULT 'Penonton',
  `joinDate` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `profile_photo`, `password`, `recoveryEmail`, `role`, `subscription_plan_id`, `subscription_start`, `subscription_end`, `subscription_status`, `status`, `joinDate`, `created_at`) VALUES
(1, 'Admin', 'PoncolVerse', 'admin@poncolverse.com', NULL, '0192023a7bbd73250516f069df18b500', 'recovery@poncolverse.com', 'admin', NULL, NULL, NULL, 'none', 'Administrator', '2025-10-29', '2025-10-29 02:24:39'),
(2, 'user', '123', 'user@gmail.com', NULL, '$2y$10$/1C2cF81VA8DrIv2u9fRiuU3K6rRSaY.kV0/WNupnSP4yHZEstudy', 'Tinzzx96@gmail.com', 'user', 1, '2025-12-12', '2026-01-11', 'active', 'Penonton', '2025-11-26', '2025-11-26 15:09:53'),
(3, 'goklas', 'martin', 'goklas@gmail.com', NULL, '$2y$10$qGTqXa1gQV0d7KmtCevrCuw1.VrLe7TKhLVNrw8aKZhtJsTrZSMUa', 'martin@gmail.com', 'user', NULL, NULL, NULL, 'none', 'Penonton', '2025-12-07', '2025-12-07 06:17:06'),
(4, 'tinzzx', 'Doechii', 'doechii@gmail.com', NULL, '$2y$10$j3cqmnacwv3PzkbtkRzY1.ZzJUtoONKIGneJxbuq.6GTDkveAUhOi', 'Tinzzx96@gmail.com', 'user', NULL, NULL, NULL, 'none', 'Penonton', '2025-12-07', '2025-12-07 15:03:53'),
(5, 'goklas', 'martin', 'martin@poncolverse.com', 'uploads/profiles/profile_5_1780756813.png', '$2y$10$by/k8RACLcyxySYKd3rWZulYorrB3.sM1I.p2MS9cs2xFjpHr0RE6', 'martin@pemulihan.com', 'user', 2, '2026-06-06', '2026-07-06', 'active', 'Penonton', '2026-06-06', '2026-06-06 14:39:48'),
(6, 'ZAN', 'TZY', 'zantzy04@gmail.com', 'uploads/profiles/profile_6_1780760783.png', '$2y$10$GdtZbMA4foHSSUifO9EqxeBP6NZDN6HjrFtqb7sAwtetQAl9f2a2a', 'zantzy04@gmail.com', 'user', 3, '2026-06-07', '2026-07-07', 'active', 'Penonton', '2026-06-06', '2026-06-06 15:46:07');

-- --------------------------------------------------------

--
-- Table structure for table `watchlist`
--

CREATE TABLE `watchlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `watchlist`
--

INSERT INTO `watchlist` (`id`, `user_id`, `movie_id`, `added_at`) VALUES
(8, 1, 12, '2026-06-06 16:27:55'),
(9, 1, 11, '2026-06-06 16:28:02'),
(10, 1, 14, '2026-06-06 16:28:05'),
(11, 1, 10, '2026-06-06 16:28:08'),
(12, 1, 13, '2026-06-06 16:28:11'),
(13, 1, 8, '2026-06-06 16:28:14');

-- --------------------------------------------------------

--
-- Table structure for table `watch_history`
--

CREATE TABLE `watch_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `watched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `website_testimonials`
--

CREATE TABLE `website_testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_testimonials`
--

INSERT INTO `website_testimonials` (`id`, `user_id`, `user_name`, `message`, `rating`, `is_approved`, `created_at`) VALUES
(1, 3, 'goklas martin', 'ini adalah comment pertama di website ini', 4, 1, '2025-12-07 11:19:44'),
(2, 1, 'Admin PoncolVerse', 'halo gw admin poncolverse tes tes', 1, 1, '2025-12-07 11:37:55'),
(3, 2, 'user 123', 'halo cuy website poncolVerse tes', 5, 1, '2025-12-07 12:29:45'),
(4, 4, 'tinzzx Doechii', 'tes komen tes komen tes komen\\r\\nasd', 3, 1, '2025-12-07 15:04:42'),
(5, 6, 'ZAN TZY', 'Websitenya Mantep cuy!', 5, 1, '2026-06-06 16:12:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cast_members`
--
ALTER TABLE `cast_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movie_comments`
--
ALTER TABLE `movie_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subscription_plan_id` (`subscription_plan_id`),
  ADD KEY `idx_transaction_order` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_subscription` (`subscription_plan_id`,`subscription_status`);

--
-- Indexes for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_watchlist` (`user_id`,`movie_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_movie` (`user_id`,`movie_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `website_testimonials`
--
ALTER TABLE `website_testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cast_members`
--
ALTER TABLE `cast_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `movie_comments`
--
ALTER TABLE `movie_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `watch_history`
--
ALTER TABLE `watch_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `website_testimonials`
--
ALTER TABLE `website_testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cast_members`
--
ALTER TABLE `cast_members`
  ADD CONSTRAINT `cast_members_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_comments`
--
ALTER TABLE `movie_comments`
  ADD CONSTRAINT `movie_comments_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movie_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_subscription_plan` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `watchlist_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD CONSTRAINT `watch_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `watch_history_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `website_testimonials`
--
ALTER TABLE `website_testimonials`
  ADD CONSTRAINT `website_testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
