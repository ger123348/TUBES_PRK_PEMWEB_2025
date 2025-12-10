-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 10, 2025 at 10:10 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `donations` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text,
  `target_amount` decimal(15,2) DEFAULT '0.00',
  `current_amount` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `donations` (`id`, `title`, `image`, `description`, `target_amount`, `current_amount`, `created_at`) VALUES
(4, 'Bantuan Banjir Sumatera', '1765213808_DONASI.jpg', 'Bantuan ini dikhususkan untuk korban bencana alam di wilayah sumatera', 1000000000.00, 0.00, '2025-12-08 17:10:08');

CREATE TABLE `donation_transactions` (
  `id` int NOT NULL,
  `donation_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `donor_name` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `message` text,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `donation_transactions` (`id`, `donation_id`, `user_id`, `donor_name`, `amount`, `payment_method`, `message`, `status`, `created_at`) VALUES
(3, 4, 5, 'Hamba Allah', 100000.00, 'bri', 'semoga cepat sehat', 'pending', '2025-12-08 17:18:27');

CREATE TABLE `events` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `event_date` datetime NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `type` enum('Seminar','Workshop','Lomba','Rapat') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `image_url` varchar(255) DEFAULT NULL,
  `quota` int DEFAULT '100',
  `status` enum('open','closed') DEFAULT 'open',
  `custom_fields` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `location`, `type`, `created_at`, `image_url`, `quota`, `status`, `custom_fields`) VALUES
(8, 'abc', 'acc', '2025-12-12 17:41:00', 'ad', 'Lomba', '2025-12-10 08:41:43', 'uploads/events/1765356103_69393247d0a2a.jpg', 1, 'open', '');

CREATE TABLE `event_registrations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `registered_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('confirmed','cancelled') DEFAULT 'confirmed',
  `custom_answers` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `event_registrations` (`id`, `user_id`, `event_id`, `registered_at`, `status`, `custom_answers`, `created_at`) VALUES
(1, 3, 1, '2025-12-08 18:52:44', 'confirmed', NULL, '2025-12-10 08:44:53'),
(2, 4, 1, '2025-12-08 19:00:30', 'confirmed', NULL, '2025-12-10 08:44:53'),
(3, 5, 4, '2025-12-08 21:00:35', 'confirmed', '{\"UNILA\":\"pantek\"}', '2025-12-10 08:44:53'),
(4, 5, 3, '2025-12-08 21:03:14', 'confirmed', '[]', '2025-12-10 08:44:53'),
(5, 5, 7, '2025-12-10 15:03:49', 'confirmed', '[]', '2025-12-10 08:44:53'),
(6, 5, 6, '2025-12-10 15:37:04', 'confirmed', '[]', '2025-12-10 08:44:53'),
(7, 5, 8, '2025-12-10 15:41:57', 'confirmed', '{\"Nomor_WhatsApp\":\"089523427890\"}', '2025-12-10 08:44:53');

CREATE TABLE `forum_comments` (
  `id` int NOT NULL,
  `topic_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `forum_comments` (`id`, `topic_id`, `user_id`, `content`, `created_at`, `parent_id`) VALUES
(2, 4, 5, '@bot halo bot apa kabar', '2025-12-10 08:57:35', NULL),
(4, 4, 5, '@bot halo', '2025-12-10 09:00:21', NULL),
(6, 4, 5, '@bot halo bot', '2025-12-10 09:01:53', NULL),
(8, 4, 5, '@bot kapan kita merdeka?', '2025-12-10 09:48:03', NULL),
(10, 4, 5, '@bot kapan indonesia medeka', '2025-12-10 09:50:53', NULL),
(12, 4, 5, '@bot halo bot', '2025-12-10 09:55:33', NULL),
(14, 4, 5, '@bot kapan indonesia bubar?', '2025-12-10 09:57:56', NULL);

CREATE TABLE `forum_replies` (
  `id` int NOT NULL,
  `topic_id` int NOT NULL,
  `user_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `forum_topics` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) DEFAULT 'General',
  `views` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `forum_topics` (`id`, `user_id`, `title`, `content`, `category`, `views`, `created_at`) VALUES
(4, 5, 'HALO', 'ABCDE', 'umum', 24, '2025-12-09 07:23:51');

CREATE TABLE `hero_images` (
  `id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `hero_images` (`id`, `image_path`, `uploaded_at`) VALUES
(1, '1765354158_IMG-20251209-WA0011.jpg', '2025-12-10 08:09:18'),
(2, '1765354172_IMG-20251209-WA0012.jpg', '2025-12-10 08:09:32'),
(3, '1765354183_IMG-20251209-WA0014.jpg', '2025-12-10 08:09:43'),
(4, '1765354190_IMG-20251209-WA0013.jpg', '2025-12-10 08:09:50');

CREATE TABLE `news` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `news` (`id`, `title`, `slug`, `content`, `image_url`, `created_at`) VALUES
(5, 'HIMATRO MELAKUKAN KUNJUNGAN KE PANTI ASUHAN', 'himatro-melakukan-kunjungan-ke-panti-asuhan', 'Pada hari ini, kami melaksanakan kegiatan program kerja kunjungan sosial ke Panti Asuhan sebagai wujud kepedulian dan kontribusi nyata terhadap masyarakat. Kegiatan ini bertujuan untuk memberikan bantuan moral maupun material kepada anak-anak panti asuhan, sekaligus mempererat hubungan antara organisasi dan lingkungan sosial sekitar. Selama kegiatan berlangsung, para peserta berinteraksi langsung dengan anak-anak melalui sesi bermain, berbagi cerita, serta penyerahan bantuan yang telah dikumpulkan sebelumnya. Seluruh rangkaian acara berjalan dengan lancar, penuh kehangatan, dan mendapat respon positif dari pihak panti asuhan. Dengan terlaksananya kegiatan ini, kami berharap adanya dampak baik yang berkelanjutan serta menjadi langkah awal untuk menyelenggarakan program-program sosial serupa di masa mendatang.', 'uploads/news/1765259595_berita.jpg', '2025-12-09 12:53:15');

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `action_link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `notifications` (`id`, `type`, `message`, `action_link`, `is_read`, `created_at`) VALUES
(1, 'event_registration', 'Pendaftar Baru: <b>admin</b> telah mendaftar di event <b>abc</b>.', 'event_participants.php?id=6', 1, '2025-12-10 08:37:04'),
(2, 'event_registration', 'Pendaftar Baru: <b>admin</b> telah mendaftar di event <b>abc</b>.', 'event_participants.php?id=8', 0, '2025-12-10 08:41:57');

CREATE TABLE `payment_methods` (
  `id` int NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_holder` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `payment_methods` (`id`, `bank_name`, `account_number`, `account_holder`, `logo`, `created_at`) VALUES
(1, 'BNI', '12345677', 'Daniel', 'https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg', '2025-12-08 15:10:38');

CREATE TABLE `site_settings` (
  `id` int NOT NULL,
  `hero_title` varchar(255) NOT NULL,
  `hero_subtitle` text NOT NULL,
  `hero_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `site_settings` (`id`, `hero_title`, `hero_subtitle`, `hero_description`) VALUES
(1, 'Tumbuh Bersama,', 'Berdampak Nyata', 'Bersama untuk Kemajuann');

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` datetime DEFAULT NULL,
  `position` varchar(50) DEFAULT 'Anggota',
  `phone` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `full_name`, `email`, `phone_number`, `password`, `role`, `avatar`, `bio`, `created_at`, `last_activity`, `position`, `phone`, `photo`) VALUES
(1, 'Admin Utama', 'admin@komunitas.com', NULL, 'hashed_password', 'admin', NULL, NULL, '2025-12-08 10:29:39', NULL, 'Anggota', NULL, NULL),
(3, 'Daniel Ardiyansah', 'danil@gmail.com', '089523427890', '$2y$10$ZY8UAN/opp0wI17JcuHHPORViryKGtFXIm0WbXlSr3lrWfFu7PV.G', 'member', 'uploads/avatar_3_1765193778.jpg', 'Halo', '2025-12-08 10:49:14', '2025-12-09 13:13:29', 'Anggota', NULL, NULL),
(4, 'Gerhana Malik Ibrahim', 'ger@gmail.com', NULL, '$2y$10$CoGIqmzCvSI/SYxpafgpA.mrya.bLWbPDa0CbAtYODC3BiRvjukFq', 'member', NULL, NULL, '2025-12-08 11:55:03', '2025-12-08 21:29:04', 'Anggota', NULL, NULL),
(5, 'admin', 'admin@gmail.com', NULL, '$2y$10$TrbuUUxzypkQnIQO/SmqJ.Rmi7Y8R91DNVWMT3JHFNtYGFJzAbyxu', 'admin', NULL, '', '2025-12-08 13:30:12', '2025-12-10 17:09:19', 'Bendahara', '089523427890', '5_1765263680.jpg'),
(999, 'AI Assistant', 'bot@community.com', NULL, 'bot_pass', 'member', NULL, NULL, '2025-12-10 08:54:26', NULL, 'Virtual Assistant', NULL, NULL);

CREATE TABLE `votings` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `type` enum('member','event') DEFAULT 'event',
  `status` enum('active','closed') DEFAULT 'active',
  `start_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `votings` (`id`, `title`, `description`, `type`, `status`, `start_date`, `end_date`, `created_at`) VALUES
(2, 'ketua kelas', 'pemilihan ketua kelas', 'member', 'active', '2025-12-09 13:26:13', '2025-12-10 00:00:00', '2025-12-09 06:26:13');

CREATE TABLE `voting_options` (
  `id` int NOT NULL,
  `voting_id` int NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text,
  `vote_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `voting_options` (`id`, `voting_id`, `option_name`, `image`, `description`, `vote_count`) VALUES
(3, 2, 'Ger', '1765261573_0_download.jpg', 'Tidak ada', 1),
(4, 2, 'Gaylank', '1765261573_1_pp.jpg', 'Tidak ada', 0);

CREATE TABLE `voting_votes` (
  `id` int NOT NULL,
  `voting_id` int NOT NULL,
  `user_id` int NOT NULL,
  `option_id` int NOT NULL,
  `voted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `voting_votes` (`id`, `voting_id`, `user_id`, `option_id`, `voted_at`) VALUES
(2, 2, 5, 3, '2025-12-10 08:39:36');

ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `donation_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donation_id` (`donation_id`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`user_id`,`event_id`);

ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `fk_comment_parent` (`parent_id`);

ALTER TABLE `forum_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `hero_images`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `votings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `voting_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voting_id` (`voting_id`);

ALTER TABLE `voting_votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voting_id` (`voting_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `option_id` (`option_id`);

ALTER TABLE `donations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `donation_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `event_registrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `forum_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

ALTER TABLE `forum_replies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `forum_topics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `hero_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `news`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `payment_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `site_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

ALTER TABLE `votings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `voting_options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `voting_votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `donation_transactions`
  ADD CONSTRAINT `donation_transactions_ibfk_1` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE;

ALTER TABLE `forum_comments`
  ADD CONSTRAINT `fk_comment_parent` FOREIGN KEY (`parent_id`) REFERENCES `forum_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`id`) ON DELETE CASCADE;

ALTER TABLE `forum_replies`
  ADD CONSTRAINT `forum_replies_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `forum_topics`
  ADD CONSTRAINT `forum_topics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `voting_options`
  ADD CONSTRAINT `voting_options_ibfk_1` FOREIGN KEY (`voting_id`) REFERENCES `votings` (`id`) ON DELETE CASCADE;

ALTER TABLE `voting_votes`
  ADD CONSTRAINT `voting_votes_ibfk_1` FOREIGN KEY (`voting_id`) REFERENCES `votings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `voting_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `voting_votes_ibfk_3` FOREIGN KEY (`option_id`) REFERENCES `voting_options` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;