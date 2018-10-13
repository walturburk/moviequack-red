CREATE DATABASE  IF NOT EXISTS `mqold` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `mqold`;
-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: localhost    Database: mqold
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.31-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `collection`
--

DROP TABLE IF EXISTS `collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection` (
  `id` mediumint(9) NOT NULL,
  `name` varchar(200) NOT NULL,
  `poster` text NOT NULL,
  `backdrop` text NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `follow`
--

DROP TABLE IF EXISTS `follow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `follow` (
  `follower` varchar(15) NOT NULL,
  `follows` varchar(15) NOT NULL,
  `timestamp` int(10) NOT NULL,
  UNIQUE KEY `follower` (`follower`,`follows`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genre`
--

DROP TABLE IF EXISTS `genre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genre` (
  `movie` varchar(15) NOT NULL,
  `genre` smallint(6) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`genre`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genrenames`
--

DROP TABLE IF EXISTS `genrenames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genrenames` (
  `id` smallint(6) NOT NULL,
  `name` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incollection`
--

DROP TABLE IF EXISTS `incollection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incollection` (
  `collection` mediumint(9) NOT NULL,
  `movie` varchar(15) NOT NULL,
  UNIQUE KEY `collection` (`collection`,`movie`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language` (
  `movie` varchar(15) NOT NULL,
  `lang` varchar(3) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`lang`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movie`
--

DROP TABLE IF EXISTS `movie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movie` (
  `id` varchar(15) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `originaltitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `year` smallint(6) NOT NULL,
  `releasedate` date NOT NULL,
  `backdrop` varchar(255) NOT NULL,
  `budget` int(10) NOT NULL,
  `homepage` varchar(255) NOT NULL,
  `imdbid` varchar(10) NOT NULL,
  `language` varchar(4) NOT NULL,
  `overview` text NOT NULL,
  `poster` varchar(255) NOT NULL,
  `revenue` int(20) NOT NULL,
  `runtime` smallint(6) NOT NULL,
  `status` varchar(15) NOT NULL,
  `tagline` varchar(255) NOT NULL,
  `tmdbid` mediumint(9) NOT NULL,
  `searchstring` text NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post` (
  `id` varchar(15) NOT NULL,
  `item` varchar(15) NOT NULL,
  `emoji` varchar(30) NOT NULL,
  `message` varchar(255) NOT NULL,
  `userid` varchar(15) NOT NULL,
  `timestamp` int(10) NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `producedby`
--

DROP TABLE IF EXISTS `producedby`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producedby` (
  `movie` varchar(15) NOT NULL,
  `company` smallint(6) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`company`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `producedin`
--

DROP TABLE IF EXISTS `producedin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producedin` (
  `movie` varchar(15) NOT NULL,
  `country` varchar(6) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`country`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `productioncompany`
--

DROP TABLE IF EXISTS `productioncompany`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productioncompany` (
  `id` smallint(6) NOT NULL,
  `name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `provider`
--

DROP TABLE IF EXISTS `provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider` (
  `name` varchar(32) NOT NULL,
  `short` varchar(4) NOT NULL,
  `clear` varchar(64) NOT NULL,
  `slug` varchar(32) NOT NULL,
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ratemovie`
--

DROP TABLE IF EXISTS `ratemovie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ratemovie` (
  `movie` varchar(15) NOT NULL,
  `user` varchar(15) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `timestamp` int(10) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`user`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `region`
--

DROP TABLE IF EXISTS `region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `region` (
  `short` varchar(2) NOT NULL,
  `locale` varchar(7) NOT NULL,
  `country` varchar(50) NOT NULL,
  `currency` varchar(4) NOT NULL,
  `currencyname` varchar(32) NOT NULL,
  PRIMARY KEY (`locale`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reply`
--

DROP TABLE IF EXISTS `reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reply` (
  `reply` varchar(15) NOT NULL,
  `original` varchar(15) NOT NULL,
  UNIQUE KEY `reply` (`reply`,`original`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` varchar(33) NOT NULL,
  `time` int(10) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `browser` varchar(255) NOT NULL,
  `user` varchar(15) NOT NULL,
  UNIQUE KEY `time` (`time`,`user`),
  UNIQUE KEY `id` (`id`,`user`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stream`
--

DROP TABLE IF EXISTS `stream`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stream` (
  `movieid` varchar(15) NOT NULL,
  `region` varchar(8) NOT NULL,
  `type` varchar(10) NOT NULL,
  `provider` smallint(4) NOT NULL,
  `price` smallint(6) NOT NULL,
  `currency` varchar(6) NOT NULL,
  `link` varchar(255) NOT NULL,
  `def` varchar(3) NOT NULL,
  `dateproviderid` varchar(13) NOT NULL,
  `timestamp` int(10) NOT NULL,
  UNIQUE KEY `movieid` (`movieid`,`region`,`type`,`provider`,`def`),
  UNIQUE KEY `movieid_2` (`movieid`,`region`,`type`,`provider`,`def`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `movie` varchar(15) NOT NULL,
  `user` varchar(15) NOT NULL,
  `tag` varchar(20) NOT NULL,
  `timestamp` int(10) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`user`,`tag`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `username` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  PRIMARY KEY (`username`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote` (
  `post` varchar(15) NOT NULL,
  `user` varchar(15) NOT NULL,
  `timestamp` int(10) NOT NULL,
  `upvote` tinyint(1) NOT NULL,
  `downvote` tinyint(1) NOT NULL,
  PRIMARY KEY (`post`,`user`),
  UNIQUE KEY `post` (`post`,`user`)
);
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
