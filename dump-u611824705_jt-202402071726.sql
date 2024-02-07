-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: u611824705_jt
-- ------------------------------------------------------
-- Server version	5.7.33

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cat_contact`
--

DROP TABLE IF EXISTS `cat_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_contact` (
  `id_contact` int(11) NOT NULL AUTO_INCREMENT,
  `id_location` int(11) NOT NULL,
  `phone` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  `contact_name` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `id_contact_type` int(11) DEFAULT NULL,
  `id_contact_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_contact`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_contact`
--

LOCK TABLES `cat_contact` WRITE;
/*!40000 ALTER TABLE `cat_contact` DISABLE KEYS */;
INSERT INTO `cat_contact` VALUES (1,1,'7772314822','Isidoro Cornelio',1,1),(2,1,'7341326995','Cirilo Aranda',1,1),(3,1,'7341346283','Karen Elizabeth Aranda',2,1),(4,1,'7341109763','Josue Aranda',2,1),(5,1,'7341008654','Jessica Aranda',2,1),(6,1,'7775389909','Susana Marcelo',2,1),(7,1,'7775248198','Brenda Cornelio',2,1),(8,1,'7776051993','Raul Trejo',2,1);
/*!40000 ALTER TABLE `cat_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cat_contact_type`
--

DROP TABLE IF EXISTS `cat_contact_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_contact_type` (
  `id_contact_type` int(11) NOT NULL AUTO_INCREMENT,
  `contact_type` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_contact_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_contact_type`
--

LOCK TABLES `cat_contact_type` WRITE;
/*!40000 ALTER TABLE `cat_contact_type` DISABLE KEYS */;
INSERT INTO `cat_contact_type` VALUES (1,'Sms'),(2,'WhatsApp'),(3,'Casa');
/*!40000 ALTER TABLE `cat_contact_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cat_location`
--

DROP TABLE IF EXISTS `cat_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_location` (
  `id_location` int(11) NOT NULL AUTO_INCREMENT,
  `location_desc` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_location`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_location`
--

LOCK TABLES `cat_location` WRITE;
/*!40000 ALTER TABLE `cat_location` DISABLE KEYS */;
INSERT INTO `cat_location` VALUES (1,'Tlaquiltenago'),(2,'Zacatepec'),(3,'Otro');
/*!40000 ALTER TABLE `cat_location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cat_message`
--

DROP TABLE IF EXISTS `cat_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_message` (
  `id_message` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_message`
--

LOCK TABLES `cat_message` WRITE;
/*!40000 ALTER TABLE `cat_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `cat_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cat_status`
--

DROP TABLE IF EXISTS `cat_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_status` (
  `id_status` int(11) NOT NULL AUTO_INCREMENT,
  `status_desc` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_status`
--

LOCK TABLES `cat_status` WRITE;
/*!40000 ALTER TABLE `cat_status` DISABLE KEYS */;
INSERT INTO `cat_status` VALUES (1,'Nuevo'),(2,'En Proceso (SMS)'),(3,'Entregado'),(4,'Devuelto'),(5,'Deleted');
/*!40000 ALTER TABLE `cat_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `folio`
--

DROP TABLE IF EXISTS `folio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `folio` (
  `id_folio` int(11) NOT NULL AUTO_INCREMENT,
  `folio` int(11) DEFAULT NULL,
  `id_location` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_folio`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `folio`
--

LOCK TABLES `folio` WRITE;
/*!40000 ALTER TABLE `folio` DISABLE KEYS */;
INSERT INTO `folio` VALUES (1,534,1),(2,504,2);
/*!40000 ALTER TABLE `folio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL AUTO_INCREMENT,
  `n_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de la notificacion',
  `n_user_id` int(11) DEFAULT NULL COMMENT 'Id del usuario que envia la notificacion',
  `id_package` int(11) DEFAULT NULL,
  `id_message` int(11) DEFAULT NULL,
  `code` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_notification`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `package`
--

DROP TABLE IF EXISTS `package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package` (
  `id_package` int(11) NOT NULL AUTO_INCREMENT,
  `id_location` int(11) NOT NULL,
  `c_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion',
  `c_user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Id del usuario que crea',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `receiver` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `tracking` varchar(100) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `folio` int(11) DEFAULT NULL,
  `d_validity` int(11) DEFAULT '3' COMMENT 'dias de vigencia',
  `d_date` datetime DEFAULT NULL COMMENT 'Fecha de entrega',
  `d_user_id` int(11) DEFAULT NULL COMMENT 'Id del usuario que entrega',
  `id_status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_package`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package`
--

LOCK TABLES `package` WRITE;
/*!40000 ALTER TABLE `package` DISABLE KEYS */;
INSERT INTO `package` VALUES (1,1,'2024-02-05 10:30:05',3,'7772314822','Isidoro Cornelio','JMX000708989409',1,3,NULL,NULL,1),(2,1,'2024-02-05 10:30:05',3,'7341326995','Cirilo Aranda','JMX000721188725',2,3,NULL,NULL,1),(3,1,'2024-02-05 10:30:05',3,'7341346283','Karen Elizabeth Aranda','JMX000752857321',3,3,NULL,NULL,1),(4,1,'2024-02-05 10:30:05',3,'7341109763','Josue Aranda','JMX450016105590',4,3,NULL,NULL,1),(5,1,'2024-02-05 10:30:05',3,'7341008654','Jessica Aranda','JMX000719718472',5,3,NULL,NULL,1),(6,1,'2024-02-05 10:30:05',3,'7775389909','Susana Marcelo','JMX000734787126',6,3,NULL,NULL,1),(7,1,'2024-02-05 10:30:05',3,'7775248198','Brenda Cornelio','JMX000745806595',7,3,NULL,NULL,1),(8,1,'2024-02-05 10:30:05',3,'7776051993','Raul Trejo','JMX000717322027',8,3,NULL,NULL,1),(9,1,'2024-02-05 10:30:05',3,'7772314822','Isidoro Cornelio','JMX000713353907',9,3,NULL,NULL,1),(10,1,'2024-02-05 10:30:05',3,'7341326995','Cirilo Aranda','JMX000711228681',10,3,NULL,NULL,1),(11,1,'2024-02-05 10:30:05',3,'7341346283','Karen Elizabeth Aranda','JMX000722889968',11,3,NULL,NULL,1),(12,1,'2024-02-05 10:30:05',3,'7341109763','Josue Aranda','JMX450092888634',12,3,NULL,NULL,1),(13,1,'2024-02-05 10:30:05',3,'7341008654','Jessica Aranda','JMX450042224197',13,3,NULL,NULL,1),(14,1,'2024-02-05 10:30:05',3,'7775389909','Susana Marcelo','JMX450086073890',14,3,NULL,NULL,1),(15,1,'2024-02-05 10:30:05',3,'7775248198','Brenda Cornelio','JMX000707821802',15,3,NULL,NULL,1),(16,1,'2024-02-05 10:30:05',3,'7776051993','Raul Trejo','JMX000705274195',16,3,NULL,NULL,1),(17,1,'2024-02-05 10:30:05',3,'7772314822','Isidoro Cornelio','JMX000689254611',17,3,NULL,NULL,1),(18,1,'2024-02-05 10:30:05',3,'7341326995','Cirilo Aranda','JMX000712637782',18,3,NULL,NULL,1),(19,1,'2024-02-05 10:30:05',3,'7341346283','Karen Elizabeth Aranda','JMX000701411129',19,3,NULL,NULL,1),(20,1,'2024-02-05 10:30:05',3,'7341109763','Josue Aranda','JMX000710160013',20,3,NULL,NULL,1),(21,1,'2024-02-05 10:30:05',3,'7341008654','Jessica Aranda','JMX000707711281',21,3,NULL,NULL,1),(22,1,'2024-02-05 10:30:05',3,'7775389909','Susana Marcelo','JMX000690283242',22,3,NULL,NULL,1),(23,1,'2024-02-05 10:30:05',3,'7775248198','Brenda Cornelio','JMX450006222967',23,3,NULL,NULL,1),(24,1,'2024-02-05 10:30:05',3,'7776051993','Raul Trejo','JMX000696560297',24,3,NULL,NULL,1),(25,1,'2024-02-05 10:30:05',3,'7772314822','Isidoro Cornelio','JMX000708016925',25,3,NULL,NULL,1),(26,1,'2024-02-05 10:30:05',3,'7341326995','Cirilo Aranda','JMX000707181755',26,3,NULL,NULL,1),(27,1,'2024-02-05 10:30:05',3,'7341109763','Josue Aranda','JMX000701742285',27,3,NULL,NULL,1),(28,1,'2024-02-05 10:30:05',3,'7772314822','Isidoro Cornelio','JMX000685435860',28,3,NULL,NULL,1),(29,1,'2024-02-05 10:30:05',3,'7341326995','Cirilo Aranda','JMX000720107991',29,3,NULL,NULL,1),(30,1,'2024-02-05 10:30:05',3,'7341109763','Josue Aranda','JMX000705999508',30,3,NULL,NULL,1),(31,1,'2024-02-05 10:30:05',3,'7772314822','Isidoro Cornelio','JMX000708839370',31,3,NULL,NULL,1),(32,1,'2024-02-05 10:30:05',3,'7772314822','Isidoro Cornelio','JMX000710124909',32,3,NULL,NULL,1),(33,1,'2024-02-05 10:30:05',3,'7772314822','Isidoro Cornelio','JMX000715701805',33,3,NULL,NULL,1),(34,1,'2024-02-05 10:30:05',3,'7341346283','Karen Elizabeth Aranda','JMX000749306713',34,3,NULL,NULL,1),(35,1,'2024-02-05 10:30:05',3,'7341346283','Karen Elizabeth Aranda','JMX000738505050',35,3,NULL,NULL,1);
/*!40000 ALTER TABLE `package` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(25) COLLATE utf8_spanish_ci NOT NULL,
  `password` text COLLATE utf8_spanish_ci NOT NULL,
  `id_location_default` int(11) DEFAULT '1',
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin',1,1),(2,'user','user',1,1),(3,'clio','f3cd74a2103e7ba99a6d8c14d0672102',1,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'u611824705_jt'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-02-07 17:26:07
