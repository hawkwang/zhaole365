<?php
$plugin = OW::getPluginManager()->getPlugin('zltags');

BOL_LanguageService::getInstance()->addPrefix('zltags', '找乐标签');

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zltags_tag` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`tag` VARCHAR(50) NOT NULL,
	`userId` int(11) NOT NULL,
	`createStamp` int(11) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zltags_tag_entity` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`entityType` VARCHAR(50) NOT NULL,
	`entityId` int(11) NOT NULL,
	`pluginKey` varchar(50) NOT NULL,
	`active` tinyint(4) NOT NULL default 1,
	PRIMARY KEY (`id`),
	UNIQUE KEY `entityType` (`entityType`,`entityId`),
    KEY `pluginKey` (`pluginKey`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zltags_entity_tag` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`userId` int(11) NOT NULL,
	`tagEntityId` int(11) NOT NULL,
	`tagId` int(11) NOT NULL,
	`createStamp` int(11) NOT NULL,
	PRIMARY KEY (`id`),
    KEY `userId` (`userId`),
    KEY `tagEntityId` (`tagEntityId`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);


//installing language pack
OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('zltags')->getRootDir().'langs.zip', 'zltags');

