<?php

BOL_LanguageService::getInstance()->addPrefix('zlbase', '找乐基盘');

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zlbase_base_property` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`entityType` VARCHAR(50) NOT NULL,
	`entityId` int(11) NOT NULL,
	`key` VARCHAR(20) NOT NULL,
	`value` text NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `entityType` (`entityType`,`entityId`,`key`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8";

//installing database
OW::getDbo()->query($sql);

//installing language pack
OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('zlbase')->getRootDir().'langs.zip', 'zlareas');

//adding admin settings page
OW::getPluginManager()->addPluginSettingsRouteName('zlbase', 'zlbase.admin');
