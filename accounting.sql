# MySQL-Front 3.2  (Build 10.6)

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40111 SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT */;

/*!40101 SET NAMES latin1 */;
/*!40103 SET TIME_ZONE='SYSTEM' */;
SET AUTOCOMMIT=0;
BEGIN;

# Host: localhost    Database: accounting
# ------------------------------------------------------
# Server version 5.0.27-community-nt

#
# Table structure for table tbladd
#

DROP TABLE IF EXISTS `tbladd`;
CREATE TABLE `tbladd` (
  `add_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `student_id` int(11) default NULL,
  `class_id` int(11) default NULL,
  `date` date default NULL,
  PRIMARY KEY  (`add_id`),
  UNIQUE KEY `student_id_2` (`student_id`,`class_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tbladd
#



#
# Table structure for table tbladditionalfee
#

DROP TABLE IF EXISTS `tbladditionalfee`;
CREATE TABLE `tbladditionalfee` (
  `additionalfee_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `student_id` int(11) default NULL,
  `feeelement_id` int(11) default NULL,
  `amount` bigint(20) default NULL,
  PRIMARY KEY  (`additionalfee_id`),
  UNIQUE KEY `sy_id_2` (`sy_id`,`student_id`,`feeelement_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `feeelement_id` (`feeelement_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tbladditionalfee
#



#
# Table structure for table tblauth
#

DROP TABLE IF EXISTS `tblauth`;
CREATE TABLE `tblauth` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` varchar(16) collate latin1_general_ci NOT NULL default '',
  `passwd` varchar(32) collate latin1_general_ci NOT NULL default '',
  `authflag` int(11) NOT NULL default '0',
  `authflag_w` int(11) NOT NULL default '0',
  `active` smallint(6) NOT NULL default '0',
  `fullname` text collate latin1_general_ci,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblauth
#

INSERT INTO `tblauth` VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3',1996488718,1073741824,0,'administrator');
INSERT INTO `tblauth` VALUES (2,'registrar','5940569cd1d60781f856f93235b072ee',536870926,0,0,NULL);
INSERT INTO `tblauth` VALUES (3,'accounting','d4c143f004d88b7286e6f999dea9d0d7',268435456,0,0,NULL);
INSERT INTO `tblauth` VALUES (4,'cashier','6ac2470ed8ccf204fd5ff89b32a355cf',134217728,0,0,NULL);
INSERT INTO `tblauth` VALUES (41,'zaldy','28c3530f9eea69c782f369f52c3afb9d',2130707326,2130707326,1,'zaldy jabiñar');
INSERT INTO `tblauth` VALUES (55,'wency','ba7d17ac5cdd131015987d90f47aad7a',2130706446,2130706446,1,'Wenceslao Perante');
INSERT INTO `tblauth` VALUES (56,'felisa','6b71a2bb3e4bec7c00953eb9bbd47556',1056964622,1056964622,1,'Felisa E. Gomba');


#
# Table structure for table tblblockstudent
#

DROP TABLE IF EXISTS `tblblockstudent`;
CREATE TABLE `tblblockstudent` (
  `block_id` int(11) NOT NULL auto_increment,
  `student_id` int(11) NOT NULL default '0',
  `office_id` int(11) NOT NULL default '0',
  `message` text collate latin1_general_ci NOT NULL,
  `date` date default NULL,
  PRIMARY KEY  (`block_id`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblblockstudent
#



#
# Table structure for table tblbuilding
#

DROP TABLE IF EXISTS `tblbuilding`;
CREATE TABLE `tblbuilding` (
  `building_id` int(11) NOT NULL auto_increment,
  `description` text collate latin1_general_ci,
  PRIMARY KEY  (`building_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblbuilding
#

INSERT INTO `tblbuilding` VALUES (1,'DOTC');
INSERT INTO `tblbuilding` VALUES (2,'LUNA');


#
# Table structure for table tblchange
#

DROP TABLE IF EXISTS `tblchange`;
CREATE TABLE `tblchange` (
  `change_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `student_id` int(11) default NULL,
  `class_id` int(11) default NULL,
  `new_class_id` int(11) default NULL,
  `date` date default NULL,
  PRIMARY KEY  (`change_id`),
  UNIQUE KEY `student_id_2` (`student_id`,`class_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  KEY `new_class_id` (`new_class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblchange
#



#
# Table structure for table tblcivilstatus
#

DROP TABLE IF EXISTS `tblcivilstatus`;
CREATE TABLE `tblcivilstatus` (
  `civilstatus_id` int(11) NOT NULL auto_increment,
  `title` text collate latin1_general_ci,
  PRIMARY KEY  (`civilstatus_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblcivilstatus
#

INSERT INTO `tblcivilstatus` VALUES (1,'single');
INSERT INTO `tblcivilstatus` VALUES (2,'married');
INSERT INTO `tblcivilstatus` VALUES (3,'divorced');
INSERT INTO `tblcivilstatus` VALUES (4,'separated');


#
# Table structure for table tblclass
#

DROP TABLE IF EXISTS `tblclass`;
CREATE TABLE `tblclass` (
  `class_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `year_level` int(11) default NULL,
  `department_id` int(11) NOT NULL default '0',
  `course_id` int(11) default NULL,
  `major_ignore` smallint(6) default NULL,
  `section_flag` int(11) NOT NULL default '0',
  `max_student_reg` int(11) NOT NULL default '0',
  `max_student_nreg` int(11) NOT NULL default '0',
  `subject` text collate latin1_general_ci NOT NULL,
  `subject_code` text collate latin1_general_ci,
  `unit` int(11) default NULL,
  `exempt` smallint(6) default NULL,
  `flag` bigint(20) default NULL,
  `teacher_id` int(11) default NULL,
  `feeelement_id` int(11) default NULL,
  `fee_amount` int(11) default NULL,
  PRIMARY KEY  (`class_id`),
  KEY `department_id` (`department_id`),
  KEY `sy_id` (`sy_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `course_id` (`course_id`),
  KEY `feeelement_id` (`feeelement_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblclass
#

INSERT INTO `tblclass` VALUES (1,20061,1,1,2,0,3,45,0,'test description','test code',NULL,NULL,32768,1,NULL,NULL);


#
# Table structure for table tblclassschedule
#

DROP TABLE IF EXISTS `tblclassschedule`;
CREATE TABLE `tblclassschedule` (
  `schedule_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `class_id` int(11) default NULL,
  `room_id` int(11) default NULL,
  `time_st` int(11) default NULL,
  `time_end` int(11) default NULL,
  PRIMARY KEY  (`schedule_id`),
  KEY `sy_id` (`sy_id`),
  KEY `class_id` (`class_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblclassschedule
#

INSERT INTO `tblclassschedule` VALUES (1,20061,1,1,420,600);
INSERT INTO `tblclassschedule` VALUES (2,20061,1,1,3300,3420);


#
# Table structure for table tblcourse
#

DROP TABLE IF EXISTS `tblcourse`;
CREATE TABLE `tblcourse` (
  `course_id` int(11) NOT NULL auto_increment,
  `department_id` int(11) NOT NULL default '0',
  `short_name` char(16) collate latin1_general_ci NOT NULL default '',
  `long_name` char(64) collate latin1_general_ci NOT NULL default '',
  `major` char(32) collate latin1_general_ci default NULL,
  `minor` char(32) collate latin1_general_ci default NULL,
  `school_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`course_id`),
  KEY `department_id` (`department_id`),
  KEY `school_id` (`school_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblcourse
#

INSERT INTO `tblcourse` VALUES (1,1,'BSEE','Bachelor of Science in Electrical Engineering','','',1);
INSERT INTO `tblcourse` VALUES (2,1,'BSCE','Bachelor of Science in Civil Engineering','','',1);
INSERT INTO `tblcourse` VALUES (3,1,'BSME','Bachelor of Science in Mechanical Engineering','','',1);
INSERT INTO `tblcourse` VALUES (4,1,'BSIE','Bachelor of Science in Industrial Engineering','','',1);
INSERT INTO `tblcourse` VALUES (5,3,'BSHRT','Bachelor of Science in Hotel and Restaurant Technology','','',1);
INSERT INTO `tblcourse` VALUES (6,3,'BIT','Bachelor of Industrial Technology','Food Technology','',1);
INSERT INTO `tblcourse` VALUES (7,3,'BIT','Bachelor of Industrial Technology','Civil Technology','',1);
INSERT INTO `tblcourse` VALUES (8,3,'BIT','Bachelor of Industrial Technology','Electronics Technology','',1);


#
# Table structure for table tbldepartment
#

DROP TABLE IF EXISTS `tbldepartment`;
CREATE TABLE `tbldepartment` (
  `department_id` int(11) NOT NULL auto_increment,
  `short_name` char(8) collate latin1_general_ci NOT NULL default '',
  `long_name` char(64) collate latin1_general_ci NOT NULL default '',
  `dean_id` int(11) default NULL,
  `order_no` int(11) NOT NULL default '0',
  PRIMARY KEY  (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tbldepartment
#

INSERT INTO `tbldepartment` VALUES (1,'COE','Department of Engineering',0,10);
INSERT INTO `tbldepartment` VALUES (2,'COEd','Department of Education',0,20);
INSERT INTO `tbldepartment` VALUES (3,'COT','Department of Technology',0,30);


#
# Table structure for table tbldrop
#

DROP TABLE IF EXISTS `tbldrop`;
CREATE TABLE `tbldrop` (
  `drop_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `student_id` int(11) default NULL,
  `class_id` int(11) default NULL,
  `date` date default NULL,
  PRIMARY KEY  (`drop_id`),
  UNIQUE KEY `student_id_2` (`student_id`,`class_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tbldrop
#



#
# Table structure for table tblfeecategory
#

DROP TABLE IF EXISTS `tblfeecategory`;
CREATE TABLE `tblfeecategory` (
  `feecategory_id` int(11) NOT NULL default '0',
  `title` varchar(64) collate latin1_general_ci NOT NULL default '',
  `fee_flag` int(11) NOT NULL default '0',
  PRIMARY KEY  (`feecategory_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblfeecategory
#

INSERT INTO `tblfeecategory` VALUES (1,'Registration Fee',2);
INSERT INTO `tblfeecategory` VALUES (2,'Tuition Fee',1);
INSERT INTO `tblfeecategory` VALUES (3,'Miscellaneous Fee',2);
INSERT INTO `tblfeecategory` VALUES (4,'Other Fee',3);
INSERT INTO `tblfeecategory` VALUES (5,'Graduation Fee',4);
INSERT INTO `tblfeecategory` VALUES (6,'Internet Fee',2);
INSERT INTO `tblfeecategory` VALUES (7,'NSTP-ROTC',1);
INSERT INTO `tblfeecategory` VALUES (8,'NSTP-CWTS/LTC',1);
INSERT INTO `tblfeecategory` VALUES (9,'Related Learning Expericence(RLE)',3);
INSERT INTO `tblfeecategory` VALUES (10,'Test Booklet',1);
INSERT INTO `tblfeecategory` VALUES (11,'Uniform',8);
INSERT INTO `tblfeecategory` VALUES (12,'Hospital Affiliation Fees',2);
INSERT INTO `tblfeecategory` VALUES (13,'Entrance Fee',8);
INSERT INTO `tblfeecategory` VALUES (14,'Transcript of Records',8);
INSERT INTO `tblfeecategory` VALUES (15,'Certification Fee',8);
INSERT INTO `tblfeecategory` VALUES (16,'Statement of Account',8);
INSERT INTO `tblfeecategory` VALUES (17,'Accreditation Fee',8);
INSERT INTO `tblfeecategory` VALUES (18,'Other Fees',8);
INSERT INTO `tblfeecategory` VALUES (20,'Examination Fee',8);
INSERT INTO `tblfeecategory` VALUES (21,'Books',8);
INSERT INTO `tblfeecategory` VALUES (22,'Dormitories',8);
INSERT INTO `tblfeecategory` VALUES (23,'Penalties',16);
INSERT INTO `tblfeecategory` VALUES (24,'Removal',8);
INSERT INTO `tblfeecategory` VALUES (25,'Graduation Fees',8);
INSERT INTO `tblfeecategory` VALUES (26,'',8);
INSERT INTO `tblfeecategory` VALUES (27,'Alumni Fee',8);
INSERT INTO `tblfeecategory` VALUES (28,'Baccalaureate Dinner',8);
INSERT INTO `tblfeecategory` VALUES (29,'Miscellaneous Fee',8);
INSERT INTO `tblfeecategory` VALUES (30,'Picture',8);
INSERT INTO `tblfeecategory` VALUES (31,'Souvenir Program',8);
INSERT INTO `tblfeecategory` VALUES (32,'TOGA',8);
INSERT INTO `tblfeecategory` VALUES (33,'Diploma',8);
INSERT INTO `tblfeecategory` VALUES (34,'School ID',8);
INSERT INTO `tblfeecategory` VALUES (35,'Year Book',8);
INSERT INTO `tblfeecategory` VALUES (36,'School ID',2);


#
# Table structure for table tblfeeelement
#

DROP TABLE IF EXISTS `tblfeeelement`;
CREATE TABLE `tblfeeelement` (
  `feeelement_id` int(11) NOT NULL auto_increment,
  `title` varchar(64) collate latin1_general_ci NOT NULL default '',
  `feecategory_id` int(11) default NULL,
  `fee_flag` int(11) NOT NULL default '0',
  PRIMARY KEY  (`feeelement_id`),
  UNIQUE KEY `title` (`title`,`feecategory_id`),
  KEY `feecategory_id` (`feecategory_id`)
) ENGINE=MyISAM AUTO_INCREMENT=95 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblfeeelement
#

INSERT INTO `tblfeeelement` VALUES (1,'Tuition Fee',2,1);
INSERT INTO `tblfeeelement` VALUES (2,'Laboratory Fee',4,1);
INSERT INTO `tblfeeelement` VALUES (3,'Computer Laboratory Fee',4,1);
INSERT INTO `tblfeeelement` VALUES (4,'Changing/Dropping/Adding of subject',4,1);
INSERT INTO `tblfeeelement` VALUES (5,'Penalty of late enrolment',4,1);
INSERT INTO `tblfeeelement` VALUES (6,'OJT Fee',4,1);
INSERT INTO `tblfeeelement` VALUES (7,'Internship Fee',4,1);
INSERT INTO `tblfeeelement` VALUES (8,'Library Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (9,'Med',3,2);
INSERT INTO `tblfeeelement` VALUES (10,'Medical Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (11,'Dental Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (12,'Athletic Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (13,'School Organ',3,2);
INSERT INTO `tblfeeelement` VALUES (14,'FFPCC Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (15,'BSP/GSP',3,2);
INSERT INTO `tblfeeelement` VALUES (16,'Red Cross',3,2);
INSERT INTO `tblfeeelement` VALUES (17,'Cultural Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (18,'Guidance Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (19,'Security Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (20,'Development Fee',3,2);
INSERT INTO `tblfeeelement` VALUES (21,'SSA',3,2);
INSERT INTO `tblfeeelement` VALUES (22,'SAI',3,2);
INSERT INTO `tblfeeelement` VALUES (23,'School ID',3,2);
INSERT INTO `tblfeeelement` VALUES (24,'Registration Fee',1,2);
INSERT INTO `tblfeeelement` VALUES (25,'PPA',4,2);
INSERT INTO `tblfeeelement` VALUES (26,'Internet Fee',6,2);
INSERT INTO `tblfeeelement` VALUES (28,'Orientation Fee',4,2);
INSERT INTO `tblfeeelement` VALUES (30,'GPGSA',3,2);
INSERT INTO `tblfeeelement` VALUES (31,'Graduate Journal',3,2);
INSERT INTO `tblfeeelement` VALUES (32,'Research Journal',3,2);
INSERT INTO `tblfeeelement` VALUES (33,'Application Fee',4,2);
INSERT INTO `tblfeeelement` VALUES (34,'STEP',3,2);
INSERT INTO `tblfeeelement` VALUES (35,'Youth Science Club',3,2);
INSERT INTO `tblfeeelement` VALUES (36,'FFP/FAHP',3,2);
INSERT INTO `tblfeeelement` VALUES (37,'Student Handbook',4,2);
INSERT INTO `tblfeeelement` VALUES (38,'TOGA',5,4);
INSERT INTO `tblfeeelement` VALUES (39,'Program',5,4);
INSERT INTO `tblfeeelement` VALUES (40,'Diploma',5,4);
INSERT INTO `tblfeeelement` VALUES (41,'Alumni Fee',5,4);
INSERT INTO `tblfeeelement` VALUES (42,'Miscellaneous Fee',5,4);
INSERT INTO `tblfeeelement` VALUES (43,'NSTP-CWTS/LTS',8,1);
INSERT INTO `tblfeeelement` VALUES (44,'NSTP-NROTC',7,1);
INSERT INTO `tblfeeelement` VALUES (45,'Nursing Laboratory Fee',4,1);
INSERT INTO `tblfeeelement` VALUES (46,'Related Learning Experience',9,1);
INSERT INTO `tblfeeelement` VALUES (47,'Test Booklet',10,1);
INSERT INTO `tblfeeelement` VALUES (48,'Uniform',11,8);
INSERT INTO `tblfeeelement` VALUES (49,'RLE',9,2);
INSERT INTO `tblfeeelement` VALUES (53,'Hospital Affiliation Fees',12,3);
INSERT INTO `tblfeeelement` VALUES (55,'Entrance Fee',13,8);
INSERT INTO `tblfeeelement` VALUES (56,'Transcript of Records',14,8);
INSERT INTO `tblfeeelement` VALUES (57,'Certification Fee',15,8);
INSERT INTO `tblfeeelement` VALUES (58,'Statement of Account',16,8);
INSERT INTO `tblfeeelement` VALUES (59,'Accreditation Fee',17,8);
INSERT INTO `tblfeeelement` VALUES (60,'Class Cards',18,8);
INSERT INTO `tblfeeelement` VALUES (61,'Enrollment Form',18,8);
INSERT INTO `tblfeeelement` VALUES (62,'Assessment Slip',18,8);
INSERT INTO `tblfeeelement` VALUES (63,'Examination Permit',18,8);
INSERT INTO `tblfeeelement` VALUES (64,'Examination Fee',20,8);
INSERT INTO `tblfeeelement` VALUES (65,'Books',21,8);
INSERT INTO `tblfeeelement` VALUES (66,'Dorm',22,8);
INSERT INTO `tblfeeelement` VALUES (67,'Assessment Slip',23,16);
INSERT INTO `tblfeeelement` VALUES (68,'removal',18,8);
INSERT INTO `tblfeeelement` VALUES (69,'Removal',24,8);
INSERT INTO `tblfeeelement` VALUES (70,'Picture',5,4);
INSERT INTO `tblfeeelement` VALUES (71,'Baccalaureate Dinner',5,4);
INSERT INTO `tblfeeelement` VALUES (73,'TOGA',25,8);
INSERT INTO `tblfeeelement` VALUES (74,'Souvenir Program',25,8);
INSERT INTO `tblfeeelement` VALUES (75,'Program',11,8);
INSERT INTO `tblfeeelement` VALUES (76,'Diploma',25,8);
INSERT INTO `tblfeeelement` VALUES (77,'Alumni Fee',25,8);
INSERT INTO `tblfeeelement` VALUES (78,'Picture',25,8);
INSERT INTO `tblfeeelement` VALUES (80,'Miscellaneous Fee',25,8);
INSERT INTO `tblfeeelement` VALUES (85,'Alumni Fee',27,8);
INSERT INTO `tblfeeelement` VALUES (86,'Baccalaureate Dinner',28,8);
INSERT INTO `tblfeeelement` VALUES (87,'Miscellaneous Fee',29,8);
INSERT INTO `tblfeeelement` VALUES (88,'Picture',30,8);
INSERT INTO `tblfeeelement` VALUES (89,'Souvenir Program',31,8);
INSERT INTO `tblfeeelement` VALUES (90,'TOGA',32,8);
INSERT INTO `tblfeeelement` VALUES (91,'Diploma',33,8);
INSERT INTO `tblfeeelement` VALUES (92,'School ID',34,8);
INSERT INTO `tblfeeelement` VALUES (93,'Year Book',35,8);
INSERT INTO `tblfeeelement` VALUES (94,'School ID',36,2);


#
# Table structure for table tblfeerate
#

DROP TABLE IF EXISTS `tblfeerate`;
CREATE TABLE `tblfeerate` (
  `feerate_id` int(11) NOT NULL auto_increment,
  `enter_year` int(11) default NULL,
  `school_id` int(11) default NULL,
  `department_id` int(11) default NULL,
  `course_id` int(11) default NULL,
  `year_level` int(11) default NULL,
  `effective_syid` int(11) default NULL,
  `feeratetitle_id` int(11) default NULL,
  `amount` bigint(20) default NULL,
  PRIMARY KEY  (`feerate_id`),
  UNIQUE KEY `enter_year` (`enter_year`,`school_id`,`department_id`,`course_id`,`year_level`,`effective_syid`,`feeratetitle_id`),
  KEY `school_id` (`school_id`),
  KEY `effective_syid` (`effective_syid`),
  KEY `feeratetitle_id` (`feeratetitle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblfeerate
#



#
# Table structure for table tblfeeratetitle
#

DROP TABLE IF EXISTS `tblfeeratetitle`;
CREATE TABLE `tblfeeratetitle` (
  `feeratetitle_id` int(11) NOT NULL default '0',
  `feeratetype` int(11) default NULL,
  `feeelement_id` int(11) default NULL,
  `department_id` int(11) default NULL,
  `defaultval` smallint(6) default NULL,
  `short_name` char(16) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`feeratetitle_id`),
  KEY `feeelement_id` (`feeelement_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblfeeratetitle
#

INSERT INTO `tblfeeratetitle` VALUES (1,1,1,NULL,0,NULL);
INSERT INTO `tblfeeratetitle` VALUES (2,9,2,NULL,0,'Lab');
INSERT INTO `tblfeeratetitle` VALUES (3,9,3,NULL,0,'Com');
INSERT INTO `tblfeeratetitle` VALUES (4,2,4,NULL,0,NULL);
INSERT INTO `tblfeeratetitle` VALUES (5,3,4,NULL,0,NULL);
INSERT INTO `tblfeeratetitle` VALUES (6,4,4,NULL,0,NULL);
INSERT INTO `tblfeeratetitle` VALUES (7,5,5,NULL,0,NULL);
INSERT INTO `tblfeeratetitle` VALUES (8,6,5,NULL,0,NULL);
INSERT INTO `tblfeeratetitle` VALUES (9,7,5,NULL,0,NULL);
INSERT INTO `tblfeeratetitle` VALUES (10,9,6,NULL,0,'OJT');
INSERT INTO `tblfeeratetitle` VALUES (11,9,7,2,0,'SED');
INSERT INTO `tblfeeratetitle` VALUES (12,9,44,9,0,'ROTC');
INSERT INTO `tblfeeratetitle` VALUES (13,9,43,9,0,'CWTS');
INSERT INTO `tblfeeratetitle` VALUES (14,9,45,8,0,'NurseLab');
INSERT INTO `tblfeeratetitle` VALUES (15,9,46,8,0,'RLE');
INSERT INTO `tblfeeratetitle` VALUES (16,9,47,NULL,1,'TB');
INSERT INTO `tblfeeratetitle` VALUES (17,10,53,8,0,'HAF');


#
# Table structure for table tblgraderemark
#

DROP TABLE IF EXISTS `tblgraderemark`;
CREATE TABLE `tblgraderemark` (
  `id` int(11) NOT NULL auto_increment,
  `remark` text collate latin1_general_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblgraderemark
#

INSERT INTO `tblgraderemark` VALUES (1,'Incomplete');
INSERT INTO `tblgraderemark` VALUES (2,'Dropped');
INSERT INTO `tblgraderemark` VALUES (3,'On progress');
INSERT INTO `tblgraderemark` VALUES (4,'No grade');
INSERT INTO `tblgraderemark` VALUES (5,'Not taken');
INSERT INTO `tblgraderemark` VALUES (6,'Failed');


#
# Table structure for table tblgraduationfee
#

DROP TABLE IF EXISTS `tblgraduationfee`;
CREATE TABLE `tblgraduationfee` (
  `gradfee_id` int(11) NOT NULL auto_increment,
  `year` int(11) default NULL,
  `school_id` int(11) default NULL,
  `department_id` int(11) default NULL,
  `course_id` int(11) default NULL,
  `feeelement_id` int(11) default NULL,
  `amount` bigint(20) default NULL,
  PRIMARY KEY  (`gradfee_id`),
  UNIQUE KEY `year` (`year`,`school_id`,`department_id`,`course_id`,`feeelement_id`),
  KEY `school_id` (`school_id`),
  KEY `feeelement_id` (`feeelement_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblgraduationfee
#



#
# Table structure for table tblguarantor
#

DROP TABLE IF EXISTS `tblguarantor`;
CREATE TABLE `tblguarantor` (
  `guarantor_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) NOT NULL default '0',
  `student_id` int(11) NOT NULL default '0',
  `teacher_id` int(11) NOT NULL default '0',
  `due_date` date default NULL,
  `remark` text collate latin1_general_ci,
  PRIMARY KEY  (`guarantor_id`),
  UNIQUE KEY `sy_id_2` (`sy_id`,`student_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblguarantor
#



#
# Table structure for table tblmiscfee
#

DROP TABLE IF EXISTS `tblmiscfee`;
CREATE TABLE `tblmiscfee` (
  `miscfee_id` int(11) NOT NULL auto_increment,
  `enter_year` int(11) default NULL,
  `school_id` int(11) default NULL,
  `department_id` int(11) default NULL,
  `course_id` int(11) default NULL,
  `year_level` int(11) default NULL,
  `effective_year` int(11) default NULL,
  `feeelement_id` int(11) default NULL,
  `amount` bigint(20) default NULL,
  `semester_flag` int(11) default NULL,
  PRIMARY KEY  (`miscfee_id`),
  UNIQUE KEY `enter_year` (`enter_year`,`school_id`,`department_id`,`course_id`,`year_level`,`effective_year`,`feeelement_id`),
  KEY `school_id` (`school_id`),
  KEY `feeelement_id` (`feeelement_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblmiscfee
#



#
# Table structure for table tbloptionalfee
#

DROP TABLE IF EXISTS `tbloptionalfee`;
CREATE TABLE `tbloptionalfee` (
  `optionalfee_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) NOT NULL default '0',
  `school_id` int(11) NOT NULL default '0',
  `feeelement_id` int(11) NOT NULL default '0',
  `amount` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`optionalfee_id`),
  UNIQUE KEY `sy_id_2` (`sy_id`,`school_id`,`feeelement_id`),
  KEY `sy_id` (`sy_id`),
  KEY `school_id` (`school_id`),
  KEY `feeelement_id` (`feeelement_id`)
) ENGINE=MyISAM AUTO_INCREMENT=133 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tbloptionalfee
#

INSERT INTO `tbloptionalfee` VALUES (1,20051,2,48,0);
INSERT INTO `tbloptionalfee` VALUES (2,20051,1,48,0);
INSERT INTO `tbloptionalfee` VALUES (3,20051,2,55,10000);
INSERT INTO `tbloptionalfee` VALUES (4,20051,2,56,0);
INSERT INTO `tbloptionalfee` VALUES (5,20051,2,57,2500);
INSERT INTO `tbloptionalfee` VALUES (6,20051,2,58,2500);
INSERT INTO `tbloptionalfee` VALUES (7,20051,2,59,2000);
INSERT INTO `tbloptionalfee` VALUES (12,20051,1,57,2500);
INSERT INTO `tbloptionalfee` VALUES (13,20051,1,62,1000);
INSERT INTO `tbloptionalfee` VALUES (14,20051,1,55,10000);
INSERT INTO `tbloptionalfee` VALUES (15,20051,1,64,6000);
INSERT INTO `tbloptionalfee` VALUES (16,20051,3,60,1000);
INSERT INTO `tbloptionalfee` VALUES (17,20051,3,61,1500);
INSERT INTO `tbloptionalfee` VALUES (18,20051,3,62,1500);
INSERT INTO `tbloptionalfee` VALUES (19,20051,3,63,1000);
INSERT INTO `tbloptionalfee` VALUES (20,20051,3,57,2500);
INSERT INTO `tbloptionalfee` VALUES (21,20051,3,59,6000);
INSERT INTO `tbloptionalfee` VALUES (22,20051,3,56,0);
INSERT INTO `tbloptionalfee` VALUES (23,20051,3,58,2500);
INSERT INTO `tbloptionalfee` VALUES (24,20051,4,59,6000);
INSERT INTO `tbloptionalfee` VALUES (25,20051,4,57,2500);
INSERT INTO `tbloptionalfee` VALUES (26,20051,4,58,2500);
INSERT INTO `tbloptionalfee` VALUES (27,20051,4,60,1000);
INSERT INTO `tbloptionalfee` VALUES (28,20051,4,61,1500);
INSERT INTO `tbloptionalfee` VALUES (29,20051,4,62,1500);
INSERT INTO `tbloptionalfee` VALUES (30,20051,4,63,1000);
INSERT INTO `tbloptionalfee` VALUES (31,20051,4,56,0);
INSERT INTO `tbloptionalfee` VALUES (32,20051,2,62,1500);
INSERT INTO `tbloptionalfee` VALUES (33,20051,2,60,1000);
INSERT INTO `tbloptionalfee` VALUES (34,20051,2,61,1500);
INSERT INTO `tbloptionalfee` VALUES (35,20051,2,63,1000);
INSERT INTO `tbloptionalfee` VALUES (36,20051,1,65,0);
INSERT INTO `tbloptionalfee` VALUES (37,20051,1,66,0);
INSERT INTO `tbloptionalfee` VALUES (38,20051,2,66,0);
INSERT INTO `tbloptionalfee` VALUES (39,20051,3,66,0);
INSERT INTO `tbloptionalfee` VALUES (40,20051,4,66,0);
INSERT INTO `tbloptionalfee` VALUES (41,20052,2,48,0);
INSERT INTO `tbloptionalfee` VALUES (42,20052,2,55,10000);
INSERT INTO `tbloptionalfee` VALUES (43,20052,2,56,0);
INSERT INTO `tbloptionalfee` VALUES (44,20052,2,57,2500);
INSERT INTO `tbloptionalfee` VALUES (45,20052,2,58,2500);
INSERT INTO `tbloptionalfee` VALUES (46,20052,2,59,0);
INSERT INTO `tbloptionalfee` VALUES (47,20052,2,62,1500);
INSERT INTO `tbloptionalfee` VALUES (48,20052,2,60,1000);
INSERT INTO `tbloptionalfee` VALUES (49,20052,2,61,1500);
INSERT INTO `tbloptionalfee` VALUES (50,20052,2,63,1000);
INSERT INTO `tbloptionalfee` VALUES (51,20052,2,66,0);
INSERT INTO `tbloptionalfee` VALUES (53,20052,2,65,0);
INSERT INTO `tbloptionalfee` VALUES (54,20052,2,69,0);
INSERT INTO `tbloptionalfee` VALUES (82,20052,2,85,5000);
INSERT INTO `tbloptionalfee` VALUES (83,20052,2,86,30000);
INSERT INTO `tbloptionalfee` VALUES (84,20052,2,87,1500);
INSERT INTO `tbloptionalfee` VALUES (85,20052,2,88,3200);
INSERT INTO `tbloptionalfee` VALUES (86,20052,2,89,5500);
INSERT INTO `tbloptionalfee` VALUES (87,20052,2,90,10000);
INSERT INTO `tbloptionalfee` VALUES (88,20052,2,91,6000);
INSERT INTO `tbloptionalfee` VALUES (89,20052,3,86,0);
INSERT INTO `tbloptionalfee` VALUES (90,20052,3,91,10000);
INSERT INTO `tbloptionalfee` VALUES (91,20052,3,87,2500);
INSERT INTO `tbloptionalfee` VALUES (92,20052,3,88,3200);
INSERT INTO `tbloptionalfee` VALUES (93,20052,3,89,5500);
INSERT INTO `tbloptionalfee` VALUES (94,20052,3,90,10000);
INSERT INTO `tbloptionalfee` VALUES (95,20052,3,85,15000);
INSERT INTO `tbloptionalfee` VALUES (97,20052,4,85,20000);
INSERT INTO `tbloptionalfee` VALUES (98,20052,4,86,0);
INSERT INTO `tbloptionalfee` VALUES (99,20052,4,91,15000);
INSERT INTO `tbloptionalfee` VALUES (100,20052,4,87,2500);
INSERT INTO `tbloptionalfee` VALUES (101,20052,4,88,3200);
INSERT INTO `tbloptionalfee` VALUES (102,20052,4,89,5500);
INSERT INTO `tbloptionalfee` VALUES (103,20052,4,90,10000);
INSERT INTO `tbloptionalfee` VALUES (105,20051,2,92,0);
INSERT INTO `tbloptionalfee` VALUES (106,20052,2,93,0);
INSERT INTO `tbloptionalfee` VALUES (107,20052,3,93,0);
INSERT INTO `tbloptionalfee` VALUES (108,20052,4,93,0);
INSERT INTO `tbloptionalfee` VALUES (109,20053,2,59,2000);
INSERT INTO `tbloptionalfee` VALUES (110,20053,2,62,1500);
INSERT INTO `tbloptionalfee` VALUES (111,20053,2,57,2500);
INSERT INTO `tbloptionalfee` VALUES (112,20053,2,60,1000);
INSERT INTO `tbloptionalfee` VALUES (113,20053,2,66,0);
INSERT INTO `tbloptionalfee` VALUES (114,20053,2,61,1500);
INSERT INTO `tbloptionalfee` VALUES (115,20053,2,55,10000);
INSERT INTO `tbloptionalfee` VALUES (116,20053,2,63,1000);
INSERT INTO `tbloptionalfee` VALUES (117,20053,2,92,6500);
INSERT INTO `tbloptionalfee` VALUES (118,20053,2,58,2500);
INSERT INTO `tbloptionalfee` VALUES (119,20053,2,56,0);
INSERT INTO `tbloptionalfee` VALUES (120,20053,2,48,0);
INSERT INTO `tbloptionalfee` VALUES (121,20061,2,59,2000);
INSERT INTO `tbloptionalfee` VALUES (122,20061,2,62,1500);
INSERT INTO `tbloptionalfee` VALUES (123,20061,2,57,2500);
INSERT INTO `tbloptionalfee` VALUES (124,20061,2,60,1000);
INSERT INTO `tbloptionalfee` VALUES (125,20061,2,66,0);
INSERT INTO `tbloptionalfee` VALUES (126,20061,2,61,1500);
INSERT INTO `tbloptionalfee` VALUES (127,20061,2,55,10000);
INSERT INTO `tbloptionalfee` VALUES (128,20061,2,63,1000);
INSERT INTO `tbloptionalfee` VALUES (129,20061,2,92,6500);
INSERT INTO `tbloptionalfee` VALUES (130,20061,2,58,2500);
INSERT INTO `tbloptionalfee` VALUES (131,20061,2,56,0);
INSERT INTO `tbloptionalfee` VALUES (132,20061,2,48,0);


#
# Table structure for table tblpayment
#

DROP TABLE IF EXISTS `tblpayment`;
CREATE TABLE `tblpayment` (
  `payment_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `student_id` int(11) default NULL,
  `feeelement_id` int(11) default NULL,
  `payment` bigint(20) default NULL,
  `date` datetime default NULL,
  `orno` int(11) default NULL,
  `user_id` int(11) default NULL,
  PRIMARY KEY  (`payment_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `feeelement_id` (`feeelement_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblpayment
#



#
# Table structure for table tblpayment_extrainfo
#

DROP TABLE IF EXISTS `tblpayment_extrainfo`;
CREATE TABLE `tblpayment_extrainfo` (
  `orno` int(11) NOT NULL default '0',
  `payor` text collate latin1_general_ci,
  PRIMARY KEY  (`orno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblpayment_extrainfo
#



#
# Table structure for table tblregist
#

DROP TABLE IF EXISTS `tblregist`;
CREATE TABLE `tblregist` (
  `regist_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `student_id` int(11) default NULL,
  `class_id` int(11) default NULL,
  `date` date default NULL,
  `regist_flag` int(11) NOT NULL default '0',
  `grade_midterm` int(11) default NULL,
  `grade_final` int(11) default NULL,
  `grade_remark` int(11) default NULL,
  PRIMARY KEY  (`regist_id`),
  UNIQUE KEY `student_id_2` (`student_id`,`class_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  KEY `grade_remark` (`grade_remark`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblregist
#

INSERT INTO `tblregist` VALUES (1,20061,60001,1,'2007-09-19',1,NULL,NULL,NULL);


#
# Table structure for table tblroom
#

DROP TABLE IF EXISTS `tblroom`;
CREATE TABLE `tblroom` (
  `room_id` int(11) NOT NULL auto_increment,
  `room_code` varchar(16) collate latin1_general_ci default NULL,
  `building_id` int(11) default NULL,
  `description` text collate latin1_general_ci,
  PRIMARY KEY  (`room_id`),
  KEY `building_id` (`building_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblroom
#

INSERT INTO `tblroom` VALUES (1,'EB-1',1,'Lecture/Laboratory');
INSERT INTO `tblroom` VALUES (2,'EB-2',1,'Lecture');
INSERT INTO `tblroom` VALUES (3,'EB-3',1,'Lecture');
INSERT INTO `tblroom` VALUES (4,'EB-4',1,'Lecture');
INSERT INTO `tblroom` VALUES (5,'EB-5',1,'Lecture');
INSERT INTO `tblroom` VALUES (6,'EB-6',2,'Lecture');
INSERT INTO `tblroom` VALUES (7,'EB-7',2,'Lecture');
INSERT INTO `tblroom` VALUES (8,'EB-8',2,'Lecture/Laboratory');
INSERT INTO `tblroom` VALUES (9,'EB-9',2,'Lecture');


#
# Table structure for table tblscholarship
#

DROP TABLE IF EXISTS `tblscholarship`;
CREATE TABLE `tblscholarship` (
  `scholarship_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) NOT NULL default '0',
  `student_id` int(11) NOT NULL default '0',
  `scholartype_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`scholarship_id`),
  UNIQUE KEY `sy_id_2` (`sy_id`,`student_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `scholartype_id` (`scholartype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblscholarship
#



#
# Table structure for table tblscholartype
#

DROP TABLE IF EXISTS `tblscholartype`;
CREATE TABLE `tblscholartype` (
  `scholartype_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) NOT NULL default '0',
  `title` text collate latin1_general_ci,
  `tuition_deduction_rate` int(11) NOT NULL default '0',
  `tuition_deduction_amount` bigint(20) NOT NULL default '0',
  `flag` int(11) NOT NULL default '0',
  PRIMARY KEY  (`scholartype_id`),
  KEY `sy_id` (`sy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblscholartype
#



#
# Table structure for table tblschoollevel
#

DROP TABLE IF EXISTS `tblschoollevel`;
CREATE TABLE `tblschoollevel` (
  `school_id` int(11) NOT NULL auto_increment,
  `name` char(24) collate latin1_general_ci NOT NULL default '',
  PRIMARY KEY  (`school_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblschoollevel
#

INSERT INTO `tblschoollevel` VALUES (1,'Undergraduate');


#
# Table structure for table tblschoolyear
#

DROP TABLE IF EXISTS `tblschoolyear`;
CREATE TABLE `tblschoolyear` (
  `sy_id` int(11) NOT NULL auto_increment,
  `year` int(11) default NULL,
  `semester` int(11) default NULL,
  PRIMARY KEY  (`sy_id`),
  UNIQUE KEY `year` (`year`,`semester`)
) ENGINE=MyISAM AUTO_INCREMENT=20062 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblschoolyear
#

INSERT INTO `tblschoolyear` VALUES (20052,2005,2);
INSERT INTO `tblschoolyear` VALUES (20053,2005,3);
INSERT INTO `tblschoolyear` VALUES (20061,2006,1);


#
# Table structure for table tblstudentinfo
#

DROP TABLE IF EXISTS `tblstudentinfo`;
CREATE TABLE `tblstudentinfo` (
  `student_id` int(11) NOT NULL default '0',
  `first_name` text collate latin1_general_ci,
  `middle_name` text collate latin1_general_ci,
  `last_name` text collate latin1_general_ci,
  `civil_status` int(11) default NULL,
  `p_first_name` text collate latin1_general_ci,
  `p_middle_name` text collate latin1_general_ci,
  `p_last_name` text collate latin1_general_ci,
  `p_relation` text collate latin1_general_ci,
  `date_of_birth` date default NULL,
  `place_of_birth` text collate latin1_general_ci,
  `course_id` int(11) NOT NULL default '0',
  `enter_sy` int(11) NOT NULL default '0',
  `graduate_sy` int(11) NOT NULL default '0',
  `feebase_sy` int(11) NOT NULL default '0',
  `present_address` text collate latin1_general_ci,
  `home_address` text collate latin1_general_ci,
  `parent_address` text collate latin1_general_ci,
  `gender` char(1) collate latin1_general_ci default NULL,
  `elem_school` text collate latin1_general_ci,
  `elem_grad_year` int(11) default NULL,
  `second_school` text collate latin1_general_ci,
  `second_grad_year` int(11) default NULL,
  `course_completed` text collate latin1_general_ci,
  `last_school` text collate latin1_general_ci,
  `last_school_year` int(11) default NULL,
  PRIMARY KEY  (`student_id`),
  KEY `course_id` (`course_id`),
  KEY `enter_sy` (`enter_sy`),
  KEY `feebase_sy` (`feebase_sy`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblstudentinfo
#

INSERT INTO `tblstudentinfo` VALUES (60001,'Felisa','E.','Gomba',1,NULL,NULL,NULL,NULL,NULL,NULL,2,20061,0,20061,NULL,NULL,NULL,'F',NULL,NULL,NULL,NULL,NULL,NULL,NULL);


#
# Table structure for table tblstudentsenrolled
#

DROP TABLE IF EXISTS `tblstudentsenrolled`;
CREATE TABLE `tblstudentsenrolled` (
  `enroll_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) default NULL,
  `sy_id_end` int(11) default NULL,
  `student_id` int(11) default NULL,
  `date` date default NULL,
  `date_officially` date default NULL,
  `feebase_sy` int(11) NOT NULL default '0',
  `course_id` int(11) NOT NULL default '0',
  `year_level` int(11) default NULL,
  `section` int(11) default NULL,
  `campus_flag` int(11) default NULL,
  `date_dropped` datetime default NULL,
  `refund_rate` int(11) default NULL,
  PRIMARY KEY  (`enroll_id`),
  UNIQUE KEY `sy_id_2` (`sy_id`,`student_id`),
  KEY `sy_id` (`sy_id`),
  KEY `student_id` (`student_id`),
  KEY `course_id` (`course_id`),
  KEY `feebase_sy` (`feebase_sy`),
  KEY `sy_id_end` (`sy_id_end`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblstudentsenrolled
#

INSERT INTO `tblstudentsenrolled` VALUES (1,20061,20062,60001,'2007-09-19',NULL,20061,2,1,0,NULL,NULL,NULL);


#
# Table structure for table tblsyinfo
#

DROP TABLE IF EXISTS `tblsyinfo`;
CREATE TABLE `tblsyinfo` (
  `syinfo_id` int(11) NOT NULL auto_increment,
  `sy_id` int(11) NOT NULL default '0',
  `department_id` int(11) default NULL,
  `course_id` int(11) default NULL,
  `year_level` int(11) default NULL,
  `lastday_of_enrol` date NOT NULL default '0000-00-00',
  `lastday_of_changing` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`syinfo_id`),
  UNIQUE KEY `sy_id_2` (`sy_id`,`department_id`,`course_id`,`year_level`),
  KEY `sy_id` (`sy_id`),
  KEY `department_id` (`department_id`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblsyinfo
#

INSERT INTO `tblsyinfo` VALUES (1,20043,NULL,NULL,NULL,'2005-04-08','0000-00-00');
INSERT INTO `tblsyinfo` VALUES (2,20051,NULL,NULL,NULL,'2005-06-15','2005-08-15');
INSERT INTO `tblsyinfo` VALUES (3,20051,6,NULL,NULL,'2005-06-24','2005-08-15');
INSERT INTO `tblsyinfo` VALUES (5,20052,NULL,NULL,NULL,'2005-11-13','2005-12-23');
INSERT INTO `tblsyinfo` VALUES (6,20052,8,NULL,4,'2005-11-14','2005-12-23');
INSERT INTO `tblsyinfo` VALUES (7,20052,2,NULL,NULL,'2005-11-14','2005-12-23');
INSERT INTO `tblsyinfo` VALUES (8,20052,6,NULL,NULL,'2005-11-27','2005-12-23');
INSERT INTO `tblsyinfo` VALUES (9,20051,2,42,3,'2006-02-20','2006-02-20');
INSERT INTO `tblsyinfo` VALUES (12,20052,3,NULL,NULL,'2006-03-31','2006-03-31');
INSERT INTO `tblsyinfo` VALUES (13,20061,NULL,NULL,NULL,'2006-06-02','2006-06-02');
INSERT INTO `tblsyinfo` VALUES (14,20053,NULL,NULL,NULL,'2006-04-21','2006-04-28');
INSERT INTO `tblsyinfo` VALUES (15,20052,5,NULL,NULL,'2006-04-30','2006-04-30');
INSERT INTO `tblsyinfo` VALUES (16,20053,6,NULL,NULL,'2006-05-10','2006-05-10');
INSERT INTO `tblsyinfo` VALUES (17,20061,1,NULL,NULL,'2007-12-01','2007-12-01');


#
# Table structure for table tblteacher
#

DROP TABLE IF EXISTS `tblteacher`;
CREATE TABLE `tblteacher` (
  `teacher_id` int(11) NOT NULL default '0',
  `first_name` text collate latin1_general_ci,
  `middle_name` text collate latin1_general_ci,
  `last_name` text collate latin1_general_ci,
  `title` text collate latin1_general_ci,
  `rank` text collate latin1_general_ci,
  `position` text collate latin1_general_ci,
  `department_id` int(11) default NULL,
  `address` text collate latin1_general_ci,
  `noclass` smallint(6) default NULL,
  `date_of_birth` date default NULL,
  `doctor_degree` text collate latin1_general_ci,
  `master_degree` text collate latin1_general_ci,
  `bachelor_degree` text collate latin1_general_ci,
  PRIMARY KEY  (`teacher_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblteacher
#

INSERT INTO `tblteacher` VALUES (1,'Taku','X','Iwamura','Mr.',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL);


#
# Table structure for table tblteacherpos
#

DROP TABLE IF EXISTS `tblteacherpos`;
CREATE TABLE `tblteacherpos` (
  `position_id` int(11) NOT NULL auto_increment,
  `teacher_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`position_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

#
# Dumping data for table tblteacherpos
#

INSERT INTO `tblteacherpos` VALUES (1,10001);
INSERT INTO `tblteacherpos` VALUES (2,10002);
INSERT INTO `tblteacherpos` VALUES (3,10012);
INSERT INTO `tblteacherpos` VALUES (4,40003);

COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
