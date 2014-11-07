<?php



BOL_LanguageService::getInstance()->addPrefix('zlareas', '找乐地盘');

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zlareas_area` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`areacode` VARCHAR(20) NOT NULL,
	`province` VARCHAR(30) NOT NULL,
	`city` VARCHAR(30) NOT NULL,
	`area` VARCHAR(30) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8";

//installing database
OW::getDbo()->query($sql);
//初始化数据
$sql = "INSERT INTO `wx_zlareas_area` VALUES (1,'110101','北京市','北京市','东城区'),(2,'110102','北京市','北京市','西城区'),(3,'110103','北京市','北京市','崇文区'),
		(4,'110104','北京市','北京市','宣武区'),(5,'110105','北京市','北京市','朝阳区'),(6,'110106','北京市','北京市','丰台区'),
		(7,'110107','北京市','北京市','石景山区'),(8,'110108','北京市','北京市','海淀区'),(9,'110109','北京市','北京市','门头沟区'),(10,'110111','北京市','北京市','房山区'),
		(11,'110112','北京市','北京市','通州区'),(12,'110113','北京市','北京市','顺义区'),(13,'110114','北京市','北京市','昌平区'),(14,'110115','北京市','北京市','大兴区'),
		(15,'110116','北京市','北京市','怀柔区'),(16,'110117','北京市','北京市','平谷区'),(17,'110228','北京市','北京市','密云县'),(18,'110229','北京市','北京市','延庆县'),
		(19,'310101','上海市','上海市','黄浦区'),(20,'310103','上海市','上海市','卢湾区'),(21,'310104','上海市','上海市','徐汇区'),(22,'310105','上海市','上海市','长宁区'),
		(23,'310106','上海市','上海市','静安区'),(24,'310107','上海市','上海市','普陀区'),(25,'310108','上海市','上海市','闸北区'),(26,'310109','上海市','上海市','虹口区'),
		(27,'310110','上海市','上海市','杨浦区'),(28,'310112','上海市','上海市','闵行区'),(29,'310113','上海市','上海市','宝山区'),(30,'310114','上海市','上海市','嘉定区'),
		(31,'310115','上海市','上海市','浦东新区'),(32,'310116','上海市','上海市','金山区'),(33,'310117','上海市','上海市','松江区'),(34,'310118','上海市','上海市','青浦区'),
		(35,'310119','上海市','上海市','南汇区'),(36,'310120','上海市','上海市','奉贤区'),(37,'310200','上海市','上海市','区级县'),(38,'310230','上海市','上海市','崇明县');";
OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zlareas_location` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `address` varchar(100) NOT NULL,
  `longitude` float( 10, 6 ) NOT NULL,
  `latitude` float( 10, 6 ) NOT NULL,
  `areacode` varchar(20) NOT NULL,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) 
ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

//installing language pack
OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('zlareas')->getRootDir().'langs.zip', 'zlareas');

//adding admin settings page
OW::getPluginManager()->addPluginSettingsRouteName('zlareas', 'zlareas.admin');
