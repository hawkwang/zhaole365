<?php


OW::getRouter()->addRoute(new OW_Route('zlgroups-view', 'zlgroups/:groupId', 'ZLGROUPS_MCTRL_Groups', 'view'));

ZLGROUPS_CLASS_EventHandler::getInstance()->genericInit();
$eventHandler = ZLGROUPS_MCLASS_EventHandler::getInstance();

OW::getEventManager()->bind('mobile.invitations.on_item_render', array($eventHandler, 'onInvitationsItemRender'));
OW::getEventManager()->bind('invitations.on_command', array($eventHandler, 'onInvitationCommand'));
OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRenderDisableActions"));

ZLGROUPS_CLASS_ConsoleBridge::getInstance()->genericInit();