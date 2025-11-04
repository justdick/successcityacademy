-- MySQL dump 10.13  Distrib 8.4.0, for Win64 (x86_64)
--
-- Host: localhost    Database: student_management
-- ------------------------------------------------------
-- Server version	8.4.0

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
-- Table structure for table `access_logs`
--

DROP TABLE IF EXISTS `access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `resource_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_denied` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_resource_type` (`resource_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_logs`
--

LOCK TABLES `access_logs` WRITE;
/*!40000 ALTER TABLE `access_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_levels`
--

DROP TABLE IF EXISTS `class_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_levels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_levels`
--

LOCK TABLES `class_levels` WRITE;
/*!40000 ALTER TABLE `class_levels` DISABLE KEYS */;
INSERT INTO `class_levels` VALUES (1,'STEM 1A','2025-11-03 14:42:05'),(2,'STEM 1B','2025-11-03 14:42:05'),(3,'STEM 2A','2025-11-03 14:42:05'),(4,'STEM 2B','2025-11-03 14:42:05'),(5,'Business 1A','2025-11-03 14:42:05'),(6,'Business 1B','2025-11-03 14:42:05'),(7,'Business 2A','2025-11-03 14:42:05'),(8,'Business 2B','2025-11-03 14:42:05'),(9,'Humanities 1A','2025-11-03 14:42:05'),(10,'Humanities 1B','2025-11-03 14:42:05'),(11,'Humanities 2A','2025-11-03 14:42:05'),(12,'Humanities 2B','2025-11-03 14:42:05'),(13,'General Arts 1A','2025-11-03 14:42:05'),(14,'General Arts 1B','2025-11-03 14:42:05'),(15,'General Arts 2A','2025-11-03 14:42:05'),(16,'General Arts 2B','2025-11-03 14:42:05'),(17,'ICT 1A','2025-11-03 14:42:05'),(18,'ICT 1B','2025-11-03 14:42:05'),(19,'ICT 2A','2025-11-03 14:42:05'),(20,'ICT 2B','2025-11-03 14:42:05'),(21,'Home Economics 1A','2025-11-03 14:42:05'),(22,'Home Economics 2A','2025-11-03 14:42:05'),(23,'Arts & Design 1A','2025-11-03 14:42:05'),(24,'Arts & Design 2A','2025-11-03 14:42:05');
/*!40000 ALTER TABLE `class_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_level_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_class_level` (`class_level_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class_level_id`) REFERENCES `class_levels` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'2024-STEM-001','Juan Dela Cruz',1,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(2,'2024-STEM-002','Maria Santos',1,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(3,'2024-STEM-003','Pedro Reyes',1,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(4,'2024-STEM-004','Ana Garcia',1,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(5,'2024-STEM-005','Jose Ramos',1,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(6,'2024-BUS-001','Sofia Martinez',5,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(7,'2024-BUS-002','Miguel Torres',5,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(8,'2024-BUS-003','Isabella Cruz',5,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(9,'2024-BUS-004','Carlos Mendoza',5,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(10,'2024-BUS-005','Lucia Fernandez',5,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(11,'2024-HUM-001','Diego Lopez',9,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(12,'2024-HUM-002','Valentina Gomez',9,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(13,'2024-HUM-003','Mateo Diaz',9,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(14,'2024-HUM-004','Camila Morales',9,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(15,'2024-HUM-005','Santiago Herrera',9,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(16,'2024-ICT-001','Gabriel Silva',17,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(17,'2024-ICT-002','Emma Castillo',17,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(18,'2024-ICT-003','Lucas Vargas',17,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(19,'2024-ICT-004','Mia Ortiz',17,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(20,'2024-ICT-005','Daniel Navarro',17,'2025-11-03 14:42:05','2025-11-03 14:42:05');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subject_weightings`
--

DROP TABLE IF EXISTS `subject_weightings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subject_weightings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject_id` int NOT NULL,
  `ca_percentage` decimal(5,2) NOT NULL DEFAULT '40.00',
  `exam_percentage` decimal(5,2) NOT NULL DEFAULT '60.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_subject_weighting` (`subject_id`),
  KEY `idx_subject_id` (`subject_id`),
  CONSTRAINT `subject_weightings_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_ca_percentage` CHECK (((`ca_percentage` >= 0) and (`ca_percentage` <= 100))),
  CONSTRAINT `chk_exam_percentage` CHECK (((`exam_percentage` >= 0) and (`exam_percentage` <= 100))),
  CONSTRAINT `chk_percentage_sum` CHECK (((`ca_percentage` + `exam_percentage`) = 100.00))
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subject_weightings`
--

LOCK TABLES `subject_weightings` WRITE;
/*!40000 ALTER TABLE `subject_weightings` DISABLE KEYS */;
INSERT INTO `subject_weightings` VALUES (1,41,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(2,29,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(3,48,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(4,16,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(5,43,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(6,30,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(7,27,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(8,25,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(9,26,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(10,24,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(11,35,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(12,38,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(13,40,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(14,13,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(15,47,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(16,44,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(17,49,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(18,32,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(19,31,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(20,36,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(21,7,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(22,50,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(23,45,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(24,23,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(25,17,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(26,18,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(27,21,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(28,22,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(29,5,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(30,19,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(31,20,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(32,46,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(33,11,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(34,3,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(35,14,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(36,1,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(37,28,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(38,4,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(39,9,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(40,34,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(41,12,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(42,8,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(43,15,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(44,2,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(45,6,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(46,42,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(47,37,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(48,10,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(49,39,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(50,33,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05');
/*!40000 ALTER TABLE `subject_weightings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'Oral Communication','2025-11-03 14:42:05'),(2,'Reading and Writing','2025-11-03 14:42:05'),(3,'Komunikasyon at Pananaliksik','2025-11-03 14:42:05'),(4,'Pagbasa at Pagsusuri','2025-11-03 14:42:05'),(5,'General Mathematics','2025-11-03 14:42:05'),(6,'Statistics and Probability','2025-11-03 14:42:05'),(7,'Earth and Life Science','2025-11-03 14:42:05'),(8,'Physical Science','2025-11-03 14:42:05'),(9,'Personal Development','2025-11-03 14:42:05'),(10,'Understanding Culture, Society and Politics','2025-11-03 14:42:05'),(11,'Introduction to Philosophy','2025-11-03 14:42:05'),(12,'Physical Education and Health','2025-11-03 14:42:05'),(13,'Contemporary Philippine Arts','2025-11-03 14:42:05'),(14,'Media and Information Literacy','2025-11-03 14:42:05'),(15,'Pre-Calculus','2025-11-03 14:42:05'),(16,'Basic Calculus','2025-11-03 14:42:05'),(17,'General Biology 1','2025-11-03 14:42:05'),(18,'General Biology 2','2025-11-03 14:42:05'),(19,'General Physics 1','2025-11-03 14:42:05'),(20,'General Physics 2','2025-11-03 14:42:05'),(21,'General Chemistry 1','2025-11-03 14:42:05'),(22,'General Chemistry 2','2025-11-03 14:42:05'),(23,'Fundamentals of Accountancy','2025-11-03 14:42:05'),(24,'Business Math','2025-11-03 14:42:05'),(25,'Business Finance','2025-11-03 14:42:05'),(26,'Business Marketing','2025-11-03 14:42:05'),(27,'Business Ethics','2025-11-03 14:42:05'),(28,'Organization and Management','2025-11-03 14:42:05'),(29,'Applied Economics','2025-11-03 14:42:05'),(30,'Business Enterprise Simulation','2025-11-03 14:42:05'),(31,'Creative Writing','2025-11-03 14:42:05'),(32,'Creative Nonfiction','2025-11-03 14:42:05'),(33,'World Religions and Belief Systems','2025-11-03 14:42:05'),(34,'Philippine Politics and Governance','2025-11-03 14:42:05'),(35,'Community Engagement','2025-11-03 14:42:05'),(36,'Disciplines and Ideas in Social Sciences','2025-11-03 14:42:05'),(37,'Trends, Networks and Critical Thinking','2025-11-03 14:42:05'),(38,'Computer Programming','2025-11-03 14:42:05'),(39,'Web Development','2025-11-03 14:42:05'),(40,'Computer Systems Servicing','2025-11-03 14:42:05'),(41,'Animation','2025-11-03 14:42:05'),(42,'Technical Drafting','2025-11-03 14:42:05'),(43,'Bread and Pastry Production','2025-11-03 14:42:05'),(44,'Cookery','2025-11-03 14:42:05'),(45,'Food and Beverage Services','2025-11-03 14:42:05'),(46,'Housekeeping','2025-11-03 14:42:05'),(47,'Contemporary Philippine Arts from the Regions','2025-11-03 14:42:05'),(48,'Art Appreciation','2025-11-03 14:42:05'),(49,'Creative Industries','2025-11-03 14:42:05'),(50,'Exhibit Design','2025-11-03 14:42:05');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_class_assignments`
--

DROP TABLE IF EXISTS `teacher_class_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_class_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `class_level_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_teacher_class` (`user_id`,`class_level_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_class_level_id` (`class_level_id`),
  CONSTRAINT `teacher_class_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_assignments_ibfk_2` FOREIGN KEY (`class_level_id`) REFERENCES `class_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_class_assignments`
--

LOCK TABLES `teacher_class_assignments` WRITE;
/*!40000 ALTER TABLE `teacher_class_assignments` DISABLE KEYS */;
INSERT INTO `teacher_class_assignments` VALUES (1,2,1,'2025-11-03 14:42:05'),(2,2,2,'2025-11-03 14:42:05'),(3,3,5,'2025-11-03 14:42:05'),(4,3,6,'2025-11-03 14:42:05'),(5,4,17,'2025-11-03 14:42:05'),(6,4,18,'2025-11-03 14:42:05');
/*!40000 ALTER TABLE `teacher_class_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_subject_assignments`
--

DROP TABLE IF EXISTS `teacher_subject_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_subject_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_teacher_subject` (`user_id`,`subject_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_subject_id` (`subject_id`),
  CONSTRAINT `teacher_subject_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_assignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_subject_assignments`
--

LOCK TABLES `teacher_subject_assignments` WRITE;
/*!40000 ALTER TABLE `teacher_subject_assignments` DISABLE KEYS */;
INSERT INTO `teacher_subject_assignments` VALUES (1,2,15,'2025-11-03 14:42:05'),(2,2,16,'2025-11-03 14:42:05'),(3,2,5,'2025-11-03 14:42:05'),(4,3,23,'2025-11-03 14:42:05'),(5,3,24,'2025-11-03 14:42:05'),(6,3,25,'2025-11-03 14:42:05'),(7,4,38,'2025-11-03 14:42:05'),(8,4,39,'2025-11-03 14:42:05'),(9,4,40,'2025-11-03 14:42:05');
/*!40000 ALTER TABLE `teacher_subject_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `term_assessments`
--

DROP TABLE IF EXISTS `term_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `term_assessments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` int NOT NULL,
  `term_id` int NOT NULL,
  `ca_mark` decimal(5,2) DEFAULT NULL,
  `exam_mark` decimal(5,2) DEFAULT NULL,
  `final_mark` decimal(5,2) GENERATED ALWAYS AS ((coalesce(`ca_mark`,0) + coalesce(`exam_mark`,0))) STORED,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assessment` (`student_id`,`subject_id`,`term_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_term_id` (`term_id`),
  KEY `idx_student_term` (`student_id`,`term_id`),
  KEY `idx_term_subject` (`term_id`,`subject_id`),
  CONSTRAINT `term_assessments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `term_assessments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `term_assessments_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `term_assessments`
--

LOCK TABLES `term_assessments` WRITE;
/*!40000 ALTER TABLE `term_assessments` DISABLE KEYS */;
INSERT INTO `term_assessments` (`id`, `student_id`, `subject_id`, `term_id`, `ca_mark`, `exam_mark`, `created_at`, `updated_at`) VALUES (1,'2024-STEM-001',15,1,35.50,55.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(2,'2024-STEM-001',16,1,34.00,52.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(3,'2024-STEM-001',5,1,37.00,56.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(4,'2024-STEM-002',15,1,38.00,58.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(5,'2024-STEM-002',16,1,39.50,59.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(6,'2024-STEM-002',5,1,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(7,'2024-STEM-003',15,1,32.00,50.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(8,'2024-STEM-003',16,1,30.50,48.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(9,'2024-STEM-003',5,1,33.00,51.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(10,'2024-STEM-004',15,1,36.50,54.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(11,'2024-STEM-004',16,1,37.00,55.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(12,'2024-STEM-004',5,1,38.50,57.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(13,'2024-STEM-005',15,1,33.00,51.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(14,'2024-STEM-005',16,1,32.50,50.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(15,'2024-STEM-005',5,1,34.50,53.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(16,'2024-BUS-001',23,1,36.00,54.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(17,'2024-BUS-001',24,1,37.50,56.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(18,'2024-BUS-001',25,1,35.00,53.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(19,'2024-BUS-002',23,1,34.50,52.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(20,'2024-BUS-002',24,1,36.00,54.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(21,'2024-BUS-002',25,1,33.50,51.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(22,'2024-BUS-003',23,1,38.50,58.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(23,'2024-BUS-003',24,1,39.00,59.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(24,'2024-BUS-003',25,1,37.50,57.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(25,'2024-BUS-004',23,1,31.00,49.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(26,'2024-BUS-004',24,1,32.50,50.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(27,'2024-BUS-004',25,1,30.00,48.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(28,'2024-BUS-005',23,1,35.50,54.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(29,'2024-BUS-005',24,1,36.50,55.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(30,'2024-BUS-005',25,1,34.00,52.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(31,'2024-HUM-001',31,1,36.00,55.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(32,'2024-HUM-001',33,1,37.50,56.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(33,'2024-HUM-002',31,1,38.50,58.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(34,'2024-HUM-002',33,1,39.00,59.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(35,'2024-HUM-003',31,1,33.00,51.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(36,'2024-HUM-003',33,1,34.50,52.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(37,'2024-HUM-004',31,1,37.00,56.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(38,'2024-HUM-004',33,1,38.00,57.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(39,'2024-HUM-005',31,1,35.00,53.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(40,'2024-HUM-005',33,1,36.00,54.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(41,'2024-ICT-001',38,1,37.00,56.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(42,'2024-ICT-001',39,1,38.50,58.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(43,'2024-ICT-001',40,1,36.00,55.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(44,'2024-ICT-002',38,1,39.00,59.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(45,'2024-ICT-002',39,1,40.00,60.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(46,'2024-ICT-002',40,1,38.50,58.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(47,'2024-ICT-003',38,1,34.00,52.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(48,'2024-ICT-003',39,1,35.50,54.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(49,'2024-ICT-003',40,1,33.50,51.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(50,'2024-ICT-004',38,1,36.50,55.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(51,'2024-ICT-004',39,1,37.50,57.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(52,'2024-ICT-004',40,1,35.50,54.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(53,'2024-ICT-005',38,1,35.00,53.50,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(54,'2024-ICT-005',39,1,36.00,55.00,'2025-11-03 14:42:05','2025-11-03 14:42:05'),(55,'2024-ICT-005',40,1,34.50,52.50,'2025-11-03 14:42:05','2025-11-03 14:42:05');
/*!40000 ALTER TABLE `term_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terms`
--

DROP TABLE IF EXISTS `terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_term` (`name`,`academic_year`),
  KEY `idx_academic_year` (`academic_year`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terms`
--

LOCK TABLES `terms` WRITE;
/*!40000 ALTER TABLE `terms` DISABLE KEYS */;
INSERT INTO `terms` VALUES (1,'Term 1','2024/2025','2024-09-01','2024-12-15',1,'2025-11-03 14:42:05'),(2,'Term 2','2024/2025','2025-01-06','2025-04-10',0,'2025-11-03 14:42:05'),(3,'Term 3','2024/2025','2025-04-21','2025-07-25',0,'2025-11-03 14:42:05');
/*!40000 ALTER TABLE `terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_username` (`username`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$qrDD0NiqHFMXy5F52H1QW.SuYoe9joIWtvjx7PMxdegQgOWv2NLQq','admin','System Administrator','2025-11-03 14:42:04','2025-11-03 14:42:04'),(2,'teacher1','$2y$10$f3d0ww0T4/u8h/6U0qJuI.VfpwsNkuaaR4Xjz1Ln.fFCZ/X5IIVAO','user','Maria Santos','2025-11-03 14:42:05','2025-11-03 14:42:05'),(3,'teacher2','$2y$10$f3d0ww0T4/u8h/6U0qJuI.VfpwsNkuaaR4Xjz1Ln.fFCZ/X5IIVAO','user','Juan Dela Cruz','2025-11-03 14:42:05','2025-11-03 14:42:05'),(4,'teacher3','$2y$10$f3d0ww0T4/u8h/6U0qJuI.VfpwsNkuaaR4Xjz1Ln.fFCZ/X5IIVAO','user','Ana Reyes','2025-11-03 14:42:05','2025-11-03 14:42:05');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'student_management'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-04 13:28:31
