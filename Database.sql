-- -------------------------------------------
--
-- DEFAULT DATABASE TABLE STRUCTURES
-- Diver : MySQL
-- -------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` BIGINT(11) NOT NULL,
  `option_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Option name must be as unique string.',
  `option_value` LONGTEXT COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_name` (`option_name`);

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` BIGINT(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id`          BIGINT(11) NOT NULL,
  `username`    VARCHAR(120) COLLATE utf8_unicode_ci NOT NULL,
  `email`       VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `password`    VARCHAR(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'sha1 string PasswordHash - (PhPass by OpenWall)',
  `first_name`  VARCHAR(120) COLLATE utf8_unicode_ci NOT NULL,
  `last_name`   VARCHAR(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role`        VARCHAR(100) DEFAULT 'unknown',
  `token_key`   VARCHAR(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT  'Token Key',
  `property`    TEXT NOT NULL DEFAULT '',
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NULL DEFAULT '1990-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP
                          COMMENT 'use `1990-01-01 00:00:00` to prevent error sql time stamp zero value'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE INDEX `username_and_email` (`username`,`email`),
  ADD KEY `username` (`username`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;


--
-- Add data default administrator table `users`
--
INSERT IGNORE INTO users(`username`, `email`, `first_name`, `role`, `token_key`, `password`)
  VALUES ('admin', 'admin@example.com', 'Administrator', 'admin:super', sha2(concat(NOW(), RAND()), 512), sha1(concat(NOW(), RAND())));

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;