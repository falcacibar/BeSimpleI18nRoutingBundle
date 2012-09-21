-- @info tabla i18n_locales, información de localizacion
-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: loogares_new
-- ------------------------------------------------------
-- Server version	5.5.24-0ubuntu0.12.04.1

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
-- Table structure for table `i18n_locales`
--

DROP TABLE IF EXISTS `i18n_locales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `i18n_locales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locale` varchar(14) NOT NULL,
  `nombre` varchar(128) CHARACTER SET utf8 NOT NULL,
  `estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `i18n_locales`
--

LOCK TABLES `i18n_locales` WRITE;
/*!40000 ALTER TABLE `i18n_locales` DISABLE KEYS */;
INSERT INTO `i18n_locales` VALUES (1,'af_ZA','Afrikaans',0),(2,'ar_SA','العربية',0),(3,'be_BY','Беларускі',0),(4,'bg_BG','български',0),(5,'bn_BD','বাংলা',0),(6,'ca_ES','Catalan',0),(7,'cs_CZ','čeština',0),(8,'da_DK','Dansk',0),(9,'de_DE','Deutsch',0),(10,'el_GR','Greek',0),(11,'en_GB','English (United Kingdom)',0),(12,'en_US','English (United States)',1),(13,'es_AR','Español (Argentina)',1),(14,'es_ES','Español (España)',0),(15,'es_MX','Español (Mexico)',0),(16,'et_EE','Eesti',0),(17,'eu_ES','Euskara',0),(18,'fa_IR','فارس',0),(19,'fi_FI','Suomi',0),(20,'fo_FO','Føroyskt',0),(21,'fr_FR','Français',0),(22,'ga_IE','Gaeilge',0),(23,'he_IL','עברית',0),(24,'hr_HR','hr̀vātskī',0),(25,'hu_HU','Magyar',0),(26,'is_IS','Icelandic',0),(27,'it_IT','Italiano',0),(28,'ja_JP','日本語',0),(29,'ko_KR','한국어',0),(30,'lt_LT','Lietuvių',0),(31,'lv_LV','Latviešu',0),(32,'ms_MY','Bahasa Melayu',0),(33,'mk_MK','Македонски јазик',0),(34,'nl_NL','Nederlands',0),(35,'no_NO','Norsk bokmål',0),(36,'pl_PL','Polski',0),(37,'pt_BR','Português do Brasil',0),(38,'pt_PT','Português ibérico',0),(39,'ro_RO','Română',0),(40,'ru_RU','Русский',0),(41,'sk_SK','Slovenčina',0),(42,'sl_SI','Slovenščina',0),(43,'sr_CS','Srpski',0),(44,'sv_SE','Svenska',0),(45,'tn_ZA','Setswana',0),(46,'tr_TR','Türkçe',0),(47,'uk_UA','українська',0),(48,'vi_VN','Tiếng Việt',0),(49,'zh_CN','简体中文',0),(50,'zh_TW','繁體中文',0),(51,'es_CL','Español (Chile)',1),(52,'en','English',0);
/*!40000 ALTER TABLE `i18n_locales` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-09-21 16:24:56
