<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.location_tag
 * @since 1.0
 */

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "locationtag_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` int(11) NOT NULL,
  `entityType` varchar(32) NOT NULL,
  `countryCode` varchar(10) NOT NULL,
  `address` varchar(255) NOT NULL,
  `lat` DECIMAL( 15, 4 ) NOT NULL,
  `lng` DECIMAL( 15, 4 ) NOT NULL,
  `northEastLat` DECIMAL( 15, 4 ) NOT NULL,
  `northEastLng` DECIMAL( 15, 4 ) NOT NULL,
  `southWestLat` DECIMAL( 15, 4 ) NOT NULL,
  `southWestLng` DECIMAL( 15, 4 ) NOT NULL,
  `json` TEXT NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `entityId` (`entityId`, `entityType`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('locationtag', 'locationtag_admin');


if ( !OW::getConfig()->configExists('locationtag', 'api_key') )
{
    OW::getConfig()->addConfig('locationtag', 'api_key', '', 'API key');
}

OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('locationtag')->getRootDir() . 'langs.zip', 'locationtag');