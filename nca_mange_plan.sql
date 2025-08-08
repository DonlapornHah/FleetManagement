/*
 Navicat Premium Data Transfer

 Source Server         : nca_manage
 Source Server Type    : MySQL
 Source Server Version : 80017 (8.0.17)
 Source Host           : localhost:3306
 Source Schema         : nca_mange_plan

 Target Server Type    : MySQL
 Target Server Version : 80017 (8.0.17)
 File Encoding         : 65001

 Date: 08/08/2025 18:03:22
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for break_point
-- ----------------------------
DROP TABLE IF EXISTS `break_point`;
CREATE TABLE `break_point`  (
  `brkp_id` int(11) NOT NULL AUTO_INCREMENT,
  `brkp_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`brkp_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 31 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of break_point
-- ----------------------------
INSERT INTO `break_point` VALUES (1, 'จุดรับส่ง 1');
INSERT INTO `break_point` VALUES (2, 'จุดรับส่ง 2');
INSERT INTO `break_point` VALUES (3, 'จุดรับส่ง 3');
INSERT INTO `break_point` VALUES (4, 'จุดรับส่ง 4');
INSERT INTO `break_point` VALUES (5, 'จุดรับส่ง 5');
INSERT INTO `break_point` VALUES (6, 'จุดรับส่ง 6');
INSERT INTO `break_point` VALUES (7, 'จุดรับส่ง 7');
INSERT INTO `break_point` VALUES (8, 'จุดรับส่ง 8');
INSERT INTO `break_point` VALUES (9, 'จุดรับส่ง 9');
INSERT INTO `break_point` VALUES (10, 'จุดรับส่ง 10');
INSERT INTO `break_point` VALUES (11, 'จุดรับส่ง 11');
INSERT INTO `break_point` VALUES (12, 'จุดรับส่ง 12');
INSERT INTO `break_point` VALUES (13, 'จุดรับส่ง 13');
INSERT INTO `break_point` VALUES (14, 'จุดรับส่ง 14');
INSERT INTO `break_point` VALUES (15, 'จุดรับส่ง 15');
INSERT INTO `break_point` VALUES (16, 'จุดรับส่ง 16');
INSERT INTO `break_point` VALUES (17, 'จุดรับส่ง 17');
INSERT INTO `break_point` VALUES (18, 'จุดรับส่ง 18');
INSERT INTO `break_point` VALUES (19, 'จุดรับส่ง 19');
INSERT INTO `break_point` VALUES (20, 'จุดรับส่ง 20');
INSERT INTO `break_point` VALUES (21, 'จุดรับส่ง 21');
INSERT INTO `break_point` VALUES (22, 'จุดรับส่ง 22');
INSERT INTO `break_point` VALUES (23, 'จุดรับส่ง 23');
INSERT INTO `break_point` VALUES (24, 'จุดรับส่ง 24');
INSERT INTO `break_point` VALUES (25, 'จุดรับส่ง 25');
INSERT INTO `break_point` VALUES (26, 'จุดรับส่ง 26');
INSERT INTO `break_point` VALUES (27, 'จุดรับส่ง 27');
INSERT INTO `break_point` VALUES (28, 'จุดรับส่ง 28');
INSERT INTO `break_point` VALUES (29, 'จุดรับส่ง 29');
INSERT INTO `break_point` VALUES (30, 'จุดรับส่ง 30');

-- ----------------------------
-- Table structure for brk_in_route
-- ----------------------------
DROP TABLE IF EXISTS `brk_in_route`;
CREATE TABLE `brk_in_route`  (
  `bir_id` int(11) NOT NULL AUTO_INCREMENT,
  `br_id` int(11) NOT NULL,
  `bir_time` int(11) NOT NULL,
  `brkp_id` int(11) NOT NULL,
  `bir_status` int(11) NOT NULL,
  `bir_type` int(11) NOT NULL,
  PRIMARY KEY (`bir_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 41 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of brk_in_route
-- ----------------------------
INSERT INTO `brk_in_route` VALUES (1, 1, 21, 1, 1, 2);
INSERT INTO `brk_in_route` VALUES (2, 1, 33, 2, 2, 1);
INSERT INTO `brk_in_route` VALUES (3, 1, 41, 3, 1, 2);
INSERT INTO `brk_in_route` VALUES (4, 1, 15, 4, 2, 1);
INSERT INTO `brk_in_route` VALUES (5, 1, 28, 5, 2, 1);
INSERT INTO `brk_in_route` VALUES (6, 1, 19, 6, 2, 1);
INSERT INTO `brk_in_route` VALUES (7, 1, 48, 7, 1, 2);
INSERT INTO `brk_in_route` VALUES (8, 1, 55, 8, 2, 1);
INSERT INTO `brk_in_route` VALUES (9, 1, 38, 9, 2, 1);
INSERT INTO `brk_in_route` VALUES (10, 1, 17, 10, 1, 2);
INSERT INTO `brk_in_route` VALUES (11, 2, 19, 10, 1, 2);
INSERT INTO `brk_in_route` VALUES (12, 2, 35, 9, 2, 1);
INSERT INTO `brk_in_route` VALUES (13, 2, 41, 8, 2, 1);
INSERT INTO `brk_in_route` VALUES (14, 2, 59, 7, 1, 2);
INSERT INTO `brk_in_route` VALUES (15, 2, 28, 6, 2, 1);
INSERT INTO `brk_in_route` VALUES (16, 2, 19, 5, 2, 1);
INSERT INTO `brk_in_route` VALUES (17, 2, 32, 4, 2, 1);
INSERT INTO `brk_in_route` VALUES (18, 2, 55, 3, 1, 2);
INSERT INTO `brk_in_route` VALUES (19, 2, 38, 2, 1, 1);
INSERT INTO `brk_in_route` VALUES (20, 2, 17, 1, 1, 2);
INSERT INTO `brk_in_route` VALUES (21, 3, 46, 11, 1, 2);
INSERT INTO `brk_in_route` VALUES (22, 3, 51, 12, 1, 1);
INSERT INTO `brk_in_route` VALUES (23, 3, 25, 13, 1, 2);
INSERT INTO `brk_in_route` VALUES (24, 3, 39, 14, 2, 1);
INSERT INTO `brk_in_route` VALUES (25, 3, 20, 15, 2, 1);
INSERT INTO `brk_in_route` VALUES (26, 3, 56, 16, 2, 1);
INSERT INTO `brk_in_route` VALUES (27, 3, 42, 17, 1, 2);
INSERT INTO `brk_in_route` VALUES (28, 3, 12, 18, 2, 1);
INSERT INTO `brk_in_route` VALUES (29, 3, 53, 19, 1, 1);
INSERT INTO `brk_in_route` VALUES (30, 3, 16, 20, 1, 2);
INSERT INTO `brk_in_route` VALUES (31, 4, 46, 20, 1, 1);
INSERT INTO `brk_in_route` VALUES (32, 4, 51, 19, 1, 2);
INSERT INTO `brk_in_route` VALUES (33, 4, 25, 18, 2, 1);
INSERT INTO `brk_in_route` VALUES (34, 4, 39, 17, 1, 2);
INSERT INTO `brk_in_route` VALUES (35, 4, 41, 16, 2, 1);
INSERT INTO `brk_in_route` VALUES (36, 4, 56, 15, 2, 1);
INSERT INTO `brk_in_route` VALUES (37, 4, 42, 14, 2, 1);
INSERT INTO `brk_in_route` VALUES (38, 4, 29, 13, 1, 2);
INSERT INTO `brk_in_route` VALUES (39, 4, 53, 12, 1, 1);
INSERT INTO `brk_in_route` VALUES (40, 4, 16, 11, 1, 2);

-- ----------------------------
-- Table structure for bus_group
-- ----------------------------
DROP TABLE IF EXISTS `bus_group`;
CREATE TABLE `bus_group`  (
  `gb_id` int(11) NOT NULL AUTO_INCREMENT,
  `bi_id` int(11) NOT NULL,
  `main_dri` int(11) NOT NULL,
  `ex_1` int(11) NOT NULL,
  `ex_2` int(11) NOT NULL,
  `coach` int(11) NOT NULL,
  PRIMARY KEY (`gb_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bus_group
-- ----------------------------

-- ----------------------------
-- Table structure for bus_info
-- ----------------------------
DROP TABLE IF EXISTS `bus_info`;
CREATE TABLE `bus_info`  (
  `bi_id` int(11) NOT NULL AUTO_INCREMENT,
  `bi_licen` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `br_id` int(11) NOT NULL,
  `bt_id` int(11) NOT NULL,
  `status_id` int(11) NULL DEFAULT 1,
  PRIMARY KEY (`bi_id`) USING BTREE,
  INDEX `fk_bus_status`(`status_id` ASC) USING BTREE,
  CONSTRAINT `fk_bus_status` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bus_info
-- ----------------------------
INSERT INTO `bus_info` VALUES (1, '11-1234', 1, 2, 1);
INSERT INTO `bus_info` VALUES (2, '12-1234', 1, 2, 1);
INSERT INTO `bus_info` VALUES (3, '13-1234', 1, 2, 1);
INSERT INTO `bus_info` VALUES (4, '14-1234', 1, 2, 1);
INSERT INTO `bus_info` VALUES (5, '15-1234', 1, 2, 1);
INSERT INTO `bus_info` VALUES (6, '16-1234', 1, 2, 1);
INSERT INTO `bus_info` VALUES (7, '17-1234', 1, 2, 1);
INSERT INTO `bus_info` VALUES (8, '18-1234', 1, 2, 1);
INSERT INTO `bus_info` VALUES (9, '22-2115', 2, 2, 1);
INSERT INTO `bus_info` VALUES (10, '22-2116', 2, 2, 1);
INSERT INTO `bus_info` VALUES (11, '22-2117', 2, 2, 1);
INSERT INTO `bus_info` VALUES (12, '22-2118', 2, 2, 1);
INSERT INTO `bus_info` VALUES (13, '23-2119', 3, 2, 1);
INSERT INTO `bus_info` VALUES (14, '23-2120', 3, 2, 1);
INSERT INTO `bus_info` VALUES (15, '23-2121', 3, 2, 1);
INSERT INTO `bus_info` VALUES (16, '23-2122', 3, 2, 1);
INSERT INTO `bus_info` VALUES (17, '24-2123', 4, 2, 1);
INSERT INTO `bus_info` VALUES (18, '24-2124', 4, 2, 1);
INSERT INTO `bus_info` VALUES (19, '24-2125', 4, 2, 1);
INSERT INTO `bus_info` VALUES (20, '24-2126', 4, 2, 1);

-- ----------------------------
-- Table structure for bus_plan
-- ----------------------------
DROP TABLE IF EXISTS `bus_plan`;
CREATE TABLE `bus_plan`  (
  `bp_id` int(11) NOT NULL AUTO_INCREMENT,
  `br_id` int(11) NOT NULL,
  `pr_id` int(11) NOT NULL,
  `bp_pr_no` int(11) NOT NULL,
  `bg_id` int(11) NOT NULL,
  `bs_id` int(11) NOT NULL,
  `bp_date` datetime NOT NULL,
  PRIMARY KEY (`bp_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bus_plan
-- ----------------------------

-- ----------------------------
-- Table structure for bus_routes
-- ----------------------------
DROP TABLE IF EXISTS `bus_routes`;
CREATE TABLE `bus_routes`  (
  `br_id` int(11) NOT NULL AUTO_INCREMENT,
  `br_start` int(11) NOT NULL,
  `br_end` int(11) NOT NULL,
  `bz_id` int(11) NOT NULL,
  PRIMARY KEY (`br_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 103 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bus_routes
-- ----------------------------
INSERT INTO `bus_routes` VALUES (1, 1, 2, 1);
INSERT INTO `bus_routes` VALUES (2, 2, 1, 1);
INSERT INTO `bus_routes` VALUES (3, 1, 9, 1);
INSERT INTO `bus_routes` VALUES (4, 9, 1, 1);
INSERT INTO `bus_routes` VALUES (5, 1, 10, 1);
INSERT INTO `bus_routes` VALUES (6, 10, 1, 1);
INSERT INTO `bus_routes` VALUES (7, 1, 11, 1);
INSERT INTO `bus_routes` VALUES (8, 11, 1, 1);
INSERT INTO `bus_routes` VALUES (9, 1, 12, 1);
INSERT INTO `bus_routes` VALUES (10, 12, 1, 1);
INSERT INTO `bus_routes` VALUES (11, 1, 13, 1);
INSERT INTO `bus_routes` VALUES (12, 13, 1, 1);
INSERT INTO `bus_routes` VALUES (13, 1, 14, 1);
INSERT INTO `bus_routes` VALUES (14, 14, 1, 1);
INSERT INTO `bus_routes` VALUES (15, 1, 15, 1);
INSERT INTO `bus_routes` VALUES (16, 15, 1, 1);
INSERT INTO `bus_routes` VALUES (17, 1, 16, 1);
INSERT INTO `bus_routes` VALUES (18, 16, 1, 1);
INSERT INTO `bus_routes` VALUES (19, 1, 17, 1);
INSERT INTO `bus_routes` VALUES (20, 17, 1, 1);
INSERT INTO `bus_routes` VALUES (21, 1, 18, 1);
INSERT INTO `bus_routes` VALUES (22, 18, 1, 1);
INSERT INTO `bus_routes` VALUES (23, 1, 19, 1);
INSERT INTO `bus_routes` VALUES (24, 19, 1, 1);
INSERT INTO `bus_routes` VALUES (25, 1, 20, 1);
INSERT INTO `bus_routes` VALUES (26, 20, 1, 1);
INSERT INTO `bus_routes` VALUES (27, 1, 21, 1);
INSERT INTO `bus_routes` VALUES (28, 21, 1, 1);
INSERT INTO `bus_routes` VALUES (29, 1, 22, 1);
INSERT INTO `bus_routes` VALUES (30, 22, 1, 1);
INSERT INTO `bus_routes` VALUES (31, 1, 23, 1);
INSERT INTO `bus_routes` VALUES (32, 23, 1, 1);
INSERT INTO `bus_routes` VALUES (33, 1, 24, 1);
INSERT INTO `bus_routes` VALUES (34, 24, 1, 1);
INSERT INTO `bus_routes` VALUES (35, 1, 25, 1);
INSERT INTO `bus_routes` VALUES (36, 25, 1, 1);
INSERT INTO `bus_routes` VALUES (37, 1, 26, 1);
INSERT INTO `bus_routes` VALUES (38, 26, 1, 1);
INSERT INTO `bus_routes` VALUES (39, 1, 27, 1);
INSERT INTO `bus_routes` VALUES (40, 27, 1, 1);
INSERT INTO `bus_routes` VALUES (41, 1, 28, 1);
INSERT INTO `bus_routes` VALUES (42, 28, 1, 1);
INSERT INTO `bus_routes` VALUES (43, 1, 29, 1);
INSERT INTO `bus_routes` VALUES (44, 29, 1, 1);
INSERT INTO `bus_routes` VALUES (45, 1, 30, 1);
INSERT INTO `bus_routes` VALUES (46, 30, 1, 1);
INSERT INTO `bus_routes` VALUES (47, 1, 31, 1);
INSERT INTO `bus_routes` VALUES (48, 31, 1, 1);
INSERT INTO `bus_routes` VALUES (49, 1, 32, 1);
INSERT INTO `bus_routes` VALUES (50, 32, 1, 1);
INSERT INTO `bus_routes` VALUES (51, 1, 33, 1);
INSERT INTO `bus_routes` VALUES (52, 33, 1, 1);
INSERT INTO `bus_routes` VALUES (53, 1, 3, 2);
INSERT INTO `bus_routes` VALUES (54, 3, 1, 2);
INSERT INTO `bus_routes` VALUES (55, 1, 4, 2);
INSERT INTO `bus_routes` VALUES (56, 4, 1, 2);
INSERT INTO `bus_routes` VALUES (57, 1, 5, 2);
INSERT INTO `bus_routes` VALUES (58, 5, 1, 2);
INSERT INTO `bus_routes` VALUES (59, 1, 6, 2);
INSERT INTO `bus_routes` VALUES (60, 6, 1, 2);
INSERT INTO `bus_routes` VALUES (61, 1, 7, 2);
INSERT INTO `bus_routes` VALUES (62, 7, 1, 2);
INSERT INTO `bus_routes` VALUES (63, 1, 8, 2);
INSERT INTO `bus_routes` VALUES (64, 8, 1, 2);
INSERT INTO `bus_routes` VALUES (65, 9, 5, 3);
INSERT INTO `bus_routes` VALUES (66, 5, 9, 3);
INSERT INTO `bus_routes` VALUES (67, 9, 4, 3);
INSERT INTO `bus_routes` VALUES (68, 4, 9, 3);
INSERT INTO `bus_routes` VALUES (69, 9, 34, 3);
INSERT INTO `bus_routes` VALUES (70, 34, 9, 3);
INSERT INTO `bus_routes` VALUES (71, 12, 34, 3);
INSERT INTO `bus_routes` VALUES (72, 34, 12, 3);
INSERT INTO `bus_routes` VALUES (73, 34, 5, 3);
INSERT INTO `bus_routes` VALUES (74, 5, 34, 3);
INSERT INTO `bus_routes` VALUES (75, 35, 34, 3);
INSERT INTO `bus_routes` VALUES (76, 34, 35, 3);
INSERT INTO `bus_routes` VALUES (77, 3, 34, 3);
INSERT INTO `bus_routes` VALUES (78, 34, 3, 3);
INSERT INTO `bus_routes` VALUES (79, 11, 34, 3);
INSERT INTO `bus_routes` VALUES (80, 34, 11, 3);
INSERT INTO `bus_routes` VALUES (81, 4, 34, 3);
INSERT INTO `bus_routes` VALUES (82, 34, 4, 3);
INSERT INTO `bus_routes` VALUES (83, 8, 34, 3);
INSERT INTO `bus_routes` VALUES (84, 34, 8, 3);
INSERT INTO `bus_routes` VALUES (85, 9, 3, 3);
INSERT INTO `bus_routes` VALUES (86, 3, 9, 3);
INSERT INTO `bus_routes` VALUES (87, 13, 34, 3);
INSERT INTO `bus_routes` VALUES (88, 34, 13, 3);
INSERT INTO `bus_routes` VALUES (89, 12, 5, 3);
INSERT INTO `bus_routes` VALUES (90, 5, 12, 3);
INSERT INTO `bus_routes` VALUES (91, 36, 34, 3);
INSERT INTO `bus_routes` VALUES (92, 34, 36, 3);
INSERT INTO `bus_routes` VALUES (93, 2, 34, 3);
INSERT INTO `bus_routes` VALUES (94, 34, 2, 3);
INSERT INTO `bus_routes` VALUES (95, 15, 34, 3);
INSERT INTO `bus_routes` VALUES (96, 34, 15, 3);
INSERT INTO `bus_routes` VALUES (97, 22, 34, 3);
INSERT INTO `bus_routes` VALUES (98, 34, 22, 3);
INSERT INTO `bus_routes` VALUES (99, 21, 34, 3);
INSERT INTO `bus_routes` VALUES (100, 34, 21, 3);
INSERT INTO `bus_routes` VALUES (101, 18, 34, 3);
INSERT INTO `bus_routes` VALUES (102, 34, 18, 3);

-- ----------------------------
-- Table structure for bus_zone
-- ----------------------------
DROP TABLE IF EXISTS `bus_zone`;
CREATE TABLE `bus_zone`  (
  `bz_id` int(11) NOT NULL AUTO_INCREMENT,
  `bz_name_th` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `bz_name_en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`bz_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bus_zone
-- ----------------------------
INSERT INTO `bus_zone` VALUES (1, 'ภาคตะวันออกเฉียงเหนือ (อีสาน)', 'Northeastern');
INSERT INTO `bus_zone` VALUES (2, 'ภาคเหนือ', 'Northern');
INSERT INTO `bus_zone` VALUES (3, 'ต่างจังหวัด', 'Cross-regional');

-- ----------------------------
-- Table structure for emp_history
-- ----------------------------
DROP TABLE IF EXISTS `emp_history`;
CREATE TABLE `emp_history`  (
  `eh_id` int(11) NOT NULL AUTO_INCREMENT,
  `em_id` int(11) NOT NULL,
  `eh_his` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`eh_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of emp_history
-- ----------------------------

-- ----------------------------
-- Table structure for employee
-- ----------------------------
DROP TABLE IF EXISTS `employee`;
CREATE TABLE `employee`  (
  `em_id` int(11) NOT NULL AUTO_INCREMENT,
  `title_id` int(11) NOT NULL,
  `em_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `em_surname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `gen_id` int(11) NOT NULL,
  `main_car` int(11) NOT NULL,
  `main_route` int(11) NOT NULL,
  `et_id` int(11) NOT NULL,
  `em_queue` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `em_timeOut` datetime NULL DEFAULT NULL,
  `es_id` int(11) NOT NULL,
  PRIMARY KEY (`em_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 106 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of employee
-- ----------------------------
INSERT INTO `employee` VALUES (1, 1, 'สมศักดิ์', 'ใจดี', 1, 1, 1, 1, '1-3-1', '2025-07-30 20:45:00', 1);
INSERT INTO `employee` VALUES (2, 1, 'วีระ', 'มีสุข', 1, 2, 1, 1, '1-3-2', '2025-07-29 14:47:00', 1);
INSERT INTO `employee` VALUES (3, 2, 'สุดา', 'รักไทย', 2, 3, 1, 1, '1-1-2', '2025-07-29 14:48:00', 1);
INSERT INTO `employee` VALUES (4, 1, 'มานะ', 'กล้าหาญ', 1, 4, 1, 1, '1-1-3', '2025-07-29 14:50:00', 1);
INSERT INTO `employee` VALUES (5, 3, 'ดวงใจ', 'เมตตา', 2, 5, 1, 1, '1-1-1', '2025-07-30 14:50:37', 1);
INSERT INTO `employee` VALUES (6, 1, 'ประเสริฐ', 'ยิ่งยง', 1, 6, 1, 1, '1-3-last', '2025-07-30 14:51:30', 1);
INSERT INTO `employee` VALUES (7, 3, 'อรุณี', 'แสงทอง', 2, 7, 1, 1, '1-3-3', '2025-07-30 14:51:00', 1);
INSERT INTO `employee` VALUES (8, 1, 'ชัยวัฒน์', 'สุขสบาย', 1, 8, 1, 1, '1-3-4', '2025-07-30 14:51:22', 1);
INSERT INTO `employee` VALUES (9, 2, 'เพ็ญศรี', 'รัศมี', 2, 0, 1, 3, '1-1-3', '2025-07-30 14:52:47', 1);
INSERT INTO `employee` VALUES (10, 1, 'กำพล', 'พร้อมพงษ์', 1, 0, 1, 3, '1-2-2', '2025-07-30 14:53:00', 1);
INSERT INTO `employee` VALUES (11, 3, 'สายชล', 'ธารา', 2, 0, 1, 2, '3', '2025-07-30 14:54:23', 1);
INSERT INTO `employee` VALUES (12, 1, 'ทรงชัย', 'บารมี', 1, 0, 1, 2, '3', '2025-07-30 14:55:04', 1);
INSERT INTO `employee` VALUES (13, 2, 'นงนุช', 'พฤกษา', 2, 0, 1, 2, '3', '2025-07-29 14:53:29', 1);
INSERT INTO `employee` VALUES (14, 1, 'เดชา', 'ชาญชัย', 1, 0, 1, 2, '3', '2025-07-30 14:54:09', 1);
INSERT INTO `employee` VALUES (15, 3, 'ปาริฉัตร', 'งามดี', 2, 0, 1, 2, '3', '2025-07-30 14:53:37', 1);
INSERT INTO `employee` VALUES (16, 1, 'ทนงศักดิ์', 'พงษ์ไทย', 1, 0, 1, 2, '3', '2025-07-30 14:54:41', 1);
INSERT INTO `employee` VALUES (17, 2, 'รัชนี', 'ดารา', 2, 0, 1, 2, '7', '2025-07-30 14:53:55', 1);
INSERT INTO `employee` VALUES (18, 2, 'กานดา', 'ไพเราะ', 2, 0, 1, 3, '1-2-4', '2025-07-30 14:53:48', 1);
INSERT INTO `employee` VALUES (19, 1, 'ศักดา', 'มั่นคง', 1, 0, 1, 3, '1-2-5', '2025-07-30 14:54:30', 1);
INSERT INTO `employee` VALUES (20, 1, 'บุญมี', 'ลาภผล', 1, 0, 1, 3, '1-1-3', '2025-07-30 14:55:01', 1);
INSERT INTO `employee` VALUES (21, 3, 'อัจฉรา', 'ฉลาด', 2, 0, 1, 3, '1-2-1', '2025-07-29 14:54:49', 1);
INSERT INTO `employee` VALUES (34, 1, 'สายฟ้า', 'ทองดี', 1, 9, 2, 1, '2-3-1', '2025-07-30 16:11:19', 1);
INSERT INTO `employee` VALUES (35, 2, 'อรุณ', 'มณี', 2, 10, 2, 1, '2-3-2', '2025-07-30 16:01:58', 1);
INSERT INTO `employee` VALUES (36, 3, 'ประทีป', 'ศิริ', 1, 11, 2, 1, '2-3-last', '2025-07-30 15:46:26', 1);
INSERT INTO `employee` VALUES (37, 1, 'รุ่ง', 'จิตร', 2, 12, 2, 1, '2-1-3', '2025-07-30 16:39:58', 1);
INSERT INTO `employee` VALUES (38, 2, 'สุมาลี', 'บุญมาก', 2, 13, 3, 1, '3-3-1', '2025-07-29 17:42:27', 1);
INSERT INTO `employee` VALUES (39, 3, 'พนม', 'ชูชาติ', 1, 14, 3, 1, '3-1-1', '2025-07-30 15:51:37', 1);
INSERT INTO `employee` VALUES (40, 1, 'สมหมาย', 'สายใจ', 1, 15, 3, 1, '3-3-1', '2025-07-30 15:40:15', 1);
INSERT INTO `employee` VALUES (41, 2, 'สุนี', 'ใจดี', 2, 16, 3, 1, '3-3-2', '2025-07-29 17:42:31', 1);
INSERT INTO `employee` VALUES (42, 3, 'สุชาติ', 'มานะ', 1, 17, 4, 1, '4-3-1', '2025-07-30 16:42:09', 1);
INSERT INTO `employee` VALUES (43, 1, 'ศิริพร', 'เพียรดี', 2, 18, 4, 1, '4-3-2', '2025-07-30 15:51:06', 1);
INSERT INTO `employee` VALUES (44, 2, 'เจริญ', 'เอื้อเฟื้อ', 1, 19, 4, 1, '4-3-last', '2025-07-30 15:40:46', 1);
INSERT INTO `employee` VALUES (45, 3, 'ผ่องศรี', 'วิชัย', 2, 20, 4, 1, '4-1-1', '2025-07-29 17:42:34', 1);
INSERT INTO `employee` VALUES (46, 1, 'จิราพร', 'วิเศษ', 2, 0, 2, 2, '7', '2025-07-30 15:42:59', 1);
INSERT INTO `employee` VALUES (47, 2, 'มานพ', 'เลิศ', 1, 0, 2, 2, '7', '2025-07-30 15:58:15', 1);
INSERT INTO `employee` VALUES (48, 3, 'ปริญญา', 'เบิกบาน', 1, 0, 2, 2, '7', '2025-07-29 17:46:05', 1);
INSERT INTO `employee` VALUES (49, 1, 'ชลธิชา', 'เพ็ญ', 2, 0, 2, 2, '7', '2025-07-30 15:23:43', 1);
INSERT INTO `employee` VALUES (50, 2, 'สุนันทา', 'นามดี', 2, 0, 3, 2, '13', '2025-07-30 15:32:50', 1);
INSERT INTO `employee` VALUES (51, 3, 'วิทยา', 'รุ่งเรือง', 1, 0, 3, 2, '13', '2025-07-30 15:53:53', 1);
INSERT INTO `employee` VALUES (52, 1, 'อารี', 'มั่นคง', 1, 0, 3, 2, '13', '2025-07-30 16:24:01', 1);
INSERT INTO `employee` VALUES (53, 2, 'บรรเจิด', 'ก้องเกียรติ', 1, 0, 3, 2, '13', '2025-07-29 17:46:01', 1);
INSERT INTO `employee` VALUES (54, 3, 'จันทร์เพ็ญ', 'วรางค์', 2, 0, 4, 2, '17', '2025-07-30 14:55:40', 1);
INSERT INTO `employee` VALUES (55, 1, 'ณัฐ', 'เสริมสุข', 1, 0, 4, 2, '17', '2025-07-30 14:58:55', 1);
INSERT INTO `employee` VALUES (56, 1, 'สุนทร', 'กิติ', 1, 0, 2, 3, '2-2-1', '2025-07-30 15:34:22', 1);
INSERT INTO `employee` VALUES (57, 2, 'พชร', 'จันทรา', 2, 0, 2, 3, '2-2-1', '2025-07-30 15:54:32', 1);
INSERT INTO `employee` VALUES (58, 3, 'อารีย์', 'บุญมาก', 2, 0, 2, 3, '2-2-1', '2025-07-30 16:24:42', 1);
INSERT INTO `employee` VALUES (59, 1, 'วิชัย', 'เกียรติคุณ', 1, 0, 2, 3, '2-2-2', '2025-07-29 17:45:58', 1);
INSERT INTO `employee` VALUES (60, 2, 'สายพิณ', 'สวัสดี', 2, 0, 3, 3, '3-2-3', '2025-07-30 15:32:41', 1);
INSERT INTO `employee` VALUES (61, 3, 'สุพัตรา', 'เรืองศรี', 1, 0, 3, 3, '3-2-1', '2025-07-30 15:45:21', 1);
INSERT INTO `employee` VALUES (62, 1, 'จันทร์', 'สามัคคี', 2, 0, 3, 3, '3-1-1', '2025-07-30 16:15:27', 1);
INSERT INTO `employee` VALUES (63, 2, 'ประเสริฐ', 'วัฒนกิจ', 1, 0, 3, 3, '3-2-2', '2025-07-29 17:45:55', 1);
INSERT INTO `employee` VALUES (64, 3, 'สายใจ', 'สมบูรณ์', 2, 0, 4, 3, '4-2-2', '2025-07-30 14:33:14', 1);
INSERT INTO `employee` VALUES (65, 1, 'อุษา', 'แสงทอง', 2, 0, 4, 3, '4-2-3', '2025-07-30 14:58:54', 1);
INSERT INTO `employee` VALUES (66, 2, 'พิมพ์', 'เรืองเดช', 2, 0, 4, 2, '17', '2025-07-29 14:45:33', 1);
INSERT INTO `employee` VALUES (67, 3, 'อภิชาติ', 'เพชรดี', 1, 0, 4, 2, '17', '2025-07-30 14:40:51', 1);
INSERT INTO `employee` VALUES (68, 2, 'อภิญญา', 'รัตนชัย', 2, 0, 4, 3, '4-2-1', '2025-07-29 14:55:02', 1);
INSERT INTO `employee` VALUES (69, 3, 'เกรียงไกร', 'พูนผล', 1, 0, 4, 3, '4-1-1', '2025-07-30 14:30:04', 1);

-- ----------------------------
-- Table structure for location
-- ----------------------------
DROP TABLE IF EXISTS `location`;
CREATE TABLE `location`  (
  `locat_id` int(11) NOT NULL AUTO_INCREMENT,
  `locat_name_th` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `locat_name_eng` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `lot_id` int(11) NOT NULL,
  PRIMARY KEY (`locat_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 37 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of location
-- ----------------------------
INSERT INTO `location` VALUES (1, 'กรุงเทพฯ', 'Bangkok', 1);
INSERT INTO `location` VALUES (2, 'ขอนแก่น', 'Khon Kaen', 1);
INSERT INTO `location` VALUES (3, 'น่าน', 'Nan', 1);
INSERT INTO `location` VALUES (4, 'เชียงราย', 'Chiang Rai', 1);
INSERT INTO `location` VALUES (5, 'เชียงใหม่', 'Chiang Mai', 1);
INSERT INTO `location` VALUES (6, 'ลำปาง', 'Lampang', 1);
INSERT INTO `location` VALUES (7, 'แพร่', 'Phrae', 1);
INSERT INTO `location` VALUES (8, 'อุตรดิตถ์', 'Uttaradit', 1);
INSERT INTO `location` VALUES (9, 'อุบลราชธานี', 'Ubon Ratchathani', 1);
INSERT INTO `location` VALUES (10, 'อ.เดชอุดม', 'Det Udom', 1);
INSERT INTO `location` VALUES (11, 'ศรีสะเกษ', 'Si Sa Ket', 1);
INSERT INTO `location` VALUES (12, 'สุรินทร์', 'Surin', 1);
INSERT INTO `location` VALUES (13, 'นางรอง,บุรีรัมย์', 'Nang Rong', 1);
INSERT INTO `location` VALUES (14, 'หนองคาย', 'Nong Khai', 1);
INSERT INTO `location` VALUES (15, 'อุดรธานี', 'Udon Thani', 1);
INSERT INTO `location` VALUES (16, 'มหาสารคาม', 'Maha Sarakham', 1);
INSERT INTO `location` VALUES (17, 'หนองบัวลำภู', 'Nong Bua Lamphu', 1);
INSERT INTO `location` VALUES (18, 'นครพนม', 'Nakhon Phanom', 1);
INSERT INTO `location` VALUES (19, 'อ.ธาตุพนม', 'That Phanom', 1);
INSERT INTO `location` VALUES (20, 'อ.ศรีสงคราม', 'Si Songkhram', 1);
INSERT INTO `location` VALUES (21, 'สกลนคร', 'Sakon Nakhon', 1);
INSERT INTO `location` VALUES (22, 'กาฬสินธุ์', 'Kalasin', 1);
INSERT INTO `location` VALUES (23, 'อ.คำม่วง', 'Kham Muang', 1);
INSERT INTO `location` VALUES (24, 'มุกดาหาร', 'Mukdahan', 1);
INSERT INTO `location` VALUES (25, 'ร้อยเอ็ด', 'Roi Et', 1);
INSERT INTO `location` VALUES (26, 'อำนาจเจริญ', 'Amnat Charoen', 1);
INSERT INTO `location` VALUES (27, 'สนม', 'Sanom', 1);
INSERT INTO `location` VALUES (28, 'อ.เขมราฐ', 'Khemarat', 1);
INSERT INTO `location` VALUES (29, 'จักราช,บุรีรัมย์', 'Chakkarat', 1);
INSERT INTO `location` VALUES (30, 'ยโสธร', 'Yasothon', 1);
INSERT INTO `location` VALUES (31, 'บ้านแพง', 'Ban Phaeng', 1);
INSERT INTO `location` VALUES (32, 'อ.บุณฑริก', 'Buntharik', 1);
INSERT INTO `location` VALUES (33, 'อ.ราษีไศล', 'Rasi Salai', 1);
INSERT INTO `location` VALUES (34, 'ระยอง', 'Rayong', 1);
INSERT INTO `location` VALUES (35, 'อ.แม่สาย', 'Mae Sai', 1);
INSERT INTO `location` VALUES (36, 'พิษณุโลก', 'Phitsanulok', 1);

-- ----------------------------
-- Table structure for plan_request
-- ----------------------------
DROP TABLE IF EXISTS `plan_request`;
CREATE TABLE `plan_request`  (
  `pr_id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `br_id` int(11) NOT NULL,
  `pr_date` date NOT NULL,
  `pr_request` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `pr_plus` int(11) NOT NULL,
  `pr_status` int(11) NOT NULL,
  `pr_loc` datetime NOT NULL,
  PRIMARY KEY (`pr_id`) USING BTREE,
  CONSTRAINT `plan_request_chk_1` CHECK (json_valid(`pr_request`))
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan_request
-- ----------------------------
INSERT INTO `plan_request` VALUES (1, '', 1, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"127\",\"315\",\"315\",\"315\",\"315\"]}', 0, 2, '2025-08-08 11:19:10');
INSERT INTO `plan_request` VALUES (2, '', 2, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\"],\"time_plus\":[\"343\",\"343\",\"343\"]}', 0, 2, '2025-08-08 11:19:10');
INSERT INTO `plan_request` VALUES (3, '', 3, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45\",\"12:45\",\"15:45\"],\"time_plus\":[\"360\",\"360\",\"360\"]}', 0, 2, '2025-08-08 11:19:10');
INSERT INTO `plan_request` VALUES (4, '', 4, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"10:45\",\"13:30\",\"18:45\"],\"time_plus\":[\"398\",\"398\",\"398\"]}', 0, 2, '2025-08-08 11:19:10');
INSERT INTO `plan_request` VALUES (5, '', 1, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"300\",\"315\",\"315\",\"315\",\"315\"]}', 0, 2, '2025-08-08 11:22:10');
INSERT INTO `plan_request` VALUES (6, '', 2, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\"],\"time_plus\":[\"343\",\"343\",\"343\"]}', 0, 2, '2025-08-08 11:22:10');
INSERT INTO `plan_request` VALUES (7, '', 3, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45\",\"12:45\",\"15:45\"],\"time_plus\":[\"360\",\"360\",\"360\"]}', 0, 2, '2025-08-08 11:22:10');
INSERT INTO `plan_request` VALUES (8, '', 4, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"10:45\",\"13:30\",\"18:45\"],\"time_plus\":[\"398\",\"398\",\"398\"]}', 0, 2, '2025-08-08 11:22:10');
INSERT INTO `plan_request` VALUES (9, 'คิวมาตรฐานของสาย 1', 1, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"127\",\"315\",\"315\",\"315\",\"315\"],\"point\":[[\"1\",\"3\",\"7\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"]],\"ex\":[{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 0, '2025-08-08 14:17:57');
INSERT INTO `plan_request` VALUES (10, 'คิวมาตรฐานของสาย 2', 2, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\"],\"time_plus\":[\"343\",\"343\",\"343\"],\"point\":[[\"10\",\"9\",\"8\",\"7\",\"6\",\"5\",\"4\",\"3\",\"2\",\"1\"],[\"10\",\"9\",\"8\",\"6\",\"5\",\"4\",\"2\",\"1\",\"7\",\"3\"],[\"10\",\"9\",\"8\",\"6\",\"5\",\"4\",\"2\",\"1\",\"7\",\"3\"]],\"ex\":[{\"start1\":\"7\",\"end1\":\"3\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"7\",\"end1\":\"3\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"7\",\"end1\":\"3\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 0, '2025-08-08 14:17:57');
INSERT INTO `plan_request` VALUES (11, 'คิวมาตรฐานของสาย 3', 3, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45\",\"12:45\",\"15:45\"],\"time_plus\":[\"360\",\"360\",\"360\"],\"point\":[[\"14\",\"15\",\"16\",\"18\",\"19\",\"20\",\"11\",\"12\",\"13\",\"17\"],[\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"17\",\"18\",\"19\",\"20\"],[\"14\",\"15\",\"16\",\"18\",\"19\",\"20\",\"11\",\"12\",\"13\",\"17\"]],\"ex\":[{\"start1\":\"13\",\"end1\":\"17\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"13\",\"end1\":\"17\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"13\",\"end1\":\"17\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 0, '2025-08-08 14:17:57');
INSERT INTO `plan_request` VALUES (12, 'คิวมาตรฐานของสาย 4', 4, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"10:45\",\"13:30\",\"18:45\"],\"time_plus\":[\"398\",\"398\",\"398\"],\"point\":[[\"20\",\"19\",\"18\",\"16\",\"15\",\"14\",\"17\",\"13\",\"12\",\"11\"],[\"20\",\"19\",\"18\",\"16\",\"15\",\"14\",\"17\",\"13\",\"12\",\"11\"],[\"20\",\"19\",\"18\",\"16\",\"15\",\"14\",\"17\",\"13\",\"12\",\"11\"]],\"ex\":[{\"start1\":\"17\",\"end1\":\"13\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"17\",\"end1\":\"13\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"17\",\"end1\":\"13\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 0, '2025-08-08 14:17:57');
INSERT INTO `plan_request` VALUES (13, 'คิวมาตรฐานของสาย 1', 1, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"127\",\"315\",\"315\",\"315\",\"315\"],\"point\":[[\"1\",\"3\",\"7\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"]],\"ex\":[{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 0, '2025-08-08 14:19:12');
INSERT INTO `plan_request` VALUES (14, 'คิวมาตรฐานของสาย 1', 1, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"127\",\"315\",\"315\",\"315\",\"315\"],\"point\":[[\"1\",\"3\",\"7\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"]],\"ex\":[{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 0, '2025-08-08 14:19:56');
INSERT INTO `plan_request` VALUES (15, 'คิวมาตรฐานของสาย 1', 1, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"127\",\"315\",\"315\",\"315\",\"315\"],\"point\":[[\"1\",\"3\",\"7\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"]],\"ex\":[{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 0, '2025-08-08 14:23:09');
INSERT INTO `plan_request` VALUES (16, 'หห', 1, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"127\",\"315\",\"315\",\"315\",\"315\"],\"point\":[[\"1\",\"3\",\"7\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"]],\"ex\":[{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 1, '2025-08-08 14:23:51');
INSERT INTO `plan_request` VALUES (17, 'หหหห', 1, '2025-08-08', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"127\",\"315\",\"315\",\"315\",\"315\"],\"point\":[[\"1\",\"3\",\"7\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"],[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\"]],\"ex\":[{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"}]}', 0, 2, '2025-08-08 14:24:31');
INSERT INTO `plan_request` VALUES (18, '', 1, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"127\",\"315\",\"315\",\"315\",\"315\"]}', 0, 1, '2025-08-08 14:51:24');
INSERT INTO `plan_request` VALUES (19, '', 2, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\"],\"time_plus\":[\"343\",\"343\",\"343\"]}', 0, 1, '2025-08-08 14:51:24');
INSERT INTO `plan_request` VALUES (20, '', 3, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45\",\"12:45\",\"15:45\"],\"time_plus\":[\"360\",\"360\",\"360\"]}', 0, 1, '2025-08-08 14:51:24');
INSERT INTO `plan_request` VALUES (21, '', 4, '2025-08-09', '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"10:45\",\"13:30\",\"18:45\"],\"time_plus\":[\"398\",\"398\",\"398\"]}', 0, 1, '2025-08-08 14:51:24');

-- ----------------------------
-- Table structure for queue_request
-- ----------------------------
DROP TABLE IF EXISTS `queue_request`;
CREATE TABLE `queue_request`  (
  `qr_id` int(11) NOT NULL AUTO_INCREMENT,
  `br_id` int(11) NOT NULL,
  `qr_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `br_go` int(11) NOT NULL,
  `qr_request` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `qr_return` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`qr_id`) USING BTREE,
  CONSTRAINT `queue_request_chk_1` CHECK (json_valid(`qr_return`))
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of queue_request
-- ----------------------------
INSERT INTO `queue_request` VALUES (1, 1, 'แผนมาตรฐานของสาย 1', 2, '{\"request\":[\"2\",\"2\",\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\",\"13:45\",\"15:45\"],\"time_plus\":[\"300\",\"315\",\"315\",\"315\",\"315\"],\"point\":[[1,2,3,5,6,7,8,9,10],[1,2,3,4,5,6,7,8,9,10],[1,2,3,4,5,6,7,8,9,10],[1,2,3,4,5,6,7,8,9,10],[1,2,3,4,5,6,7,8,9,10]],\"ex\":[{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"3\",\"end1\":\"7\",\"start2\":\"\",\"end2\":\"\"}]}', '[\"2\"]');
INSERT INTO `queue_request` VALUES (2, 2, 'แผนมาตรฐานของสาย 2', 1, '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45:00\",\"10:45\",\"11:45\"],\"time_plus\":[\"343\",\"343\",\"343\"],\"point\":[[10,9,8,7,6,5,4,3,2,1],[10,9,8,6,5,4,2,1,7,3],[10,9,8,6,5,4,2,1,7,3]],\"ex\":[{\"start1\":\"7\",\"end1\":\"3\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"7\",\"end1\":\"3\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"7\",\"end1\":\"3\",\"start2\":\"\",\"end2\":\"\"}]}', '[\"1\"]');
INSERT INTO `queue_request` VALUES (3, 3, 'แผนมาตรฐานของสาย 3', 4, '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"09:45\",\"12:45\",\"15:45\"],\"time_plus\":[\"360\",\"360\",\"360\"],\"point\":[[14,15,16,18,19,20,11,12,13,17],[11,12,13,14,15,16,17,18,19,20],[14,15,16,18,19,20,11,12,13,17]],\"ex\":[{\"start1\":\"13\",\"end1\":\"17\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"13\",\"end1\":\"17\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"13\",\"end1\":\"17\",\"start2\":\"\",\"end2\":\"\"}]}', '[\"4\"]');
INSERT INTO `queue_request` VALUES (4, 4, 'แผนมาตรฐานของสาย 4', 3, '{\"request\":[\"2\",\"2\",\"2\"],\"reserve\":[],\"time\":[\"10:45\",\"13:30\",\"18:45\"],\"time_plus\":[\"398\",\"398\",\"398\"],\"point\":[[20,19,18,16,15,14,17,13,12,11],[20,19,18,16,15,14,17,13,12,11],[20,19,18,16,15,14,17,13,12,11]],\"ex\":[{\"start1\":\"17\",\"end1\":\"13\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"17\",\"end1\":\"13\",\"start2\":\"\",\"end2\":\"\"},{\"start1\":\"17\",\"end1\":\"13\",\"start2\":\"\",\"end2\":\"\"}]}', '[\"3\"]');

-- ----------------------------
-- Table structure for status
-- ----------------------------
DROP TABLE IF EXISTS `status`;
CREATE TABLE `status`  (
  `status_id` int(11) NOT NULL,
  `status_name_th` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`status_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of status
-- ----------------------------
INSERT INTO `status` VALUES (1, 'พร้อม');
INSERT INTO `status` VALUES (2, 'ซ่อม');
INSERT INTO `status` VALUES (3, 'ลาป่วย');
INSERT INTO `status` VALUES (4, 'รีเทรนนิ่ง');

SET FOREIGN_KEY_CHECKS = 1;
