<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$plugin = OW::getPluginManager()->getPlugin('uheader');

$P = OW_DB_PREFIX;

$sql = array();

$sql[] = "CREATE TABLE IF NOT EXISTS `{$P}uheader_cover` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL,
  `settings` text NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `templateId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_2` (`userId`,`status`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `{$P}uheader_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` varchar(255) NOT NULL,
  `settings` text NOT NULL,
  `default` int(11) NOT NULL DEFAULT '0',
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `{$P}uheader_template_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `templateId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `templateId` (`templateId`,`roleid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

foreach ( $sql as $query )
{
    OW::getDbo()->query($query);
}

OW::getConfig()->addConfig('uheader', 'cover_height', '250', 'Cover height in pixels');
OW::getConfig()->addConfig('uheader', 'photo_share', '1', 'Add cover images to user photos');
OW::getConfig()->addConfig('uheader', 'tpl_view_mode', 'list', 'Cover gallery view mode');

OW::getPluginManager()->addPluginSettingsRouteName('uheader', 'uheader-settings-page');

$authorization = OW::getAuthorization();
$groupName = 'uheader';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view_cover', true);
$authorization->addAction($groupName, 'add_cover');
$authorization->addAction($groupName, 'add_comment');

BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', $plugin->getKey());

try {
    OW::getAutoloader()->addPackagePointer("UHEADER_BOL", $plugin->getBolDir());
    OW::getAutoloader()->addPackagePointer("UHEADER_CLASS", $plugin->getClassesDir());
} catch (Exception $e) {}

try {
    //Add default templates
    $templatesBridge = UHEADER_CLASS_TemplatesBridge::getInstance();
    $templatesBridge->addBuiltInCovers();
} catch (Exception $e) {}

try {
    UHEADER_BOL_Service::getInstance()->saveInfoConfig(UHEADER_BOL_Service::INFO_LINE1, "base-gender-age");
    UHEADER_BOL_Service::getInstance()->saveInfoConfig(UHEADER_BOL_Service::INFO_LINE2, "base-about");
} catch (Exception $e) {}