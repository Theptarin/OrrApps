-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 09, 2018 at 04:42 PM
-- Server version: 5.7.22-0ubuntu0.16.04.1
-- PHP Version: 7.0.28-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `orr_projects`
--

-- --------------------------------------------------------

--
-- Table structure for table `my_activity`
--

CREATE TABLE `my_activity` (
  `id` int(11) NOT NULL,
  `description` text NOT NULL,
  `sec_user` varchar(20) NOT NULL DEFAULT '',
  `sec_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sec_ip` varchar(20) NOT NULL DEFAULT '',
  `sec_script` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `my_activity`
--

INSERT INTO `my_activity` (`id`, `description`, `sec_user`, `sec_time`, `sec_ip`, `sec_script`) VALUES
(1, 'User root is signin.', 'root', '2018-05-09 09:05:09', '10.1.16.4', 'authorize_orr');

-- --------------------------------------------------------

--
-- Table structure for table `my_datafield`
--

CREATE TABLE `my_datafield` (
  `field_id` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL COMMENT 'คำอธิบาย',
  `sec_owner` varchar(20) NOT NULL,
  `sec_user` varchar(20) NOT NULL,
  `sec_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sec_ip` varchar(20) NOT NULL,
  `sec_script` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `my_datafield`
--

INSERT INTO `my_datafield` (`field_id`, `name`, `description`, `sec_owner`, `sec_user`, `sec_time`, `sec_ip`, `sec_script`) VALUES
('any_use', '', '', 'root', 'root', '2018-04-29 10:36:51', '127.0.0.1', 'Project:my_datafield'),
('fname', 'ชื่อ', '', 'orr', 'orr', '2018-04-30 07:59:40', '127.0.0.1', 'Project:my_datafield'),
('lname', 'นามสกุล', '', 'orr', 'orr', '2018-04-30 07:59:56', '127.0.0.1', 'Project:my_datafield'),
('name', 'ชื่อเรียก', 'คำที่ตั้งขึ้นเพื่อใช้เรียก', 'root', 'orr', '2018-04-30 07:58:32', '127.0.0.1', 'Project:my_datafield'),
('sec_ip', 'เลขไอพี', '', 'orr', 'orr', '2018-04-30 10:51:00', '127.0.0.1', 'Project:my_datafield'),
('sec_owner', 'เจ้าของ', '', 'orr', 'orr', '2018-04-30 10:47:04', '127.0.0.1', 'Project:my_datafield'),
('sec_script', 'แก้ไขจาก', '', 'orr', 'orr', '2018-04-30 10:51:23', '127.0.0.1', 'Project:my_datafield'),
('sec_time', 'แก้ไขเมื่อ', '', 'orr', 'orr', '2018-04-30 10:50:40', '127.0.0.1', 'Project:my_datafield'),
('sec_user', 'แก้ไขโดย', '', 'orr', 'orr', '2018-04-30 10:50:22', '127.0.0.1', 'Project:my_datafield'),
('status', 'สถานะ', '', 'orr', 'orr', '2018-04-30 08:01:15', '127.0.0.1', 'Project:my_datafield'),
('user', 'รหัสผู้ใช้ระบบ', '', 'orr', 'orr', '2018-04-30 07:56:21', '127.0.0.1', 'Project:my_datafield');

-- --------------------------------------------------------

--
-- Table structure for table `my_sys`
--

CREATE TABLE `my_sys` (
  `sys_id` varchar(50) NOT NULL DEFAULT '',
  `any_use` tinyint(4) NOT NULL DEFAULT '0',
  `aut_user` tinyint(4) NOT NULL DEFAULT '0',
  `aut_group` tinyint(4) NOT NULL DEFAULT '0',
  `aut_any` tinyint(4) NOT NULL DEFAULT '0',
  `aut_god` tinyint(4) NOT NULL DEFAULT '0',
  `aut_can_from` varchar(50) NOT NULL,
  `title` varchar(60) NOT NULL,
  `description` text NOT NULL,
  `sec_owner` varchar(20) NOT NULL,
  `sec_user` varchar(20) NOT NULL DEFAULT '',
  `sec_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sec_ip` varchar(20) NOT NULL DEFAULT '',
  `sec_script` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='สิทธิการใช้งาน';

--
-- Dumping data for table `my_sys`
--

INSERT INTO `my_sys` (`sys_id`, `any_use`, `aut_user`, `aut_group`, `aut_any`, `aut_god`, `aut_can_from`, `title`, `description`, `sec_owner`, `sec_user`, `sec_time`, `sec_ip`, `sec_script`) VALUES
('Project_', 1, 3, 2, 1, 1, '', 'ตั้งค่าระบบงาน', '', 'root', 'root', '2018-05-09 08:37:05', '127.0.0.1', 'Import'),
('Project_my_sys', 1, 3, 2, 1, 1, '', 'กำหนดข้อมูลโปรแกรม', '', 'root', 'root', '2018-05-09 08:37:57', '127.0.0.1', 'Import'),
('Project_my_user', 1, 3, 2, 1, 1, 'Project:my_user', 'ข้อมูลผู้ใช้งาน', '', 'root', 'root', '2018-05-09 08:38:47', '127.0.0.1', 'Import');

-- --------------------------------------------------------

--
-- Table structure for table `my_user`
--

CREATE TABLE `my_user` (
  `id` int(11) NOT NULL,
  `user` varchar(20) NOT NULL DEFAULT '',
  `val_pass` blob NOT NULL,
  `password` varchar(100) NOT NULL COMMENT 'Input Password',
  `prefix` varchar(30) NOT NULL DEFAULT '',
  `fname` varchar(50) NOT NULL DEFAULT '',
  `lname` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL,
  `sec_owner` varchar(20) NOT NULL,
  `sec_user` varchar(20) NOT NULL,
  `sec_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sec_ip` varchar(20) NOT NULL,
  `sec_script` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `my_user`
--

INSERT INTO `my_user` (`id`, `user`, `val_pass`, `password`, `prefix`, `fname`, `lname`, `status`, `sec_owner`, `sec_user`, `sec_time`, `sec_ip`, `sec_script`) VALUES
(1, 'root', 0x3161316463393163393037333235633639323731646466306339343462633732, '', 'คุณ', 'ผู้ดูแลระบบ', 'ทดสอบ', 0, '', 'root', '2018-05-09 08:34:58', '127.0.0.1', 'import');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `my_activity`
--
ALTER TABLE `my_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sec_script` (`sec_script`);

--
-- Indexes for table `my_datafield`
--
ALTER TABLE `my_datafield`
  ADD PRIMARY KEY (`field_id`);

--
-- Indexes for table `my_sys`
--
ALTER TABLE `my_sys`
  ADD PRIMARY KEY (`sys_id`);

--
-- Indexes for table `my_user`
--
ALTER TABLE `my_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `my_activity`
--
ALTER TABLE `my_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `my_user`
--
ALTER TABLE `my_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
