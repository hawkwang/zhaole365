<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

try
{
    OW::getPluginManager()->addPluginSettingsRouteName('hint', 'hint-configuration');
}
catch ( Exception $e )
{
    // Log
}

OW::getConfig()->addConfig("hint", "uhint_enabled", 1);
OW::getConfig()->addConfig("hint", "admin_notified", 0);

OW::getConfig()->addConfig("hint", "info_user_line1", json_encode(array(
    "key" => "base-gender-age",
    "question" => null
)));

OW::getConfig()->addConfig("hint", "info_user_line2", json_encode(array(
    "key" => "friends-list",
    "question" => null
)));


OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('hint')->getRootDir() . 'langs.zip', 'hint');
