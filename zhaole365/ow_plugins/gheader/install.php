<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$plugin = OW::getPluginManager()->getPlugin('gheader');

$P = OW_DB_PREFIX;

$sql = array();

$sql[] = "CREATE TABLE `{$P}gheader_cover` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL,
  `settings` text NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupId_2` (`groupId`,`status`),
  KEY `groupId` (`groupId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";


foreach ( $sql as $query )
{
    try
    {
        OW::getDbo()->query($query);
    }
    catch ( Exception $e )
    {
        //Log
    }
}

try
{
    OW::getPluginManager()->addPluginSettingsRouteName('gheader', 'gheader-settings-page');
}
catch ( Exception $e )
{
    // Log
}

try 
{
    $authorization = OW::getAuthorization();
    $groupName = 'gheader';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'view_cover', true);
    $authorization->addAction($groupName, 'add_cover');
    $authorization->addAction($groupName, 'add_comment');
    $authorization->addAction($groupName, 'delete_comment_by_content_owner');
}
catch ( Exception $e )
{
    // Log
}

try 
{
    BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', $plugin->getKey());
}
catch ( Exception $e )
{
    // Log
}
