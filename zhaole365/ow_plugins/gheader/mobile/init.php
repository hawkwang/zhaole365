<?php

require_once dirname(dirname(__FILE__)) . DS . 'plugin.php';

$plugin = GHEADER_Plugin::getInstance();

if ( $plugin->isAvaliable() )
{
    $plugin->mobileInit();
}

function gheader_disable_formats( OW_Event $event )
{
    $params = $event->getParams();

    if ( !in_array($params["action"]["entityType"], array( GHEADER_CLASS_CommentsBridge::ENTITY_TYPE )) )
    {
        return;
    }

    $data = $event->getData();

    $data["disabled"] = true;

    $event->setData($data);
}
OW::getEventManager()->bind('feed.on_item_render', "gheader_disable_formats");