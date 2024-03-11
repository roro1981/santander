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
-- Table structure for table `bbs_api_log`
--

DROP TABLE IF EXISTS `bbs_api_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bbs_api_log` (
  `alg_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id log',
  `alg_external_id` int NOT NULL COMMENT 'Id de la orden en Flow',
  `alg_url` varchar(600) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL de API POST',
  `alg_request` text COLLATE utf8mb4_unicode_ci COMMENT 'Body enviado en API POST',
  `alg_response` text COLLATE utf8mb4_unicode_ci COMMENT 'Response de API POST',
  `alg_status_code` int DEFAULT NULL COMMENT 'HTTP Status Code de API POST',
  `alg_created_at` timestamp NOT NULL COMMENT 'Fecha creación',
  `alg_updated_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha modificacion',
  PRIMARY KEY (`alg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bbs_api_log`
--

LOCK TABLES `bbs_api_log` WRITE;
/*!40000 ALTER TABLE `bbs_api_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_api_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bbs_cart`
--

DROP TABLE IF EXISTS `bbs_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bbs_cart` (
  `car_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id carro registrado',
  `car_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Uuid carro registrado',
  `car_id_transaction` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador transaccion',
  `car_flow_currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Moneda del cobro',
  `car_flow_amount` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Monto total para pagar',
  `car_description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción del ítem del carro',
  `car_agreement` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codigo de convenio asociado al comercio',
  `car_url` varchar(600) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL para redireccionamiento autorización de pago',
  `car_expires_at` bigint unsigned NOT NULL COMMENT 'Tiempo expiracion codigo QR',
  `car_items_number` int NOT NULL COMMENT 'Cantidad de detalles a informar',
  `car_collector` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codigo identificador del recaudador',
  `car_status` enum('CREATED','REGISTERED-CART','AUTHORIZED','FAILED') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Estado del cobro',
  `car_url_return` varchar(600) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de retorno tras pago exitoso',
  `car_authorization_uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codigo autorización de orden entregada por el webhook',
  `car_sent_kafka` tinyint NOT NULL COMMENT 'Verifica si carro fue enviado a kafka',
  `car_fail_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codigo error webhook',
  `car_fail_motive` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Detalle error webhook',
  `car_flow_id` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador de flow',
  `car_flow_attempt_number` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Numero de intentos de pago',
  `car_flow_product_id` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Id del producto',
  `car_flow_email_paid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email usuario',
  `car_flow_subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Asunto de transaccion de pago',
  `car_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha creación',
  `car_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha modificación',
  PRIMARY KEY (`car_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bbs_cart`
--

LOCK TABLES `bbs_cart` WRITE;
/*!40000 ALTER TABLE `bbs_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bbs_cart_status`
--

DROP TABLE IF EXISTS `bbs_cart_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bbs_cart_status` (
  `cas_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id estado carro',
  `car_id` bigint unsigned NOT NULL,
  `cas_status` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Estado del cobro',
  `cas_created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha creación',
  PRIMARY KEY (`cas_id`),
  KEY `bbs_cart_status_car_id_foreign` (`car_id`),
  CONSTRAINT `bbs_cart_status_car_id_foreign` FOREIGN KEY (`car_id`) REFERENCES `bbs_cart` (`car_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bbs_cart_status`
--

LOCK TABLES `bbs_cart_status` WRITE;
/*!40000 ALTER TABLE `bbs_cart_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_cart_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bbs_conciliation`
--

DROP TABLE IF EXISTS `bbs_conciliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bbs_conciliation` (
  `con_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id rendicion',
  `con_cart_id` int NOT NULL COMMENT 'Identificador del carro',
  `con_agreement_id` int NOT NULL COMMENT 'Identificador del convenio',
  `con_product_number` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Id del producto pagado',
  `con_customer_number` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Id del Cliente que realizo el pago',
  `con_product_expiration` datetime NOT NULL COMMENT 'Fecha de Expiración del producto',
  `con_product_description` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descripción Producto',
  `con_product_amount` int NOT NULL COMMENT 'Monto del producto pagado',
  `con_operation_number` int NOT NULL COMMENT 'Numero de la operación',
  `con_operation_date` datetime NOT NULL COMMENT 'Fecha y hora de la transaccion',
  `con_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Status transaccion:OK, NO EXISTE, INCONSISTENCIA PAGO',
  `con_file_process` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Archivo de origen del registro',
  `con_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha creación',
  PRIMARY KEY (`con_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bbs_conciliation`
--

LOCK TABLES `bbs_conciliation` WRITE;
/*!40000 ALTER TABLE `bbs_conciliation` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_conciliation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bbs_idempotency`
--

DROP TABLE IF EXISTS `bbs_idempotency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bbs_idempotency` (
  `idp_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único',
  `idp_response` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Response API POST',
  `idp_httpcode` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'HTTP Status Code API POST',
  `idp_created_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha creación',
  `idp_updated_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha modificacion'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bbs_idempotency`
--

LOCK TABLES `bbs_idempotency` WRITE;
/*!40000 ALTER TABLE `bbs_idempotency` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_idempotency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bbs_parameter`
--

DROP TABLE IF EXISTS `bbs_parameter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bbs_parameter` (
  `par_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Titulo parametro',
  `par_value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Valor parametro',
  `par_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripcion parametro',
  `par_created_at` timestamp NOT NULL COMMENT 'Fecha creación parametro',
  `par_updated_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha modificacion parametro',
  UNIQUE KEY `bbs_parameter_par_code_unique` (`par_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bbs_parameter`
--

LOCK TABLES `bbs_parameter` WRITE;
/*!40000 ALTER TABLE `bbs_parameter` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbs_parameter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1),(2,'2024_01_10_151405_create_bbs_cart_table',1),(3,'2024_01_10_200536_create_bbs_cart_status_table',1),(4,'2024_01_10_202112_create_bbs_api_log_table',1),(5,'2024_01_10_203756_create_bbs_idempotency_table',1),(6,'2024_01_11_145443_create_bbs_conciliation_table',1),(7,'2024_01_26_041032_create_bbs_parameter_table',1),(8,'2024_02_20_152001_create_jobs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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

-- Dump completed on 2024-02-20 15:03:29
