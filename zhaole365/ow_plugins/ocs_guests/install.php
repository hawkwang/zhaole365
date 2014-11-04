<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /install.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests
 * @since 1.3.1
 */

$config = OW::getConfig();

if ( !$config->configExists('ocsguests', 'store_period') )
{
    $config->addConfig('ocsguests', 'store_period', 3, 'Guests visit period, months');
}

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."ocsguests_guest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `guestId` int(11) NOT NULL,
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  `visitTimestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`guestId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('ocsguests', 'ocsguests.admin');

$path = OW::getPluginManager()->getPlugin('ocsguests')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'ocsguests');
