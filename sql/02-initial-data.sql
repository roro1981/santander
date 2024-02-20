CREATE DATABASE  IF NOT EXISTS `flow` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `flow`;
-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: flow
-- ------------------------------------------------------
-- Server version	8.0.26

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `bbs_parameter`
--

LOCK TABLES `bbs_parameter` WRITE;
/*!40000 ALTER TABLE `bbs_parameter` DISABLE KEYS */;
INSERT INTO `bbs_parameter` VALUES ('CURRENCY','999','Moneda de pago','2024-02-20 18:48:13',NULL),('KAFKA_NOTIFICATION_TOPIC','first_topic','Topic para mensajes de notificación a Kafka','2024-02-20 18:48:13',NULL),('KAFKA_PAYMENT_TYPE','BC','Detalle de tipo de pago enviado al notificar a Flow Core','2024-02-20 18:48:13',NULL),('SANTANDER_TOKEN_COMPANY','768300143','Company para obtencion bearer token santander','2024-02-20 18:48:13',NULL),('SANTANDER_TOKEN_PASSWORD','Ax4o5idb_h','Password para obtencion bearer token santander','2024-02-20 18:48:13',NULL),('SANTANDER_TOKEN_URL','https://paymentbutton-bsan-cert.e-pagos.cl','URL para obtencion bearer token santander','2024-02-20 18:48:13',NULL),('SANTANDER_TOKEN_USERNAME','768300143','Username para obtencion bearer token santander','2024-02-20 18:48:13',NULL),('SFTP_HOST_SANTANDER','200.75.7.235','Host SFTP SANTANDER','2024-02-20 18:48:13',NULL),('SFTP_PASSWORD_SANTANDER','WXv+VC7G','Password SFTP SANTANDER','2024-02-20 18:48:13',NULL),('SFTP_USERNAME_SANTANDER','flowsa_bsan','Username SFTP SANTANDER','2024-02-20 18:48:13',NULL),('URL_RETORNO','https://tebi4tbxq0.execute-api.us-west-2.amazonaws.com/QA/santander/v1/redirect','Url de retorno','2024-02-20 18:48:13',NULL);
/*!40000 ALTER TABLE `bbs_parameter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'flow'
--

--
-- Dumping routines for database 'flow'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-02-20 15:48:18
