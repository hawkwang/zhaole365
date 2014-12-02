<?php

require_once 'plugin.php';

$plugin = ZLGHEADER_Plugin::getInstance();

//Routs
$router = OW::getRouter();
$router->addRoute(new OW_Route('zlgheader-settings-page', 'admin/plugins/zlgroup-cover', 'ZLGHEADER_CTRL_Admin', 'index'));

if ( $plugin->isAvaliable() )
{
    $plugin->fullInit();
}
else
{
   $plugin->shortInit();
}

function zlgheader_on_plugin_deactivate( OW_Event $event )
{
    $params = $event->getParams();
    $pluginKey = $params['pluginKey'];

    if ( $pluginKey != 'zlgroups' )
    {
        return;
    }

    ZLGHEADER_Plugin::getInstance()->shortDeactivate();
}
OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, 'zlgheader_on_plugin_deactivate');

function zlgheader_on_plugin_activate( OW_Event $event )
{
    $params = $event->getParams();
    $pluginKey = $params['pluginKey'];

    if ( $pluginKey != 'zlgroups' )
    {
        return;
    }

    ZLGHEADER_Plugin::getInstance()->fullActivate();
}
OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, 'zlgheader_on_plugin_activate');