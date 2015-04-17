-- MySQL dump 10.9
--
-- Host: localhost    Database: russofileru_php
-- ------------------------------------------------------
-- Server version       4.1.10a-nt

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

--
-- Table structure for table `test_sections_seq`
--

DROP TABLE IF EXISTS `test_sections_seq`;
CREATE TABLE `test_sections_seq` (
  `id` int(11) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `test_sections_seq`
--


/*!40000 ALTER TABLE `test_sections_seq` DISABLE KEYS */;
LOCK TABLES `test_sections_seq` WRITE;
INSERT INTO `test_sections_seq` VALUES (12);
UNLOCK TABLES;
/*!40000 ALTER TABLE `test_sections_seq` ENABLE KEYS */;

--
-- Table structure for table `test_sections`
--

DROP TABLE IF EXISTS `test_sections`;
CREATE TABLE `test_sections` (
  `section_id` bigint(20) NOT NULL default '0',
  `section_left` bigint(20) NOT NULL default '0',
  `section_right` bigint(20) NOT NULL default '0',
  `section_level` int(11) default NULL,
  `section_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`section_id`),
  UNIQUE KEY `section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `test_sections`
--


/*!40000 ALTER TABLE `test_sections` DISABLE KEYS */;
LOCK TABLES `test_sections` WRITE;
INSERT INTO `test_sections` VALUES (1,1,24,0,'Root'),(2,2,3,1,'Node 1'),(3,4,11,1,'Node 2'),(4,12,19,1,'Node 3'),(5,20,21,1,'Node 4'),(6,22,23,1,'Node 5'),(7,5,6,2,'Subnode 1'),(8,7,8,2,'Subnode 2'),(9,9,10,2,'Subnode 3'),(10,13,16,2,'Subnode 1'),(11,17,18,2,'Subnode 2'),(12,14,15,3,'Subsubnode 1');
UNLOCK TABLES;
/*!40000 ALTER TABLE `test_sections` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

