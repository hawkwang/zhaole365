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

//installing language pack
OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('zlareas')->getRootDir().'langs.zip', 'zlareas');

//adding admin settings page
OW::getPluginManager()->addPluginSettingsRouteName('zlareas', 'zlareas.admin');
