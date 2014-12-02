<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$uheader = OW::getPluginManager()->getPlugin('uheader');

//Routs
$router = OW::getRouter();
$router->addRoute(new OW_Route('uheader-settings-page', 'admin/plugins/profile-cover', 'UHEADER_CTRL_Admin', 'index'));
$router->addRoute(new OW_Route('uheader-settings-gallery', 'admin/plugins/profile-cover/gallery', 'UHEADER_CTRL_Templates', 'index'));
$router->addRoute(new OW_Route('uheader-settings-gallery-item', 'admin/plugins/profile-cover/gallery/:tplId', 'UHEADER_CTRL_Templates', 'index'));

// Bridges
UHEADER_CLASS_PhotoBridge::getInstance()->init();
UHEADER_CLASS_NewsfeedBridge::getInstance()->init();
UHEADER_CLASS_PrivacyBridge::getInstance()->init();
UHEADER_CLASS_NotificationsBridge::getInstance()->init();
UHEADER_CLASS_CommentsBridge::getInstance()->init();
UHEADER_CLASS_CreditsBridge::getInstance()->init();
UHEADER_CLASS_UavatarsBridge::getInstance()->init();
UHEADER_CLASS_BaseBridge::getInstance()->init();

//Event handler
UHEADER_CLASS_EventHandler::getInstance()->init();