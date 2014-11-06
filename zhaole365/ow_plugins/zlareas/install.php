<?php



BOL_LanguageService::getInstance()->addPrefix('zlareas', '找乐地盘');

$sql = "CREATE TABLE `" . OW_DB_PREFIX . "zlareas_area` (
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

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zllocations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `address` varchar(100) NOT NULL,
  `longitude` float( 10, 6 ) NOT NULL,
  `latitude` float( 10, 6 ) NOT NULL,
  `areacode` varchar(20) NOT NULL,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

//installing language pack
OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('zlareas')->getRootDir().'langs.zip', 'zlareas');

//adding admin settings page
OW::getPluginManager()->addPluginSettingsRouteName('zlareas', 'zlareas.admin');
