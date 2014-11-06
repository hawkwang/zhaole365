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
 */

try
{

        $sql = "ALTER TABLE `".OW_DB_PREFIX."locationtag_data` CHANGE `entityType` `entityType` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
        
        Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }

try
{

        $sql = "UPDATE `".OW_DB_PREFIX."locationtag_data` SET `entityType` = 'user-status' WHERE `entityType` = 'status_update' ";
        
        Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }
