CREATE DATABASE  IF NOT EXISTS `mqold`;


 

DROP TABLE IF EXISTS `collection`;
 
 
CREATE TABLE `collection` (
  `id` mediumint(9) NOT NULL,
  `name` varchar(200) NOT NULL,
  `poster` text NOT NULL,
  `backdrop` text NOT NULL,
  PRIMARY KEY (`id`)
);
 

DROP TABLE IF EXISTS `follow`;
 
 
CREATE TABLE `follow` (
  `follower` varchar(15) NOT NULL,
  `follows` varchar(15) NOT NULL,
  `timestamp` int(10) NOT NULL,
  UNIQUE KEY `follower` (`follower`,`follows`)
);
 

--
-- Table structure for table `genre`
--

DROP TABLE IF EXISTS `genre`;
 
 
CREATE TABLE `genre` (
  `movie` varchar(15) NOT NULL,
  `genre` smallint(6) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`genre`)
);
 

--
-- Table structure for table `genrenames`
--

DROP TABLE IF EXISTS `genrenames`;
 
 
CREATE TABLE `genrenames` (
  `id` smallint(6) NOT NULL,
  `name` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
);
 

--
-- Table structure for table `incollection`
--

DROP TABLE IF EXISTS `incollection`;
 
 
CREATE TABLE `incollection` (
  `collection` mediumint(9) NOT NULL,
  `movie` varchar(15) NOT NULL,
  UNIQUE KEY `collection` (`collection`,`movie`)
);
 

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
 
 
CREATE TABLE `language` (
  `movie` varchar(15) NOT NULL,
  `lang` varchar(3) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`lang`)
);
 

--
-- Table structure for table `movie`
--

DROP TABLE IF EXISTS `movie`;
 
 
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
 

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
 
 
CREATE TABLE `post` (
  `id` varchar(15) NOT NULL,
  `item` varchar(15) NOT NULL,
  `emoji` varchar(30) NOT NULL,
  `message` varchar(255) NOT NULL,
  `userid` varchar(15) NOT NULL,
  `timestamp` int(10) NOT NULL,
  PRIMARY KEY (`id`)
);
 

--
-- Table structure for table `producedby`
--

DROP TABLE IF EXISTS `producedby`;
 
 
CREATE TABLE `producedby` (
  `movie` varchar(15) NOT NULL,
  `company` smallint(6) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`company`)
);
 

--
-- Table structure for table `producedin`
--

DROP TABLE IF EXISTS `producedin`;
 
 
CREATE TABLE `producedin` (
  `movie` varchar(15) NOT NULL,
  `country` varchar(6) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`country`)
);
 

--
-- Table structure for table `productioncompany`
--

DROP TABLE IF EXISTS `productioncompany`;
 
 
CREATE TABLE `productioncompany` (
  `id` smallint(6) NOT NULL,
  `name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
);
 

--
-- Table structure for table `provider`
--

DROP TABLE IF EXISTS `provider`;
 
 
CREATE TABLE `provider` (
  `name` varchar(32) NOT NULL,
  `short` varchar(4) NOT NULL,
  `clear` varchar(64) NOT NULL,
  `slug` varchar(32) NOT NULL,
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);
 

--
-- Table structure for table `ratemovie`
--

DROP TABLE IF EXISTS `ratemovie`;
 
 
CREATE TABLE `ratemovie` (
  `movie` varchar(15) NOT NULL,
  `user` varchar(15) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `timestamp` int(10) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`user`)
);
 

--
-- Table structure for table `region`
--

DROP TABLE IF EXISTS `region`;
 
 
CREATE TABLE `region` (
  `short` varchar(2) NOT NULL,
  `locale` varchar(7) NOT NULL,
  `country` varchar(50) NOT NULL,
  `currency` varchar(4) NOT NULL,
  `currencyname` varchar(32) NOT NULL,
  PRIMARY KEY (`locale`)
);
 

--
-- Table structure for table `reply`
--

DROP TABLE IF EXISTS `reply`;
 
 
CREATE TABLE `reply` (
  `reply` varchar(15) NOT NULL,
  `original` varchar(15) NOT NULL,
  UNIQUE KEY `reply` (`reply`,`original`)
);
 

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
 
 
CREATE TABLE `session` (
  `id` varchar(33) NOT NULL,
  `time` int(10) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `browser` varchar(255) NOT NULL,
  `user` varchar(15) NOT NULL,
  UNIQUE KEY `time` (`time`,`user`),
  UNIQUE KEY `id` (`id`,`user`)
);
 

--
-- Table structure for table `stream`
--

DROP TABLE IF EXISTS `stream`;
 
 
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
 

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
 
 
CREATE TABLE `tag` (
  `movie` varchar(15) NOT NULL,
  `user` varchar(15) NOT NULL,
  `tag` varchar(20) NOT NULL,
  `timestamp` int(10) NOT NULL,
  UNIQUE KEY `movie` (`movie`,`user`,`tag`)
);
 

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
 
 
CREATE TABLE `user` (
  `username` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  PRIMARY KEY (`username`)
);
 

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
 
 
CREATE TABLE `vote` (
  `post` varchar(15) NOT NULL,
  `user` varchar(15) NOT NULL,
  `timestamp` int(10) NOT NULL,
  `upvote` tinyint(1) NOT NULL,
  `downvote` tinyint(1) NOT NULL,
  PRIMARY KEY (`post`,`user`),
  UNIQUE KEY `post` (`post`,`user`)
);
 
 

 
 
 
 
 
 
 

-- Dump completed
