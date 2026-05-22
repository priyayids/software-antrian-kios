/*
 Navicat Premium Data Transfer

 Source Server         : Mysql
 Source Server Type    : MySQL
 Source Server Version : 50739 (5.7.39)
 Source Host           : localhost:3306
 Source Schema         : aplikasi_antrian

 Target Server Type    : MySQL
 Target Server Version : 50739 (5.7.39)
 File Encoding         : 65001

 Date: 20/02/2024 00:05:45
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for queue_antrian_admisi
-- ----------------------------
DROP TABLE IF EXISTS `queue_antrian_admisi`;
CREATE TABLE `queue_antrian_admisi` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `no_antrian` varchar(3) NOT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '0',
  `updated_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for queue_penggilan_antrian
-- ----------------------------
DROP TABLE IF EXISTS `queue_penggilan_antrian`;
CREATE TABLE `queue_penggilan_antrian` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `antrian` varchar(255) DEFAULT NULL,
  `loket` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_antrian` (`antrian`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of queue_penggilan_antrian
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for queue_setting
-- ----------------------------
DROP TABLE IF EXISTS `queue_setting`;
CREATE TABLE `queue_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_instansi` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `telpon` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `running_text` varchar(255) DEFAULT NULL,
  `youtube_id` varchar(255) DEFAULT NULL,
  `list_loket` longtext,
  `warna_primary` varchar(255) DEFAULT NULL,
  `warna_secondary` varchar(255) DEFAULT NULL,
  `warna_accent` varchar(255) DEFAULT NULL,
  `warna_background` varchar(255) DEFAULT NULL,
  `warna_text` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of queue_setting
-- ----------------------------
BEGIN;
-- INSERT INTO `queue_setting` (`id`, `nama_instansi`, `logo`, `alamat`, `telpon`, `email`, `running_text`, `youtube_id`, `list_loket`, `warna_primary`, `warna_secondary`, `warna_accent`, `warna_background`, `warna_text`) VALUES (1, 'RSU SRIWIJAYA', 'logo-removebg-preview.png', 'Lorhbh', '558450845', 'ade2mail.com', 'SELAMAT DATANG DI RSU SRIWIJAYA', 'Dfzmsb_57XM', '[{\"no_loket\":\"1\",\"nama_loket\":\"Loket 1\"},{\"no_loket\":\"2\",\"nama_loket\":\"Loket 2\"},{\"no_loket\":\"3\",\"nama_loket\":\"Loket 3\"}]', '#00923f', '#c39292', '#6083a9', '#3a9862', '#ffffff');
INSERT INTO `queue_setting` (`id`, `nama_instansi`, `logo`, `alamat`, `telpon`, `email`, `running_text`, `youtube_id`, `list_loket`, `warna_primary`, `warna_secondary`, `warna_accent`, `warna_background`, `warna_text`) VALUES (1, 'PT NISCAYA UNGGUL NUSANTARA', 'NISCAYA LOGO.png', 'Rukan graha mas Jl. Pejuangan No.C 11, RT.1/RW.7, Kebon Jeruk, Kebonjeruk, West Jakarta City, Jakarta 11520', '558450845', 'priyayi@cubeteknologi.com', 'SELAMAT DATANG', 'Srr5BCta8UY', '[{"no_loket":"1","nama_loket":"LOKET 1"},{"no_loket":"2","nama_loket":"LOKET 2"},{"no_loket":"3","nama_loket":"LOKET 3"}]', '#020202', '#ffffff', '#6083a9', '#ffffff', '#ffffff');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
