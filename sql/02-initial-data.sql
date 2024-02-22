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
-- Dumping data for table `bbs_api_log`
--

LOCK TABLES `bbs_api_log` WRITE;
/*!40000 ALTER TABLE `bbs_api_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_api_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bbs_cart`
--

LOCK TABLES `bbs_cart` WRITE;
/*!40000 ALTER TABLE `bbs_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bbs_cart_status`
--

LOCK TABLES `bbs_cart_status` WRITE;
/*!40000 ALTER TABLE `bbs_cart_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_cart_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bbs_conciliation`
--

LOCK TABLES `bbs_conciliation` WRITE;
/*!40000 ALTER TABLE `bbs_conciliation` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_conciliation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bbs_idempotency`
--

LOCK TABLES `bbs_idempotency` WRITE;
/*!40000 ALTER TABLE `bbs_idempotency` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_idempotency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bbs_parameter`
--

LOCK TABLES `bbs_parameter` WRITE;
/*!40000 ALTER TABLE `bbs_parameter` DISABLE KEYS */;
INSERT INTO `bbs_parameter` VALUES ('CURRENCY','999','Moneda de pago','2024-02-20 18:48:13',NULL),('KAFKA_NOTIFICATION_TOPIC','first_topic','Topic para mensajes de notificación a Kafka','2024-02-20 18:48:13',NULL),('KAFKA_PAYMENT_TYPE','BC','Detalle de tipo de pago enviado al notificar a Flow Core','2024-02-20 18:48:13',NULL),('SANTANDER_TOKEN_COMPANY','768300143','Company para obtencion bearer token santander','2024-02-20 18:48:13',NULL),('SANTANDER_TOKEN_PASSWORD','Ax4o5idb_h','Password para obtencion bearer token santander','2024-02-20 18:48:13',NULL),('SANTANDER_TOKEN_URL','https://paymentbutton-bsan-cert.e-pagos.cl','URL para obtencion bearer token santander','2024-02-20 18:48:13',NULL),('SANTANDER_TOKEN_USERNAME','768300143','Username para obtencion bearer token santander','2024-02-20 18:48:13',NULL),('SFTP_HOST_SANTANDER','200.75.7.235','Host SFTP SANTANDER','2024-02-20 18:48:13',NULL),('SFTP_PASSWORD_SANTANDER','WXv+VC7G','Password SFTP SANTANDER','2024-02-20 18:48:13',NULL),('SFTP_USERNAME_SANTANDER','flowsa_bsan','Username SFTP SANTANDER','2024-02-20 18:48:13',NULL),('URL_RETORNO','https://tebi4tbxq0.execute-api.us-west-2.amazonaws.com/QA/santander/v1/redirect','Url de retorno','2024-02-20 18:48:13',NULL);
/*!40000 ALTER TABLE `bbs_parameter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1),(2,'2024_01_10_151405_create_bbs_cart_table',1),(3,'2024_01_10_200536_create_bbs_cart_status_table',1),(4,'2024_01_10_202112_create_bbs_api_log_table',1),(5,'2024_01_10_203756_create_bbs_idempotency_table',1),(6,'2024_01_11_145443_create_bbs_conciliation_table',1),(7,'2024_01_26_041032_create_bbs_parameter_table',1),(8,'2024_02_20_152001_create_jobs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
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

-- Dump completed on 2024-02-20 16:33:17
