-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 03, 2018 at 12:59 PM
-- Server version: 5.7.23-0ubuntu0.16.04.1
-- PHP Version: 7.0.30-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `orr_projects`
--

--
-- Dumping data for table `my_user`
--

INSERT INTO `my_user` (`id`, `user`, `val_pass`, `password`, `prefix`, `fname`, `lname`, `status`, `sec_owner`, `sec_user`, `sec_time`, `sec_ip`, `sec_script`) VALUES
(1, 'root', 0x3161316463393163393037333235633639323731646466306339343462633732, '', 'นาย', 'ผู้ดูแลระบบ', 'ทดสอบ', 0, 'root', 'root', '2018-07-09 10:08:31', '127.0.0.1', 'import');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
