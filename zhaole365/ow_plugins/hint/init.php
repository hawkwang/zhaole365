<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

OW::getRouter()->addRoute(new OW_Route('hint-configuration', 'admin/plugins/hint', 'HINT_CTRL_Admin', 'index'));

HINT_CLASS_ParseManager::getInstance()->init();
HINT_CLASS_ParseManager::getInstance()->addParser(new HINT_CLASS_UserParser());

HINT_CLASS_FriendsBridge::getInstance()->init();
HINT_CLASS_NewsfeedBridge::getInstance()->init();
HINT_CLASS_BaseBridge::getInstance()->init();
HINT_CLASS_GiftBridge::getInstance()->init();
HINT_CLASS_McomposeBridge::getInstance()->init();
HINT_CLASS_UheaderBridge::getInstance()->init();
HINT_CLASS_UserCreditsBridge::getInstance()->init();
HINT_CLASS_PhotoBridge::getInstance()->init();
HINT_CLASS_VideoBridge::getInstance()->init();

function hint_add_admin_notification( BASE_CLASS_EventCollector $e )
{
    
    if ( HINT_BOL_Service::getInstance()->getConfig("admin_notified") )
    {
        return;
    }
    
    $pluginTitle = OW::getPluginManager()->getPlugin("hint")->getDto()->title;
    $pluginUrl = OW::getRouter()->urlForRoute('hint-configuration');
    $pluginEmbed = '<a href="' . $pluginUrl . '">' . $pluginTitle . '</a>';
    
    $language = OW::getLanguage();
    $e->add($language->text('hint', 'admin_notification', array('plugin' => $pluginEmbed, "settingsUrl" => $pluginUrl)));
}
OW::getEventManager()->bind('admin.add_admin_notification', 'hint_add_admin_notification');
