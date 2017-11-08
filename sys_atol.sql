-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Хост: 10.0.0.56
-- Время создания: Ноя 08 2017 г., 22:21
-- Версия сервера: 5.7.19-17
-- Версия PHP: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `nopg_pravolog2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `sys_atol`
--

CREATE TABLE `sys_atol` (
  `atol:id` int(255) NOT NULL,
  `atol:external_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `atol:uuid` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'id запроса регистрации документа в АТОЛЕ',
  `atol:date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atol:document` longtext COLLATE utf8_unicode_ci,
  `atol:request` longtext COLLATE utf8_unicode_ci NOT NULL,
  `atol:request_status` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `atol:responce` longtext COLLATE utf8_unicode_ci NOT NULL,
  `atol:responce_status` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='События онлайн-касс АТОЛ';

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `sys_atol`
--
ALTER TABLE `sys_atol`
  ADD PRIMARY KEY (`atol:id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `sys_atol`
--
ALTER TABLE `sys_atol`
  MODIFY `atol:id` int(255) NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
