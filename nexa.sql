-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 07 Eki 2025, 07:04:35
-- Sunucu sürümü: 9.1.0
-- PHP Sürümü: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `nexa`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `company`
--

DROP TABLE IF EXISTS `company`;
CREATE TABLE IF NOT EXISTS `company` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_turkish_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `adres` text COLLATE utf8mb4_turkish_ci,
  `phone` varchar(50) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `fax` varchar(50) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `website` varchar(150) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `company_bank_accounts`
--

DROP TABLE IF EXISTS `company_bank_accounts`;
CREATE TABLE IF NOT EXISTS `company_bank_accounts` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` int UNSIGNED NOT NULL,
  `banka_adi` varchar(150) COLLATE utf8mb4_turkish_ci NOT NULL,
  `sube_adi` varchar(150) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `hesap_no` varchar(50) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `iban` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL,
  `para_birimi` varchar(10) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_company_bank_iban` (`iban`),
  KEY `idx_company_bank_company_id` (`company_id`),
  KEY `idx_company_bank_banka_adi` (`banka_adi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `company_contacts`
--

DROP TABLE IF EXISTS `company_contacts`;
CREATE TABLE IF NOT EXISTS `company_contacts` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` int UNSIGNED NOT NULL,
  `ad` varchar(100) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `gorev` varchar(100) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `telefon` varchar(50) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `eposta` varchar(150) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_company_contacts_company_id` (`company_id`),
  KEY `idx_company_contacts_eposta` (`eposta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `company_desc`
--

DROP TABLE IF EXISTS `company_desc`;
CREATE TABLE IF NOT EXISTS `company_desc` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` int UNSIGNED NOT NULL,
  `aciklama` text COLLATE utf8mb4_turkish_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `lastname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`),
  UNIQUE KEY `uniq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `company`
--
ALTER TABLE `company`
  ADD CONSTRAINT `fk_company_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `company_bank_accounts`
--
ALTER TABLE `company_bank_accounts`
  ADD CONSTRAINT `fk_company_bank_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `company_contacts`
--
ALTER TABLE `company_contacts`
  ADD CONSTRAINT `fk_company_contacts_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `company_desc`
--
ALTER TABLE `company_desc`
  ADD CONSTRAINT `fk_company_desc_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
