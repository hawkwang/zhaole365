<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

require_once 'plugin.php';

$plugin = GHEADER_Plugin::getInstance();

//Routs
$router = OW::getRouter();
$router->addRoute(new OW_Route('gheader-settings-page', 'admin/plugins/group-cover', 'GHEADER_CTRL_Admin', 'index'));

if ( $plugin->isAvaliable() )
{
    $plugin->fullInit();
}
else
{
   $plugin->shortInit();
}

function gheader_on_plugin_deactivate( OW_Event $event )
{
    $params = $event->getParams();
    $pluginKey = $params['pluginKey'];

    if ( $pluginKey != 'groups' )
    {
        return;
    }

    GHEADER_Plugin::getInstance()->shortDeactivate();
}
OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, 'gheader_on_plugin_deactivate');

function gheader_on_plugin_activate( OW_Event $event )
{
    $params = $event->getParams();
    $pluginKey = $params['pluginKey'];

    if ( $pluginKey != 'groups' )
    {
        return;
    }

    GHEADER_Plugin::getInstance()->fullActivate();
}
OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, 'gheader_on_plugin_activate');