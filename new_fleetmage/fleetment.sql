/*
 Navicat Premium Data Transfer

 Source Server         : nca
 Source Server Type    : MySQL
 Source Server Version : 80017 (8.0.17)
 Source Host           : localhost:3306
 Source Schema         : fleetment

 Target Server Type    : MySQL
 Target Server Version : 80017 (8.0.17)
 File Encoding         : 65001

 Date: 15/08/2025 17:56:52
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bus_info
-- ----------------------------
DROP TABLE IF EXISTS `bus_info`;
CREATE TABLE `bus_info`  (
  `bus_id` int(11) NOT NULL AUTO_INCREMENT,
  `br_id` int(11) NOT NULL,
  `bus_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `full_bus_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `license_plate` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `engine_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `chassis_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `bus_type_id` int(11) NOT NULL,
  `in_service` tinyint(1) NOT NULL DEFAULT 1,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`bus_id`) USING BTREE,
  INDEX `br_id`(`br_id` ASC) USING BTREE,
  INDEX `bus_type_id`(`bus_type_id` ASC) USING BTREE,
  CONSTRAINT `bus_info_ibfk_2` FOREIGN KEY (`bus_type_id`) REFERENCES `bus_type` (`bt_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 470 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bus_info
-- ----------------------------
INSERT INTO `bus_info` VALUES (1, 5, '5-14', '5-14-ม.1(ข) พิเศษ', '11-1000', 'ENG9120011', 'CHS9120011', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (2, 5, '5-15', '5-15-ม.1(ข) พิเศษ', '11-1001', 'ENG9120021', 'CHS9120021', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (3, 5, '5-17', '5-17-ม.1(ข) พิเศษ', '11-1002', 'ENG9120031', 'CHS9120031', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (4, 18, '18-1', '18-1-ม.1ก', '18-1', 'ENG18101', 'CHS18101', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (5, 18, '18-2', '18-2-ม.1ก', '18-2', 'ENG18201', 'CHS18201', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (6, 18, '18-3', '18-3-ม.1ก', '18-3', 'ENG18301', 'CHS18301', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (7, 18, '18-4', '18-4-ม.1ก', '18-4', 'ENG18401', 'CHS18401', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (8, 18, '18-5', '18-5-ม.1ก', '18-5', 'ENG18501', 'CHS18501', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (9, 18, '18-22', '18-22-ม.1ก', '18-22', 'ENG182201', 'CHS182201', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (10, 18, '18-23', '18-23-ม.1ก', '18-23', 'ENG182301', 'CHS182301', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (11, 18, '18-24', '18-24-ม.1ก', '18-24', 'ENG182401', 'CHS182401', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (12, 18, '18-25', '18-25-ม.1ก', '18-25', 'ENG182501', 'CHS182501', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (13, 18, '18-26', '18-26-ม.1ก', '18-26', 'ENG182601', 'CHS182601', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (14, 18, '18-15', '18-15-ม.1(ข) พิเศษ', '18-15', 'ENG181501', 'CHS181501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (15, 18, '18-85', '18-85-ม.1(ข) พิเศษ', '18-85', 'ENG188501', 'CHS188501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (16, 18, '18-86', '18-86-ม.1(ข) พิเศษ', '18-86', 'ENG188601', 'CHS188601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (17, 18, '18-117', '18-117-ม.1(ข) พิเศษ', '18-117', 'ENG1811701', 'CHS1811701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (18, 18, '18-136', '18-136-ม.1(ข) พิเศษ', '18-136', 'ENG1813601', 'CHS1813601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (19, 18, '18-139', '18-139-ม.1(ข) พิเศษ', '18-139', 'ENG1813901', 'CHS1813901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (20, 18, '18-141', '18-141-ม.1(ข) พิเศษ', '18-141', 'ENG1814101', 'CHS1814101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (21, 18, '18-146', '18-146-ม.1(ข) พิเศษ', '18-146', 'ENG1814601', 'CHS1814601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (22, 18, '18-147', '18-147-ม.1(ข) พิเศษ', '18-147', 'ENG1814701', 'CHS1814701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (23, 18, '18-174', '18-174-ม.1(ข) พิเศษ', '18-174', 'ENG1817401', 'CHS1817401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (24, 18, '18-175', '18-175-ม.1(ข) พิเศษ', '18-175', 'ENG1817501', 'CHS1817501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (25, 18, '18-176', '18-176-ม.1(ข) พิเศษ', '18-176', 'ENG1817601', 'CHS1817601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (26, 18, '18-177', '18-177-ม.1(ข) พิเศษ', '18-177', 'ENG1817701', 'CHS1817701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (27, 18, '18-178', '18-178-ม.1(ข) พิเศษ', '18-178', 'ENG1817801', 'CHS1817801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (28, 18, '18-179', '18-179-ม.1(ข) พิเศษ', '18-179', 'ENG1817901', 'CHS1817901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (29, 18, '18-183', '18-183-ม.1(ข) พิเศษ', '18-183', 'ENG1818301', 'CHS1818301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (30, 18, '18-184', '18-184-ม.1(ข) พิเศษ', '18-184', 'ENG1818401', 'CHS1818401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (31, 18, '18-185', '18-185-ม.1(ข) พิเศษ', '18-185', 'ENG1818501', 'CHS1818501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (32, 18, '18-186', '18-186-ม.1(ข) พิเศษ', '18-186', 'ENG1818601', 'CHS1818601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (33, 18, '18-187', '18-187-ม.1(ข) พิเศษ', '18-187', 'ENG1818701', 'CHS1818701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (34, 18, '18-188', '18-188-ม.1(ข) พิเศษ', '18-188', 'ENG1818801', 'CHS1818801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (35, 18, '18-193', '18-193-ม.1(ข) พิเศษ', '18-193', 'ENG1819301', 'CHS1819301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (36, 18, '18-194', '18-194-ม.1(ข) พิเศษ', '18-194', 'ENG1819401', 'CHS1819401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (37, 18, '18-195', '18-195-ม.1(ข) พิเศษ', '18-195', 'ENG1819501', 'CHS1819501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (38, 18, '18-196', '18-196-ม.1(ข) พิเศษ', '18-196', 'ENG1819601', 'CHS1819601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (39, 20, '20-1', '20-1-ม.1ก', '20-1', 'ENG20101', 'CHS20101', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (40, 20, '20-2', '20-2-ม.1ก', '20-2', 'ENG20201', 'CHS20201', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (41, 20, '20-3', '20-3-ม.1ก', '20-3', 'ENG20301', 'CHS20301', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (42, 20, '20-4', '20-4-ม.1ก', '20-4', 'ENG20401', 'CHS20401', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (43, 20, '20-5', '20-5-ม.1ก', '20-5', 'ENG20501', 'CHS20501', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (44, 20, '20-6', '20-6-ม.1ก', '20-6', 'ENG20601', 'CHS20601', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (45, 20, '20-7', '20-7-ม.1ก', '20-7', 'ENG20701', 'CHS20701', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (46, 20, '20-8', '20-8-ม.1ก', '20-8', 'ENG20801', 'CHS20801', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (47, 20, '20-1', '20-1-ม.1(ข) พิเศษ', '20-1', 'ENG20101', 'CHS20101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (48, 20, '20-2', '20-2-ม.1(ข) พิเศษ', '20-2', 'ENG20201', 'CHS20201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (49, 20, '20-3', '20-3-ม.1(ข) พิเศษ', '20-3', 'ENG20301', 'CHS20301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (50, 20, '20-4', '20-4-ม.1(ข) พิเศษ', '20-4', 'ENG20401', 'CHS20401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (51, 20, '20-5', '20-5-ม.1(ข) พิเศษ', '20-5', 'ENG20501', 'CHS20501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (52, 20, '20-6', '20-6-ม.1(ข) พิเศษ', '20-6', 'ENG20601', 'CHS20601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (53, 20, '20-7', '20-7-ม.1(ข) พิเศษ', '20-7', 'ENG20701', 'CHS20701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (54, 20, '20-8', '20-8-ม.1(ข) พิเศษ', '20-8', 'ENG20801', 'CHS20801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (55, 20, '20-9', '20-9-ม.1(ข) พิเศษ', '20-9', 'ENG20901', 'CHS20901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (56, 20, '20-10', '20-10-ม.1(ข) พิเศษ', '20-10', 'ENG201001', 'CHS201001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (57, 20, '20-11', '20-11-ม.1(ข) พิเศษ', '20-11', 'ENG201101', 'CHS201101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (58, 20, '20-12', '20-12-ม.1(ข) พิเศษ', '20-12', 'ENG201201', 'CHS201201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (59, 20, '20-13', '20-13-ม.1(ข) พิเศษ', '20-13', 'ENG201301', 'CHS201301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (60, 20, '20-14', '20-14-ม.1(ข) พิเศษ', '20-14', 'ENG201401', 'CHS201401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (61, 20, '20-15', '20-15-ม.1(ข) พิเศษ', '20-15', 'ENG201501', 'CHS201501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (62, 20, '20-64', '20-64-ม.1(ข) พิเศษ', '20-64', 'ENG206401', 'CHS206401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (63, 20, '20-91', '20-91-ม.1(ข) พิเศษ', '20-91', 'ENG209101', 'CHS209101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (64, 20, '20-92', '20-92-ม.1(ข) พิเศษ', '20-92', 'ENG209201', 'CHS209201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (65, 20, '20-93', '20-93-ม.1(ข) พิเศษ', '20-93', 'ENG209301', 'CHS209301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (66, 20, '20-94', '20-94-ม.1(ข) พิเศษ', '20-94', 'ENG209401', 'CHS209401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (67, 20, '20-95', '20-95-ม.1(ข) พิเศษ', '20-95', 'ENG209501', 'CHS209501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (68, 20, '20-96', '20-96-ม.1(ข) พิเศษ', '20-96', 'ENG209601', 'CHS209601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (69, 20, '20-107', '20-107-ม.1(ข) พิเศษ', '20-107', 'ENG2010701', 'CHS2010701', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (70, 20, '20-108', '20-108-ม.1(ข) พิเศษ', '20-108', 'ENG2010801', 'CHS2010801', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (71, 20, '20-109', '20-109-ม.1(ข) พิเศษ', '20-109', 'ENG2010901', 'CHS2010901', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (72, 20, '20-110', '20-110-ม.1(ข) พิเศษ', '20-110', 'ENG2011001', 'CHS2011001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (73, 20, '20-111', '20-111-ม.1(ข) พิเศษ', '20-111', 'ENG2011101', 'CHS2011101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (74, 20, '20-129', '20-129-ม.1(ข) พิเศษ', '20-129', 'ENG2012901', 'CHS2012901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (75, 20, '20-130', '20-130-ม.1(ข) พิเศษ', '20-130', 'ENG2013001', 'CHS2013001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (76, 20, '20-131', '20-131-ม.1(ข) พิเศษ', '20-131', 'ENG2013101', 'CHS2013101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (77, 20, '20-132', '20-132-ม.1(ข) พิเศษ', '20-132', 'ENG2013201', 'CHS2013201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (78, 20, '20-133', '20-133-ม.1(ข) พิเศษ', '20-133', 'ENG2013301', 'CHS2013301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (79, 20, '20-134', '20-134-ม.1(ข) พิเศษ', '20-134', 'ENG2013401', 'CHS2013401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (80, 20, '20-135', '20-135-ม.1(ข) พิเศษ', '20-135', 'ENG2013501', 'CHS2013501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (81, 20, '20-136', '20-136-ม.1(ข) พิเศษ', '20-136', 'ENG2013601', 'CHS2013601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (82, 20, '20-137', '20-137-ม.1(ข) พิเศษ', '20-137', 'ENG2013701', 'CHS2013701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (83, 20, '20-138', '20-138-ม.1(ข) พิเศษ', '20-138', 'ENG2013801', 'CHS2013801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (84, 20, '20-139', '20-139-ม.1(ข) พิเศษ', '20-139', 'ENG2013901', 'CHS2013901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (85, 20, '20-140', '20-140-ม.1(ข) พิเศษ', '20-140', 'ENG2014001', 'CHS2014001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (86, 20, '20-141', '20-141-ม.1(ข) พิเศษ', '20-141', 'ENG2014101', 'CHS2014101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (87, 20, '20-142', '20-142-ม.1(ข) พิเศษ', '20-142', 'ENG2014201', 'CHS2014201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (88, 20, '20-143', '20-143-ม.1(ข) พิเศษ', '20-143', 'ENG2014301', 'CHS2014301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (89, 20, '20-144', '20-144-ม.1(ข) พิเศษ', '20-144', 'ENG2014401', 'CHS2014401', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (90, 20, '20-145', '20-145-ม.1(ข) พิเศษ', '20-145', 'ENG2014501', 'CHS2014501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (91, 20, '20-146', '20-146-ม.1(ข) พิเศษ', '20-146', 'ENG2014601', 'CHS2014601', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (92, 20, '20-147', '20-147-ม.1(ข) พิเศษ', '20-147', 'ENG2014701', 'CHS2014701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (93, 20, '20-148', '20-148-ม.1(ข) พิเศษ', '20-148', 'ENG2014801', 'CHS2014801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (94, 20, '20-149', '20-149-ม.1(ข) พิเศษ', '20-149', 'ENG2014901', 'CHS2014901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (95, 20, '20-150', '20-150-ม.1(ข) พิเศษ', '20-150', 'ENG2015001', 'CHS2015001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (96, 20, '20-151', '20-151-ม.1(ข) พิเศษ', '20-151', 'ENG2015101', 'CHS2015101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (97, 20, '20-152', '20-152-ม.1(ข) พิเศษ', '20-152', 'ENG2015201', 'CHS2015201', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (98, 20, '20-153', '20-153-ม.1(ข) พิเศษ', '20-153', 'ENG2015301', 'CHS2015301', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (99, 20, '20-154', '20-154-ม.1(ข) พิเศษ', '20-154', 'ENG2015401', 'CHS2015401', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (100, 20, '20-155', '20-155-ม.1(ข) พิเศษ', '20-155', 'ENG2015501', 'CHS2015501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (101, 20, '20-156', '20-156-ม.1(ข) พิเศษ', '20-156', 'ENG2015601', 'CHS2015601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (102, 20, '20-157', '20-157-ม.1(ข) พิเศษ', '20-157', 'ENG2015701', 'CHS2015701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (103, 20, '20-158', '20-158-ม.1(ข) พิเศษ', '20-158', 'ENG2015801', 'CHS2015801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (104, 20, '20-159', '20-159-ม.1(ข) พิเศษ', '20-159', 'ENG2015901', 'CHS2015901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (105, 20, '20-160', '20-160-ม.1(ข) พิเศษ', '20-160', 'ENG2016001', 'CHS2016001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (106, 20, '20-161', '20-161-ม.1(ข) พิเศษ', '20-161', 'ENG2016101', 'CHS2016101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (107, 22, '22-1', '22-1-ม.1ก', '22-1', 'ENG22101', 'CHS22101', 1, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (108, 22, '22-2', '22-2-ม.1ก', '22-2', 'ENG22201', 'CHS22201', 1, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (109, 22, '22-3', '22-3-ม.1ก', '22-3', 'ENG22301', 'CHS22301', 1, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (110, 22, '22-4', '22-4-ม.1ก', '22-4', 'ENG22401', 'CHS22401', 1, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (111, 22, '22-5', '22-5-ม.1ก', '22-5', 'ENG22501', 'CHS22501', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (112, 22, '22-6', '22-6-ม.1ก', '22-6', 'ENG22601', 'CHS22601', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (113, 22, '22-7', '22-7-ม.1ก', '22-7', 'ENG22701', 'CHS22701', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (114, 22, '22-8', '22-8-ม.1ก', '22-8', 'ENG22801', 'CHS22801', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (115, 22, '22-9', '22-9-ม.1ก', '22-9', 'ENG22901', 'CHS22901', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (116, 22, '22-10', '22-10-ม.1ก', '22-10', 'ENG221001', 'CHS221001', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (117, 22, '22-11', '22-11-ม.1ก', '22-11', 'ENG221101', 'CHS221101', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (118, 22, '22-12', '22-12-ม.1ก', '22-12', 'ENG221201', 'CHS221201', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (119, 22, '22-1', '22-1-ม.1(ข) พิเศษ', '22-1', 'ENG22101', 'CHS22101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (120, 22, '22-2', '22-2-ม.1(ข) พิเศษ', '22-2', 'ENG22201', 'CHS22201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (121, 22, '22-3', '22-3-ม.1(ข) พิเศษ', '22-3', 'ENG22301', 'CHS22301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (122, 22, '22-4', '22-4-ม.1(ข) พิเศษ', '22-4', 'ENG22401', 'CHS22401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (123, 22, '22-5', '22-5-ม.1(ข) พิเศษ', '22-5', 'ENG22501', 'CHS22501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (124, 22, '22-6', '22-6-ม.1(ข) พิเศษ', '22-6', 'ENG22601', 'CHS22601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (125, 22, '22-7', '22-7-ม.1(ข) พิเศษ', '22-7', 'ENG22701', 'CHS22701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (126, 22, '22-8', '22-8-ม.1(ข) พิเศษ', '22-8', 'ENG22801', 'CHS22801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (127, 22, '22-9', '22-9-ม.1(ข) พิเศษ', '22-9', 'ENG22901', 'CHS22901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (128, 22, '22-10', '22-10-ม.1(ข) พิเศษ', '22-10', 'ENG221001', 'CHS221001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (129, 22, '22-11', '22-11-ม.1(ข) พิเศษ', '22-11', 'ENG221101', 'CHS221101', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (130, 22, '22-12', '22-12-ม.1(ข) พิเศษ', '22-12', 'ENG221201', 'CHS221201', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (131, 22, '22-13', '22-13-ม.1(ข) พิเศษ', '22-13', 'ENG221301', 'CHS221301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (132, 22, '22-14', '22-14-ม.1(ข) พิเศษ', '22-14', 'ENG221401', 'CHS221401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (133, 22, '22-15', '22-15-ม.1(ข) พิเศษ', '22-15', 'ENG221501', 'CHS221501', 2, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (134, 23, '23-1', '23-1-ม.1ก', NULL, NULL, NULL, 1, 1, NULL);
INSERT INTO `bus_info` VALUES (135, 23, '23-2', '23-2-ม.1ก', NULL, NULL, NULL, 1, 1, NULL);
INSERT INTO `bus_info` VALUES (136, 23, '23-1', '23-1-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (137, 23, '23-2', '23-2-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (138, 23, '23-3', '23-3-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (139, 23, '23-4', '23-4-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (140, 24, '24-49', '24-49-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (141, 24, '24-50', '24-50-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (142, 24, '24-51', '24-51-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (143, 24, '24-52', '24-52-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (144, 24, '24-53', '24-53-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (145, 24, '24-54', '24-54-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (146, 24, '24-55', '24-55-ม.1(ข) พิเศษ', NULL, NULL, NULL, 2, 1, NULL);
INSERT INTO `bus_info` VALUES (147, 26, '26-1', '26-1-ม.1(ข) พิเศษ', '26-1', 'ENG26101', 'CHS26101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (148, 26, '26-2', '26-2-ม.1(ข) พิเศษ', '26-2', 'ENG26201', 'CHS26201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (149, 26, '26-4', '26-4-ม.1(ข) พิเศษ', '26-4', 'ENG26401', 'CHS26401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (150, 26, '26-7', '26-7-ม.1(ข) พิเศษ', '26-7', 'ENG26701', 'CHS26701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (151, 26, '26-12', '26-12-ม.1(ข) พิเศษ', '26-12', 'ENG261201', 'CHS261201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (152, 26, '26-3', '26-3-ม. 1(ข)', '26-3', 'ENG26301', 'CHS26301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (153, 26, '26-5', '26-5-ม. 1(ข)', '26-5', 'ENG26501', 'CHS26501', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (154, 26, '26-6', '26-6-ม. 1(ข)', '26-6', 'ENG26601', 'CHS26601', 3, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (155, 26, '26-8', '26-8-ม. 1(ข)', '26-8', 'ENG26801', 'CHS26801', 3, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (156, 26, '26-9', '26-9-ม. 1(ข)', '26-9', 'ENG26901', 'CHS26901', 3, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (157, 26, '26-10', '26-10-ม. 1(ข)', '26-10', 'ENG261001', 'CHS261001', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (158, 26, '26-11', '26-11-ม. 1(ข)', '26-11', 'ENG261101', 'CHS261101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (159, 26, '26-13', '26-13-ม. 1(ข)', '26-13', 'ENG261301', 'CHS261301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (160, 26, '26-14', '26-14-ม. 1(ข)', '26-14', 'ENG261401', 'CHS261401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (161, 27, '27-12', '27-12-ม.1(ข) พิเศษ', '27-12', 'ENG271201', 'CHS271201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (162, 27, '27-15', '27-15-ม.1(ข) พิเศษ', '27-15', 'ENG271501', 'CHS271501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (163, 27, '27-16', '27-16-ม.1(ข) พิเศษ', '27-16', 'ENG271601', 'CHS271601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (164, 27, '27-17', '27-17-ม.1(ข) พิเศษ', '27-17', 'ENG271701', 'CHS271701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (165, 27, '27-18', '27-18-ม.1(ข) พิเศษ', '27-18', 'ENG271801', 'CHS271801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (166, 27, '27-19', '27-19-ม.1(ข) พิเศษ', '27-19', 'ENG271901', 'CHS271901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (167, 27, '27-20', '27-20-ม.1(ข) พิเศษ', '27-20', 'ENG272001', 'CHS272001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (168, 27, '27-21', '27-21-ม.1(ข) พิเศษ', '27-21', 'ENG272101', 'CHS272101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (169, 27, '27-22', '27-22-ม.1(ข) พิเศษ', '27-22', 'ENG272201', 'CHS272201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (170, 27, '27-23', '27-23-ม.1(ข) พิเศษ', '27-23', 'ENG272301', 'CHS272301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (171, 27, '27-24', '27-24-ม.1(ข) พิเศษ', '27-24', 'ENG272401', 'CHS272401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (172, 27, '27-1', '27-1-ม. 1(ข)', '27-1', 'ENG27101', 'CHS27101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (173, 27, '27-2', '27-2-ม. 1(ข)', '27-2', 'ENG27201', 'CHS27201', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (174, 27, '27-3', '27-3-ม. 1(ข)', '27-3', 'ENG27301', 'CHS27301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (175, 27, '27-10', '27-10-ม. 1(ข)', '27-10', 'ENG271001', 'CHS271001', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (176, 27, '27-11', '27-11-ม. 1(ข)', '27-11', 'ENG271101', 'CHS271101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (177, 30, '30-10', '30-10-ม.1(ข) พิเศษ', '30-10', 'ENG301001', 'CHS301001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (178, 30, '30-11', '30-11-ม.1(ข) พิเศษ', '30-11', 'ENG301101', 'CHS301101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (179, 30, '30-12', '30-12-ม.1(ข) พิเศษ', '30-12', 'ENG301201', 'CHS301201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (180, 30, '30-13', '30-13-ม.1(ข) พิเศษ', '30-13', 'ENG301301', 'CHS301301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (181, 30, '30-14', '30-14-ม.1(ข) พิเศษ', '30-14', 'ENG301401', 'CHS301401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (182, 30, '30-15', '30-15-ม.1(ข) พิเศษ', '30-15', 'ENG301501', 'CHS301501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (183, 30, '30-16', '30-16-ม.1(ข) พิเศษ', '30-16', 'ENG301601', 'CHS301601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (184, 30, '30-17', '30-17-ม.1(ข) พิเศษ', '30-17', 'ENG301701', 'CHS301701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (185, 30, '30-18', '30-18-ม.1(ข) พิเศษ', '30-18', 'ENG301801', 'CHS301801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (186, 30, '30-19', '30-19-ม.1(ข) พิเศษ', '30-19', 'ENG301901', 'CHS301901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (187, 30, '30-20', '30-20-ม.1(ข) พิเศษ', '30-20', 'ENG302001', 'CHS302001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (188, 30, '30-21', '30-21-ม.1(ข) พิเศษ', '30-21', 'ENG302101', 'CHS302101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (189, 31, '31-1', '31-1-ม.1ก', '31-1', 'ENG31101', 'CHS31101', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (190, 31, '31-2', '31-2-ม.1ก', '31-2', 'ENG31201', 'CHS31201', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (191, 31, '31-3', '31-3-ม.1ก', '31-3', 'ENG31301', 'CHS31301', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (192, 31, '31-24', '31-24-ม.1(ข) พิเศษ', '31-24', 'ENG312401', 'CHS312401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (193, 31, '31-32', '31-32-ม.1(ข) พิเศษ', '31-32', 'ENG313201', 'CHS313201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (194, 31, '31-33', '31-33-ม.1(ข) พิเศษ', '31-33', 'ENG313301', 'CHS313301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (195, 31, '31-34', '31-34-ม.1(ข) พิเศษ', '31-34', 'ENG313401', 'CHS313401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (196, 31, '31-35', '31-35-ม.1(ข) พิเศษ', '31-35', 'ENG313501', 'CHS313501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (197, 31, '31-36', '31-36-ม.1(ข) พิเศษ', '31-36', 'ENG313601', 'CHS313601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (198, 31, '31-37', '31-37-ม.1(ข) พิเศษ', '31-37', 'ENG313701', 'CHS313701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (199, 31, '31-38', '31-38-ม.1(ข) พิเศษ', '31-38', 'ENG313801', 'CHS313801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (200, 31, '31-39', '31-39-ม.1(ข) พิเศษ', '31-39', 'ENG313901', 'CHS313901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (201, 31, '31-40', '31-40-ม.1(ข) พิเศษ', '31-40', 'ENG314001', 'CHS314001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (202, 31, '31-41', '31-41-ม.1(ข) พิเศษ', '31-41', 'ENG314101', 'CHS314101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (203, 31, '31-42', '31-42-ม.1(ข) พิเศษ', '31-42', 'ENG314201', 'CHS314201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (244, 912, '912-1', '912-1-ม.1(ข) พิเศษ', '912-1', 'ENG912101', 'CHS912101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (245, 912, '912-2', '912-2-ม.1(ข) พิเศษ', '912-2', 'ENG912201', 'CHS912201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (246, 912, '912-3', '912-3-ม.1(ข) พิเศษ', '912-3', 'ENG912301', 'CHS912301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (247, 912, '912-4', '912-4-ม.1(ข) พิเศษ', '912-4', 'ENG912401', 'CHS912401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (248, 912, '912-5', '912-5-ม.1(ข) พิเศษ', '912-5', 'ENG912501', 'CHS912501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (249, 912, '912-6', '912-6-ม.1(ข) พิเศษ', '912-6', 'ENG912601', 'CHS912601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (250, 923, '923-1', '923-1-ม.1(ข) พิเศษ', '923-1', 'ENG923101', 'CHS923101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (251, 923, '923-2', '923-2-ม.1(ข) พิเศษ', '923-2', 'ENG923201', 'CHS923201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (252, 923, '923-3', '923-3-ม.1(ข) พิเศษ', '923-3', 'ENG923301', 'CHS923301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (253, 926, '926-1', '926-1-ม.1(ข) พิเศษ', '926-1', 'ENG926101', 'CHS926101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (254, 926, '926-2', '926-2-ม.1(ข) พิเศษ', '926-2', 'ENG926201', 'CHS926201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (255, 926, '926-3', '926-3-ม.1(ข) พิเศษ', '926-3', 'ENG926301', 'CHS926301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (256, 926, '926-4', '926-4-ม.1(ข) พิเศษ', '926-4', 'ENG926401', 'CHS926401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (257, 926, '926-5', '926-5-ม.1(ข) พิเศษ', '926-5', 'ENG926501', 'CHS926501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (258, 926, '926-6', '926-6-ม.1(ข) พิเศษ', '926-6', 'ENG926601', 'CHS926601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (259, 926, '926-7', '926-7-ม.1(ข) พิเศษ', '926-7', 'ENG926701', 'CHS926701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (260, 926, '926-8', '926-8-ม.1(ข) พิเศษ', '926-8', 'ENG926801', 'CHS926801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (261, 926, '926-9', '926-9-ม.1(ข) พิเศษ', '926-9', 'ENG926901', 'CHS926901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (262, 926, '926-10', '926-10-ม.1(ข) พิเศษ', '926-10', 'ENG9261001', 'CHS9261001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (263, 926, '926-11', '926-11-ม.1(ข) พิเศษ', '926-11', 'ENG9261101', 'CHS9261101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (264, 927, '927-1', '927-1-ม.1(ข) พิเศษ', '927-1', 'ENG927101', 'CHS927101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (265, 927, '927-2', '927-2-ม.1(ข) พิเศษ', '927-2', 'ENG927201', 'CHS927201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (266, 927, '927-3', '927-3-ม.1(ข) พิเศษ', '927-3', 'ENG927301', 'CHS927301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (267, 927, '927-4', '927-4-ม.1(ข) พิเศษ', '927-4', 'ENG927401', 'CHS927401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (268, 927, '927-5', '927-5-ม.1(ข) พิเศษ', '927-5', 'ENG927501', 'CHS927501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (269, 929, '929-4', '929-4-ม.1(ข) พิเศษ', '929-4', 'ENG929401', 'CHS929401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (270, 929, '929-5', '929-5-ม.1(ข) พิเศษ', '929-5', 'ENG929501', 'CHS929501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (271, 929, '929-6', '929-6-ม.1(ข) พิเศษ', '929-6', 'ENG929601', 'CHS929601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (272, 929, '929-7', '929-7-ม.1(ข) พิเศษ', '929-7', 'ENG929701', 'CHS929701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (273, 929, '929-8', '929-8-ม.1(ข) พิเศษ', '929-8', 'ENG929801', 'CHS929801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (274, 929, '929-9', '929-9-ม.1(ข) พิเศษ', '929-9', 'ENG929901', 'CHS929901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (275, 929, '929-11', '929-11-ม.1(ข) พิเศษ', '929-11', 'ENG9291101', 'CHS9291101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (276, 929, '929-12', '929-12-ม.1(ข) พิเศษ', '929-12', 'ENG9291201', 'CHS9291201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (277, 932, '932-2', '932-2-ม.1(ข) พิเศษ', '932-2', 'ENG932201', 'CHS932201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (278, 932, '932-4', '932-4-ม.1(ข) พิเศษ', '932-4', 'ENG932401', 'CHS932401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (279, 587, '587-101', '587-101-ม.1(ข) พิเศษ', '587-101', 'ENG58710101', 'CHS58710101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (280, 587, '587-102', '587-102-ม.1(ข) พิเศษ', '587-102', 'ENG58710201', 'CHS58710201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (281, 587, '587-103', '587-103-ม.1(ข) พิเศษ', '587-103', 'ENG58710301', 'CHS58710301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (282, 587, '587-131', '587-131-ม.1(ข) พิเศษ', '587-131', 'ENG58713101', 'CHS58713101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (283, 587, '587-132', '587-132-ม.1(ข) พิเศษ', '587-132', 'ENG58713201', 'CHS58713201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (284, 587, '587-133', '587-133-ม.1(ข) พิเศษ', '587-133', 'ENG58713301', 'CHS58713301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (285, 587, '587-134', '587-134-ม.1(ข) พิเศษ', '587-134', 'ENG58713401', 'CHS58713401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (286, 587, '587-135', '587-135-ม.1(ข) พิเศษ', '587-135', 'ENG58713501', 'CHS58713501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (287, 587, '587-136', '587-136-ม.1(ข) พิเศษ', '587-136', 'ENG58713601', 'CHS58713601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (288, 587, '587-137', '587-137-ม.1(ข) พิเศษ', '587-137', 'ENG58713701', 'CHS58713701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (289, 587, '587-138', '587-138-ม.1(ข) พิเศษ', '587-138', 'ENG58713801', 'CHS58713801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (290, 587, '587-139', '587-139-ม.1(ข) พิเศษ', '587-139', 'ENG58713901', 'CHS58713901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (291, 587, '587-140', '587-140-ม.1(ข) พิเศษ', '587-140', 'ENG58714001', 'CHS58714001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (292, 587, '587-141', '587-141-ม.1(ข) พิเศษ', '587-141', 'ENG58714101', 'CHS58714101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (293, 587, '587-142', '587-142-ม.1(ข) พิเศษ', '587-142', 'ENG58714201', 'CHS58714201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (294, 587, '587-143', '587-143-ม.1(ข) พิเศษ', '587-143', 'ENG58714301', 'CHS58714301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (295, 587, '587-144', '587-144-ม.1(ข) พิเศษ', '587-144', 'ENG58714401', 'CHS58714401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (296, 587, '587-145', '587-145-ม.1(ข) พิเศษ', '587-145', 'ENG58714501', 'CHS58714501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (297, 587, '587-146', '587-146-ม.1(ข) พิเศษ', '587-146', 'ENG58714601', 'CHS58714601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (298, 588, '588-271', '588-271-ม.1(ข) พิเศษ', '588-271', 'ENG58827101', 'CHS58827101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (299, 588, '588-272', '588-272-ม.1(ข) พิเศษ', '588-272', 'ENG58827201', 'CHS58827201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (300, 588, '588-273', '588-273-ม.1(ข) พิเศษ', '588-273', 'ENG58827301', 'CHS58827301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (301, 588, '588-274', '588-274-ม.1(ข) พิเศษ', '588-274', 'ENG58827401', 'CHS58827401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (302, 588, '588-275', '588-275-ม.1(ข) พิเศษ', '588-275', 'ENG58827501', 'CHS58827501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (303, 588, '588-276', '588-276-ม.1(ข) พิเศษ', '588-276', 'ENG58827601', 'CHS58827601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (304, 588, '588-201', '588-201-ม. 1(ข)', '588-201', 'ENG58820101', 'CHS58820101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (305, 588, '588-202', '588-202-ม. 1(ข)', '588-202', 'ENG58820201', 'CHS58820201', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (306, 588, '588-203', '588-203-ม. 1(ข)', '588-203', 'ENG58820301', 'CHS58820301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (307, 588, '588-204', '588-204-ม. 1(ข)', '588-204', 'ENG58820401', 'CHS58820401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (308, 588, '588-205', '588-205-ม. 1(ข)', '588-205', 'ENG58820501', 'CHS58820501', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (309, 588, '588-231', '588-231-ม.1(ข) พิเศษ', '588-231', 'ENG58823101', 'CHS58823101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (310, 588, '588-232', '588-232-ม.1(ข) พิเศษ', '588-232', 'ENG58823201', 'CHS58823201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (311, 588, '588-233', '588-233-ม.1(ข) พิเศษ', '588-233', 'ENG58823301', 'CHS58823301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (312, 588, '588-234', '588-234-ม.1(ข) พิเศษ', '588-234', 'ENG58823401', 'CHS58823401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (313, 588, '588-235', '588-235-ม.1(ข) พิเศษ', '588-235', 'ENG58823501', 'CHS58823501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (314, 588, '588-236', '588-236-ม.1(ข) พิเศษ', '588-236', 'ENG58823601', 'CHS58823601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (315, 588, '588-237', '588-237-ม.1(ข) พิเศษ', '588-237', 'ENG58823701', 'CHS58823701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (316, 588, '588-238', '588-238-ม.1(ข) พิเศษ', '588-238', 'ENG58823801', 'CHS58823801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (317, 588, '588-239', '588-239-ม.1(ข) พิเศษ', '588-239', 'ENG58823901', 'CHS58823901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (318, 588, '588-240', '588-240-ม.1(ข) พิเศษ', '588-240', 'ENG58824001', 'CHS58824001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (319, 588, '588-241', '588-241-ม.1(ข) พิเศษ', '588-241', 'ENG58824101', 'CHS58824101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (320, 588, '588-242', '588-242-ม.1(ข) พิเศษ', '588-242', 'ENG58824201', 'CHS58824201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (321, 588, '588-243', '588-243-ม.1(ข) พิเศษ', '588-243', 'ENG58824301', 'CHS58824301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (322, 588, '588-244', '588-244-ม.1(ข) พิเศษ', '588-244', 'ENG58824401', 'CHS58824401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (323, 588, '588-245', '588-245-ม.1(ข) พิเศษ', '588-245', 'ENG58824501', 'CHS58824501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (324, 588, '588-246', '588-246-ม.1(ข) พิเศษ', '588-246', 'ENG58824601', 'CHS58824601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (325, 588, '588-247', '588-247-ม.1(ข) พิเศษ', '588-247', 'ENG58824701', 'CHS58824701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (326, 659, '659-381', '659-381-ม.1ก', '659-381', 'ENG65938101', 'CHS65938101', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (327, 659, '659-382', '659-382-ม.1ก', '659-382', 'ENG65938201', 'CHS65938201', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (328, 659, '659-383', '659-383-ม.1ก', '659-383', 'ENG65938301', 'CHS65938301', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (329, 659, '659-301', '659-301-ม. 1(ข)', '659-301', 'ENG65930101', 'CHS65930101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (330, 659, '659-302', '659-302-ม. 1(ข)', '659-302', 'ENG65930201', 'CHS65930201', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (331, 659, '659-303', '659-303-ม. 1(ข)', '659-303', 'ENG65930301', 'CHS65930301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (332, 659, '659-304', '659-304-ม. 1(ข)', '659-304', 'ENG65930401', 'CHS65930401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (333, 659, '659-305', '659-305-ม. 1(ข)', '659-305', 'ENG65930501', 'CHS65930501', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (334, 659, '659-331', '659-331-ม.1(ข) พิเศษ', '659-331', 'ENG65933101', 'CHS65933101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (335, 659, '659-332', '659-332-ม.1(ข) พิเศษ', '659-332', 'ENG65933201', 'CHS65933201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (336, 659, '659-333', '659-333-ม.1(ข) พิเศษ', '659-333', 'ENG65933301', 'CHS65933301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (337, 659, '659-334', '659-334-ม.1(ข) พิเศษ', '659-334', 'ENG65933401', 'CHS65933401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (338, 659, '659-335', '659-335-ม.1(ข) พิเศษ', '659-335', 'ENG65933501', 'CHS65933501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (339, 659, '659-336', '659-336-ม.1(ข) พิเศษ', '659-336', 'ENG65933601', 'CHS65933601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (340, 659, '659-337', '659-337-ม.1(ข) พิเศษ', '659-337', 'ENG65933701', 'CHS65933701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (341, 659, '659-338', '659-338-ม.1(ข) พิเศษ', '659-338', 'ENG65933801', 'CHS65933801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (342, 659, '659-339', '659-339-ม.1(ข) พิเศษ', '659-339', 'ENG65933901', 'CHS65933901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (343, 659, '659-340', '659-340-ม.1(ข) พิเศษ', '659-340', 'ENG65934001', 'CHS65934001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (344, 659, '659-341', '659-341-ม.1(ข) พิเศษ', '659-341', 'ENG65934101', 'CHS65934101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (345, 659, '659-342', '659-342-ม.1(ข) พิเศษ', '659-342', 'ENG65934201', 'CHS65934201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (346, 659, '659-343', '659-343-ม.1(ข) พิเศษ', '659-343', 'ENG65934301', 'CHS65934301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (347, 659, '659-344', '659-344-ม.1(ข) พิเศษ', '659-344', 'ENG65934401', 'CHS65934401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (348, 659, '659-345', '659-345-ม.1(ข) พิเศษ', '659-345', 'ENG65934501', 'CHS65934501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (349, 659, '659-346', '659-346-ม.1(ข) พิเศษ', '659-346', 'ENG65934601', 'CHS65934601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (350, 659, '659-347', '659-347-ม.1(ข) พิเศษ', '659-347', 'ENG65934701', 'CHS65934701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (351, 659, '659-348', '659-348-ม.1(ข) พิเศษ', '659-348', 'ENG65934801', 'CHS65934801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (352, 660, '660-581', '660-581-ม.1ก', '660-581', 'ENG66058101', 'CHS66058101', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (353, 660, '660-582', '660-582-ม.1ก', '660-582', 'ENG66058201', 'CHS66058201', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (354, 660, '660-583', '660-583-ม.1ก', '660-583', 'ENG66058301', 'CHS66058301', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (355, 660, '660-501', '660-501-ม. 1(ข)', '660-501', 'ENG66050101', 'CHS66050101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (356, 660, '660-502', '660-502-ม. 1(ข)', '660-502', 'ENG66050201', 'CHS66050201', 3, 0, NULL);
INSERT INTO `bus_info` VALUES (357, 660, '660-503', '660-503-ม. 1(ข)', '660-503', 'ENG66050301', 'CHS66050301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (358, 660, '660-531', '660-531-ม.1(ข) พิเศษ', '660-531', 'ENG66053101', 'CHS66053101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (359, 660, '660-532', '660-532-ม.1(ข) พิเศษ', '660-532', 'ENG66053201', 'CHS66053201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (360, 660, '660-533', '660-533-ม.1(ข) พิเศษ', '660-533', 'ENG66053301', 'CHS66053301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (361, 660, '660-534', '660-534-ม.1(ข) พิเศษ', '660-534', 'ENG66053401', 'CHS66053401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (362, 660, '660-535', '660-535-ม.1(ข) พิเศษ', '660-535', 'ENG66053501', 'CHS66053501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (363, 660, '660-536', '660-536-ม.1(ข) พิเศษ', '660-536', 'ENG66053601', 'CHS66053601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (364, 660, '660-537', '660-537-ม.1(ข) พิเศษ', '660-537', 'ENG66053701', 'CHS66053701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (365, 660, '660-541', '660-541-ม.1(ข) พิเศษ', '660-541', 'ENG66054101', 'CHS66054101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (366, 660, '660-542', '660-542-ม.1(ข) พิเศษ', '660-542', 'ENG66054201', 'CHS66054201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (367, 660, '660-543', '660-543-ม.1(ข) พิเศษ', '660-543', 'ENG66054301', 'CHS66054301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (368, 660, '660-544', '660-544-ม.1(ข) พิเศษ', '660-544', 'ENG66054401', 'CHS66054401', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (369, 660, '660-545', '660-545-ม.1(ข) พิเศษ', '660-545', 'ENG66054501', 'CHS66054501', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (370, 32, '32-1', '32-1-ม.1ก', '32-1', 'ENG32101', 'CHS32101', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (371, 32, '32-2', '32-2-ม.1ก', '32-2', 'ENG32201', 'CHS32201', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (372, 32, '32-3', '32-3-ม.1ก', '32-3', 'ENG32301', 'CHS32301', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (373, 32, '32-2', '32-2-ม.1(ข) พิเศษ', '32-2', 'ENG32201', 'CHS32201', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (374, 32, '32-3', '32-3-ม.1(ข) พิเศษ', '32-3', 'ENG32301', 'CHS32301', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (375, 32, '32-6', '32-6-ม.1(ข) พิเศษ', '32-6', 'ENG32601', 'CHS32601', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (376, 32, '32-8', '32-8-ม.1(ข) พิเศษ', '32-8', 'ENG32801', 'CHS32801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (377, 32, '32-9', '32-9-ม.1(ข) พิเศษ', '32-9', 'ENG32901', 'CHS32901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (378, 32, '32-10', '32-10-ม.1(ข) พิเศษ', '32-10', 'ENG321001', 'CHS321001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (379, 32, '32-11', '32-11-ม.1(ข) พิเศษ', '32-11', 'ENG321101', 'CHS321101', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (380, 32, '32-17', '32-17-ม.1(ข) พิเศษ', '32-17', 'ENG321701', 'CHS321701', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (381, 32, '32-18', '32-18-ม.1(ข) พิเศษ', '32-18', 'ENG321801', 'CHS321801', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (382, 32, '32-19', '32-19-ม.1(ข) พิเศษ', '32-19', 'ENG321901', 'CHS321901', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (383, 32, '32-20', '32-20-ม.1(ข) พิเศษ', '32-20', 'ENG322001', 'CHS322001', 2, 1, NULL);
INSERT INTO `bus_info` VALUES (384, 32, '32-21', '32-21-ม.1(ข) พิเศษ', '32-21', 'ENG322101', 'CHS322101', 2, 0, NULL);
INSERT INTO `bus_info` VALUES (385, 32, '32-12', '32-12-ม. 1(ข)', '32-12', 'ENG321201', 'CHS321201', 3, 0, '32-4/ม.1ก');
INSERT INTO `bus_info` VALUES (386, 32, '32-19', '32-19-ม. 1(ข)', '32-19', 'ENG321901', 'CHS321901', 3, 0, '32-5ม.1ก');
INSERT INTO `bus_info` VALUES (387, 32, '32-20', '32-20-ม. 1(ข)', '32-20', 'ENG322001', 'CHS322001', 3, 0, '32-6/ม.1ก');
INSERT INTO `bus_info` VALUES (388, 32, '32-21', '32-21-ม. 1(ข)', '32-21', 'ENG322101', 'CHS322101', 3, 0, '32-7/ม.1ก');
INSERT INTO `bus_info` VALUES (389, 936, '936-13', '936-13-ม. 1(ข)', '936-13', 'ENG9361301', 'CHS9361301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (390, 936, '936-14', '936-14-ม. 1(ข)', '936-14', 'ENG9361401', 'CHS9361401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (391, 936, '936-15', '936-15-ม. 1(ข)', '936-15', 'ENG9361501', 'CHS9361501', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (392, 34, '34-1', '34-1-ม. 1(ข)', '34-1', 'ENG34101', 'CHS34101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (393, 34, '34-2', '34-2-ม. 1(ข)', '34-2', 'ENG34201', 'CHS34201', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (394, 34, '34-3', '34-3-ม. 1(ข)', '34-3', 'ENG34301', 'CHS34301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (395, 34, '34-4', '34-4-ม. 1(ข)', '34-4', 'ENG34401', 'CHS34401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (396, 34, '34-5', '34-5-ม.2', '34-5', 'ENG34501', 'CHS34501', 5, 1, NULL);
INSERT INTO `bus_info` VALUES (397, 34, '34-6', '34-6-ม. 1(ข)', '34-6', 'ENG34601', 'CHS34601', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (398, 34, '34-7', '34-7-ม. 1(ข)', '34-7', 'ENG34701', 'CHS34701', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (399, 34, '34-8', '34-8-ม. 1(ข)', '34-8', 'ENG34801', 'CHS34801', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (400, 34, '34-9', '34-9-ม. 1(ข)', '34-9', 'ENG34901', 'CHS34901', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (401, 34, '34-10', '34-10-ม. 1(ข)', '34-10', 'ENG341001', 'CHS341001', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (402, 34, '34-5', '34-5-ม.2', '34-5', 'ENG34501', 'CHS34501', 5, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (403, 34, '34-6', '34-6-ม.2', '34-6', 'ENG34601', 'CHS34601', 5, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (404, 90, '90-4', '90-4-ม. 1(ข)', '90-4', 'ENG90401', 'CHS90401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (405, 90, '90-5', '90-5-ม. 1(ข)', '90-5', 'ENG90501', 'CHS90501', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (406, 90, '90-6', '90-6-ม. 1(ข)', '90-6', 'ENG90601', 'CHS90601', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (407, 90, '90-7', '90-7-ม. 1(ข)', '90-7', 'ENG90701', 'CHS90701', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (408, 91, '91-5', '91-5-ม.1ก', '91-5', 'ENG91501', 'CHS91501', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (409, 91, '91-6', '91-6-ม.1ก', '91-6', 'ENG91601', 'CHS91601', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (410, 910, '910-1', '910-1-ม. 1(ข)', '910-1', 'ENG910101', 'CHS910101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (411, 910, '910-2', '910-2-ม. 1(ข)', '910-2', 'ENG910201', 'CHS910201', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (412, 910, '910-3', '910-3-ม. 1(ข)', '910-3', 'ENG910301', 'CHS910301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (413, 910, '910-4', '910-4-ม. 1(ข)', '910-4', 'ENG910401', 'CHS910401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (414, 98, '98-1', '98-1-ม.1ก', '98-1', 'ENG98101', 'CHS98101', 1, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (415, 98, '98-2', '98-2-ม.1ก', '98-2', 'ENG98201', 'CHS98201', 1, 0, 'ลอยเบอร์');
INSERT INTO `bus_info` VALUES (416, 98, '98-3', '98-3-ม.1ก', '98-3', 'ENG98301', 'CHS98301', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (417, 98, '98-4', '98-4-ม.1ก', '98-4', 'ENG98401', 'CHS98401', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (418, 98, '98-5', '98-5-ม.1ก', '98-5', 'ENG98501', 'CHS98501', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (419, 98, '98-6', '98-6-ม.1ก', '98-6', 'ENG98601', 'CHS98601', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (420, 98, '98-7', '98-7-ม.1ก', '98-7', 'ENG98701', 'CHS98701', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (421, 98, '98-8', '98-8-ม.1ก', '98-8', 'ENG98801', 'CHS98801', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (422, 98, '98-9', '98-9-ม.1ก', '98-9', 'ENG98901', 'CHS98901', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (423, 98, '98-10', '98-10-ม.1ก', '98-10', 'ENG981001', 'CHS981001', 1, 1, NULL);
INSERT INTO `bus_info` VALUES (424, 98, '98-1', '98-1-ม. 1(ข)', '98-1', 'ENG98101', 'CHS98101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (425, 98, '98-2', '98-2-ม. 1(ข)', '98-2', 'ENG98201', 'CHS98201', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (426, 98, '98-3', '98-3-ม. 1(ข)', '98-3', 'ENG98301', 'CHS98301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (427, 98, '98-4', '98-4-ม. 1(ข)', '98-4', 'ENG98401', 'CHS98401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (428, 98, '98-5', '98-5-ม. 1(ข)', '98-5', 'ENG98501', 'CHS98501', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (429, 98, '98-6', '98-6-ม. 1(ข)', '98-6', 'ENG98601', 'CHS98601', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (430, 98, '98-7', '98-7-ม. 1(ข)', '98-7', 'ENG98701', 'CHS98701', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (431, 98, '98-8', '98-8-ม. 1(ข)', '98-8', 'ENG98801', 'CHS98801', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (432, 98, '98-9', '98-9-ม. 1(ข)', '98-9', 'ENG98901', 'CHS98901', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (433, 98, '98-20', '98-20-ม. 1(ข)', '98-20', 'ENG982001', 'CHS982001', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (434, 98, '98-24', '98-24-ม. 1(ข)', '98-24', 'ENG982401', 'CHS982401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (435, 98, '98-29', '98-29-ม. 1(ข)', '98-29', 'ENG982901', 'CHS982901', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (436, 98, '98-30', '98-30-ม. 1(ข)', '98-30', 'ENG983001', 'CHS983001', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (437, 98, '98-33', '98-33-ม. 1(ข)', '98-33', 'ENG983301', 'CHS983301', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (438, 98, '98-44', '98-44-ม. 1(ข)', '98-44', 'ENG984401', 'CHS984401', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (439, 98, '98-45', '98-45-ม. 1(ข)', '98-45', 'ENG984501', 'CHS984501', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (440, 98, '98-46', '98-46-ม. 1(ข)', '98-46', 'ENG984601', 'CHS984601', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (441, 98, '98-47', '98-47-ม. 1(ข)', '98-47', 'ENG984701', 'CHS984701', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (442, 98, '98-48', '98-48-ม. 1(ข)', '98-48', 'ENG984801', 'CHS984801', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (443, 98, '98-49', '98-49-ม. 1(ข)', '98-49', 'ENG984901', 'CHS984901', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (444, 98, '98-50', '98-50-ม. 1(ข)', '98-50', 'ENG985001', 'CHS985001', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (445, 98, '98-51', '98-51-ม. 1(ข)', '98-51', 'ENG985101', 'CHS985101', 3, 1, NULL);
INSERT INTO `bus_info` VALUES (446, 98, '98-52', '98-52-ม. 1(ข)', '98-52', 'ENG985201', 'CHS985201', 3, 1, NULL);

-- ----------------------------
-- Table structure for bus_type
-- ----------------------------
DROP TABLE IF EXISTS `bus_type`;
CREATE TABLE `bus_type`  (
  `bt_id` int(11) NOT NULL,
  `bt_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`bt_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bus_type
-- ----------------------------
INSERT INTO `bus_type` VALUES (1, 'ม.1ก');
INSERT INTO `bus_type` VALUES (2, 'ม.1(ข) พิเศษ');
INSERT INTO `bus_type` VALUES (3, 'ม. 1(ข)');
INSERT INTO `bus_type` VALUES (4, 'ม.4(ข)');
INSERT INTO `bus_type` VALUES (5, 'ม.2');

-- ----------------------------
-- Table structure for location
-- ----------------------------
DROP TABLE IF EXISTS `location`;
CREATE TABLE `location`  (
  `locat_id` int(11) NOT NULL AUTO_INCREMENT,
  `locat_name_th` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`locat_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 51 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of location
-- ----------------------------
INSERT INTO `location` VALUES (1, 'กรุงเทพฯ');
INSERT INTO `location` VALUES (2, 'หนองบัวลำภู');
INSERT INTO `location` VALUES (3, 'เชียงใหม่');
INSERT INTO `location` VALUES (4, 'ขอนแก่น');
INSERT INTO `location` VALUES (5, 'อุดรธานี');
INSERT INTO `location` VALUES (6, 'หนองคาย');
INSERT INTO `location` VALUES (7, 'ร้อยเอ็ด');
INSERT INTO `location` VALUES (8, 'นครพนม');
INSERT INTO `location` VALUES (9, 'สกลนคร');
INSERT INTO `location` VALUES (10, 'เรณูนคร');
INSERT INTO `location` VALUES (11, 'กาฬสินธ์');
INSERT INTO `location` VALUES (12, 'สุรินทร์');
INSERT INTO `location` VALUES (13, 'สนม');
INSERT INTO `location` VALUES (14, 'บุรีรัมย์');
INSERT INTO `location` VALUES (15, 'จักราช');
INSERT INTO `location` VALUES (16, 'สหัสขันธ์');
INSERT INTO `location` VALUES (17, 'บ้านแพง');
INSERT INTO `location` VALUES (18, 'เชียงราย');
INSERT INTO `location` VALUES (19, 'ลำปาง');
INSERT INTO `location` VALUES (20, 'อุบลราชธานี');
INSERT INTO `location` VALUES (21, 'น่าน');
INSERT INTO `location` VALUES (22, 'อุตรดิตถ์');
INSERT INTO `location` VALUES (23, 'แพร่');
INSERT INTO `location` VALUES (24, 'ศรีสะเกษ');
INSERT INTO `location` VALUES (25, 'มุกดาหาร');
INSERT INTO `location` VALUES (26, 'อำนาจฯ');
INSERT INTO `location` VALUES (27, 'โพธิไทร');
INSERT INTO `location` VALUES (28, 'มหาสารคาม');
INSERT INTO `location` VALUES (29, 'เขมราฐ');
INSERT INTO `location` VALUES (30, 'กุดข้าวปุ้น');
INSERT INTO `location` VALUES (31, 'ชานุมาน');
INSERT INTO `location` VALUES (32, 'หนองหงส์');
INSERT INTO `location` VALUES (33, 'ศรีสงคราม');
INSERT INTO `location` VALUES (34, 'ธาตุพนม');
INSERT INTO `location` VALUES (35, 'กาบเชิง');
INSERT INTO `location` VALUES (36, 'จักราช');
INSERT INTO `location` VALUES (37, 'ระยอง');
INSERT INTO `location` VALUES (38, 'แม่สาย');
INSERT INTO `location` VALUES (39, 'พิษณุโลก');
INSERT INTO `location` VALUES (40, 'น่าน');
INSERT INTO `location` VALUES (41, 'พัทยา');
INSERT INTO `location` VALUES (42, 'ตาก');
INSERT INTO `location` VALUES (43, 'นครสวรรค์');
INSERT INTO `location` VALUES (44, 'พัทยา(มอเตอร์เวย์)');
INSERT INTO `location` VALUES (45, 'ปราสาท');
INSERT INTO `location` VALUES (46, 'นางรอง');
INSERT INTO `location` VALUES (47, 'รัตนบุรี');
INSERT INTO `location` VALUES (48, 'ระยอง(มอเตอร์เวย์)');
INSERT INTO `location` VALUES (49, 'ดอนตาล');
INSERT INTO `location` VALUES (50, 'แกดำ');

-- ----------------------------
-- Table structure for location_stop
-- ----------------------------
DROP TABLE IF EXISTS `location_stop`;
CREATE TABLE `location_stop`  (
  `loc_id` int(11) NOT NULL AUTO_INCREMENT,
  `loc_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `province` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`loc_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 37 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of location_stop
-- ----------------------------
INSERT INTO `location_stop` VALUES (1, 'กรุงเทพ สถานีเดินรถนครชัยแอร์', 'กรุงเทพมหานคร');
INSERT INTO `location_stop` VALUES (2, 'กรุงเทพหมอชิต', 'กรุงเทพมหานคร');
INSERT INTO `location_stop` VALUES (3, 'รังสิต สถานีเดินรถนครชัยแอร์', 'ปทุมธานี');
INSERT INTO `location_stop` VALUES (4, 'จุดปั๊มใบเวลา วังน้อย', 'อยุธยา');
INSERT INTO `location_stop` VALUES (5, 'ร้านอาหารประเดิมชัย', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (6, 'ลำตะคอง (จุดปั๊มใบเวลา)', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (7, 'ป้อมตร.ทางหลวงคลองไผ่', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (8, 'ลำตะคอง (จุดเปลี่ยนพ่วง)', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (9, 'บ้านกลางมิตรภาพ (จุดเปลี่ยนพ่วง)', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (10, 'ลานภักดี', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (11, 'สถานีขนส่ง นครราชสีมา', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (12, 'บ้านส้ม', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (13, 'ตลาดแค', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (14, 'โนนตาเถร', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (15, 'ป้อมตำรวจทงหลวง สีดา', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (16, 'สีดา', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (17, 'สีดา สถานีเดินรถนครชัยแอร์', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (18, 'บัวลาย', 'นครราชสีมา');
INSERT INTO `location_stop` VALUES (19, 'ป้อมตำรวจภูธร อำเภอพล', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (20, 'เมืองพล', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (21, 'สถานีขนส่ง อำเภอพล', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (22, 'โนนศิลา', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (23, 'บ้านไผ่', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (24, 'สถานีขนส่ง บ้านไผ่', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (25, 'บ้านเกิ้ง', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (26, 'ศูนย์มีชัย', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (27, 'บ้านแฮด', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (28, 'รพ.สิรินธร', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (29, 'ดงกลาง', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (30, 'หนองบัวดีหมี', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (31, 'ท่าพระ', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (32, 'บ้านกุดกว้าง', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (33, 'สถานีขนส่ง ปรับอากาศขอนแก่น', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (34, 'แยกเจริญศรี', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (35, 'ม.เทคโนภาค', 'ขอนแก่น');
INSERT INTO `location_stop` VALUES (36, 'สถานีขนส่งขอนแก่นแห่งที่3', 'ขอนแก่น');

-- ----------------------------
-- Table structure for plan_route_wide
-- ----------------------------
DROP TABLE IF EXISTS `plan_route_wide`;
CREATE TABLE `plan_route_wide`  (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `route_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `plan_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `bus_id` int(11) NULL DEFAULT NULL,
  `junction1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `junction2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `total_distance` decimal(6, 2) NULL DEFAULT 0.00,
  `total_time` time NULL DEFAULT NULL,
  `stop_bkk_station` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_bkk_mochit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_rangsit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_wangnoi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_prademchai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_lamtakong_pump` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_police_khlongphai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_lamtakong_change` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_banmittraphap_change` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_lanphakdi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_korat_bus_station` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_bansom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_taladkae` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_nontaether` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_police_sida` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_sida` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_sida_station` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_bualai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_police_amphoe_phon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_muangphon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_amphoe_phon_station` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_nonsila` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_banphai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_banphai_station` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_bankeng` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_meechai_center` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_banhed` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_sirindhorn_hospital` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_dongklang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_nongbuadee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_thapra` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_bankudkwang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_khonkaen_airstation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_jaerongsri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_mtec` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `stop_kk3_station` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `plan_approved` tinyint(1) NULL DEFAULT 0,
  PRIMARY KEY (`plan_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan_route_wide
-- ----------------------------
INSERT INTO `plan_route_wide` VALUES (1, 'S20-1GBKK0630', '20', 'มาตรฐาน', 39, 'ลำตะคอง', '-', 444.00, '06:32:00', '1', '1', '0', '1', '0', '1', '0', '1', '0', '0', '0', '1', '1', '1', '0', '0', '0', '1', '0', '1', '0', '1', '1', '0', '1', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '1', 1);
INSERT INTO `plan_route_wide` VALUES (2, 'S20-1GBKK0800', '20', 'มาตรฐาน', 40, 'ลำตะคอง', '-', 444.00, '06:32:00', '1', '1', '1', '1', '1', '1', '0', '1', '1', '0', '0', '1', '1', '1', '0', '1', '0', '1', '0', '1', '0', '1', '1', '0', '1', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '1', 1);
INSERT INTO `plan_route_wide` VALUES (3, 'S20-1GBKK1000', '20', 'มาตรฐาน', 41, 'บ้านกลางมิตรภาพ ', '-', 444.00, '06:32:00', '1', '1', '1', '1', '1', '1', '0', '1', '1', '0', '0', '1', '1', '1', '0', '1', '0', '1', '0', '1', '0', '1', '1', '0', '1', '1', '1', '1', '1', '1', '1', '0', '0', '0', '1', '1', 0);
INSERT INTO `plan_route_wide` VALUES (4, 'S20-1GBKK1035', '20', 'มาตรฐาน', 41, 'บ้านกลางมิตรภาพ', '-', 444.00, '06:32:00', '1', '1', '0', '1', '1', '0', '0', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '1', 0);
INSERT INTO `plan_route_wide` VALUES (5, 'S20-1GBLL101', '20', 'แผนเสริม', 42, 'ลำตะคอง', '-', 444.00, '00:00:16', '1', '0', '0', '1', '0', '0', '0', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', 0);

-- ----------------------------
-- Table structure for route
-- ----------------------------
DROP TABLE IF EXISTS `route`;
CREATE TABLE `route`  (
  `route_id` int(11) NOT NULL AUTO_INCREMENT,
  `route_name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `start_location_id` int(11) NOT NULL,
  `end_location_id` int(11) NOT NULL,
  `route_number` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `distance_km` int(11) NULL DEFAULT NULL,
  `route_group` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'สายนอก',
  PRIMARY KEY (`route_id`) USING BTREE,
  INDEX `start_location_id`(`start_location_id` ASC) USING BTREE,
  INDEX `end_location_id`(`end_location_id` ASC) USING BTREE,
  CONSTRAINT `route_ibfk_1` FOREIGN KEY (`start_location_id`) REFERENCES `location` (`locat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `route_ibfk_2` FOREIGN KEY (`end_location_id`) REFERENCES `location` (`locat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 98 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of route
-- ----------------------------
INSERT INTO `route` VALUES (1, 'กรุงเทพฯ-หนองบัวลำภู-ศรีเชียงใหม่', 1, 2, '5', 0, 'สายใน');
INSERT INTO `route` VALUES (2, 'กรุงเทพฯ-เชียงใหม่(ข)', 1, 3, '18', 713, 'สายใน');
INSERT INTO `route` VALUES (3, 'กรุงเทพฯ-ขอนแก่น', 1, 4, '20', 444, 'สายใน');
INSERT INTO `route` VALUES (4, 'กรุงเทพฯ-อุดรธานี', 1, 5, '22', 561, 'สายใน');
INSERT INTO `route` VALUES (5, 'กรุงเทพฯ-หนองคาย', 1, 6, '23', 614, 'สายใน');
INSERT INTO `route` VALUES (6, 'กรุงเทพฯ-ดอนตาล', 1, 49, '24', 678, 'สายใน');
INSERT INTO `route` VALUES (7, 'กรุงเทพฯ-นครพนม', 1, 8, '26', 727, 'สายใน');
INSERT INTO `route` VALUES (8, 'กรุงเทพฯ-สกลนคร-เรณูนคร', 1, 10, '27', 722, 'สายใน');
INSERT INTO `route` VALUES (10, 'กรุงเทพฯ-สุรินทร์', 1, 12, '31', 428, 'สายใน');
INSERT INTO `route` VALUES (11, 'กรุงเทพฯ-จักราช-สนม', 1, 13, '937', 485, 'สายใน');
INSERT INTO `route` VALUES (12, 'กรุงเทพฯ-บุรีรัมย์', 1, 14, '32', 388, 'สายใน');
INSERT INTO `route` VALUES (13, 'กรุงเทพฯ-จักราช-บุรีรัมย์', 1, 14, '936', 384, 'สายใน');
INSERT INTO `route` VALUES (14, 'กรุงเทพฯ-สหัสขันธ์-บ้านแพง', 1, 17, '34', 809, 'สายใน');
INSERT INTO `route` VALUES (15, 'กรุงเทพฯ-เชียงราย', 1, 18, '90', 844, 'สายใน');
INSERT INTO `route` VALUES (16, 'กรุงเทพฯ-ลำปาง(ข)', 1, 19, '91', 610, 'สายใน');
INSERT INTO `route` VALUES (17, 'กรุงเทพฯ-อุบลราชธานี(ข)', 1, 20, '98', 610, 'สายใน');
INSERT INTO `route` VALUES (18, 'กรุงเทพฯ-น่าน(ข)', 1, 21, '910', NULL, 'สายใน');
INSERT INTO `route` VALUES (19, 'กรุงเทพฯ-อุตรดิตถ์(ข)', 1, 22, '912', 475, 'สายใน');
INSERT INTO `route` VALUES (20, 'กรุงเทพฯ-แพร่', 1, 23, '923', 555, 'สายใน');
INSERT INTO `route` VALUES (21, 'กรุงเทพฯ-ศรีสะเกษ', 1, 24, '926', 547, 'สายใน');
INSERT INTO `route` VALUES (22, 'กรุงเทพฯ-มุกดาหาร', 1, 25, '927', 671, 'สายใน');
INSERT INTO `route` VALUES (23, 'กรุงเทพฯ-อำนาจฯ-โพธิ์ไทร', 1, 27, '929', 684, 'สายใน');
INSERT INTO `route` VALUES (24, 'กรุงเทพฯ-แกดำ', 1, 50, '932', 493, 'สายใน');
INSERT INTO `route` VALUES (25, 'อุบลราชธานี-เชียงใหม่', 20, 3, '587', 1055, 'สายนอก');
INSERT INTO `route` VALUES (26, 'อุบลราชธานี-ระยอง', 20, 37, '588', 756, 'สายนอก');
INSERT INTO `route` VALUES (27, 'เชียงใหม่-ระยอง', 3, 37, '659', 990, 'สายนอก');
INSERT INTO `route` VALUES (28, 'ระยอง-แม่สาย', 36, 38, '660', 1142, 'สายนอก');
INSERT INTO `route` VALUES (29, 'อุบลราชธานี-เชียงใหม่', 20, 3, '587', 1055, 'สายนอก');
INSERT INTO `route` VALUES (30, 'อุบลราชธานี-ระยอง', 20, 37, '588', 756, 'สายนอก');
INSERT INTO `route` VALUES (31, 'เชียงใหม่-ระยอง', 3, 37, '659', 990, 'สายนอก');
INSERT INTO `route` VALUES (32, 'ระยอง-แม่สาย', 36, 38, '660', 1142, 'สายนอก');
INSERT INTO `route` VALUES (33, 'กรุงเทพฯ-ร้อยเอ็ด', 1, 7, '24', 509, 'สายใน');
INSERT INTO `route` VALUES (34, 'กรุงเทพฯ-มหาสารคาม', 1, 28, '932', 469, 'สายใน');
INSERT INTO `route` VALUES (35, 'กรุงเทพฯ-กาฬสินธ์', 1, 11, '30', 513, 'สายใน');
INSERT INTO `route` VALUES (65, 'กรุงเทพฯ-ศรีสงคราม', 1, 33, '26', 703, 'สายใน');
INSERT INTO `route` VALUES (66, 'กรุงเทพฯ-สกลนคร', 1, 9, '27', 633, 'สายใน');
INSERT INTO `route` VALUES (67, 'กรุงเทพฯ-ธาตุพนม', 1, 34, '27', 707, 'สายใน');
INSERT INTO `route` VALUES (68, 'กรุงเทพฯ-กาบเชิง', 1, 35, '31', 464, 'สายใน');
INSERT INTO `route` VALUES (69, 'กรุงเทพฯ-จักราช-สุรินทร์', 1, 12, '937', 436, 'สายใน');
INSERT INTO `route` VALUES (70, 'กรุงเทพฯ-หนองหงส์-บุรีรัมย์', 1, 14, '936', 401, 'สายใน');
INSERT INTO `route` VALUES (71, 'กรุงเทพฯ-เขมราฐ', 1, 29, '929', 649, 'สายใน');
INSERT INTO `route` VALUES (72, 'กรุงเทพฯ-กุดข้าวปุ้น', 1, 30, '929', 624, 'สายใน');
INSERT INTO `route` VALUES (73, 'กรุงเทพฯ-ชานุมาน', 1, 31, '929', 657, 'สายใน');
INSERT INTO `route` VALUES (74, 'อุบลราชธานี-เชียงใหม่', 20, 3, '587', 1055, 'สายนอก');
INSERT INTO `route` VALUES (75, 'อุบลราชธานี-รัตนบุรี-เชียงใหม่', 20, 3, '587', 1006, 'สายนอก');
INSERT INTO `route` VALUES (76, 'อุบลราชธานี-อุตรดิตถ์', 20, 22, '587', 810, 'สายนอก');
INSERT INTO `route` VALUES (77, 'สุรินทร์-บุรีรัมย์-เชียงใหม่', 12, 3, '587', 890, 'สายนอก');
INSERT INTO `route` VALUES (78, 'อุบลราชธานี-เชียงราย', 20, 18, '587', 1121, 'สายนอก');
INSERT INTO `route` VALUES (79, 'อุบลราชธานี-แพร่-น่าน', 20, 21, '587', 1009, 'สายนอก');
INSERT INTO `route` VALUES (80, 'อุบลราชธานี-นางรอง-พัทยา', 20, 41, '588', 664, 'สายนอก');
INSERT INTO `route` VALUES (81, 'สุรินทร์-ปราสาท-พัทยา', 12, 41, '588', 489, 'สายนอก');
INSERT INTO `route` VALUES (82, 'บุรีรัมย์-นางรอง-พัทยา', 14, 41, '588', 445, 'สายนอก');
INSERT INTO `route` VALUES (83, 'อุบลราชธานี-ระยอง', 20, 37, '588', 756, 'สายนอก');
INSERT INTO `route` VALUES (84, 'ศรีสะเกษ-นางรอง-พัทยา', 24, 41, '588', 599, 'สายนอก');
INSERT INTO `route` VALUES (85, 'เชียงใหม่-ตาก-พัทยา(มอเตอร์เวย์)', 3, 44, '588', 841, 'สายนอก');
INSERT INTO `route` VALUES (86, 'เชียงใหม่-ระยอง', 3, 37, '659', 990, 'สายนอก');
INSERT INTO `route` VALUES (87, 'เชียงใหม่-ระยอง(มอเตอร์เวย์)', 3, 48, '659', 910, 'สายนอก');
INSERT INTO `route` VALUES (88, 'เชียงใหม่-ตาก-ระยอง(มอเตอร์เวย์)', 3, 48, '659', 915, 'สายนอก');
INSERT INTO `route` VALUES (89, 'อุตรดิตถ์-พิษณุโลก-พัทยา(มอเตอร์เวย์)', 22, 44, '659', 677, 'สายนอก');
INSERT INTO `route` VALUES (90, 'พิษณุโลก-พัทยา(มอเตอร์เวย์)', 40, 44, '659', 570, 'สายนอก');
INSERT INTO `route` VALUES (91, 'พัทยา-ตาก-เชียงราย', 38, 18, '660', 972, 'สายนอก');
INSERT INTO `route` VALUES (92, 'ระยอง-นครสวรรค์-เชียงราย', 36, 18, '660', 1000, 'สายนอก');
INSERT INTO `route` VALUES (93, 'ระยอง-ตาก-แม่สาย', 36, 38, '660', 1108, 'สายนอก');
INSERT INTO `route` VALUES (94, 'ระยอง-พิษณุโลก-น่าน', 36, 21, '660', 876, 'สายนอก');
INSERT INTO `route` VALUES (95, 'กรุงเทพฯ-หนองบัวลำภู', 1, 5, '5', 535, 'สายใน');

SET FOREIGN_KEY_CHECKS = 1;
