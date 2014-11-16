<?php

$plugin = OW::getPluginManager()->getPlugin('zlevent');
$router = OW::getRouter();
$router->addRoute(new OW_Route('zlevent.add', 'zlevent/add', 'ZLEVENT_CTRL_Base', 'add'));
$router->addRoute(new OW_Route('zlevent.edit', 'zlevent/edit/:eventId', 'ZLEVENT_CTRL_Base', 'edit'));
$router->addRoute(new OW_Route('zlevent.delete', 'zlevent/delete/:eventId', 'ZLEVENT_CTRL_Base', 'delete'));
$router->addRoute(new OW_Route('zlevent.view', 'zlevent/:eventId', 'ZLEVENT_CTRL_Base', 'view'));
$router->addRoute(new OW_Route('zlevent.main_menu_route', 'zlevents', 'ZLEVENT_CTRL_Base', 'eventsList', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
$router->addRoute(new OW_Route('zlevent.view_event_list', 'zlevents/:list', 'ZLEVENT_CTRL_Base', 'eventsList'));
$router->addRoute(new OW_Route('zlevent.main_user_list', 'zlevent/:eventId/users', 'ZLEVENT_CTRL_Base', 'eventUserLists', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'yes'))));
$router->addRoute(new OW_Route('zlevent.user_list', 'zlevent/:eventId/users/:list', 'ZLEVENT_CTRL_Base', 'eventUserLists'));
$router->addRoute(new OW_Route('zlevent.private_event', 'zlevent/:eventId/private', 'ZLEVENT_CTRL_Base', 'privateEvent'));
$router->addRoute(new OW_Route('zlevent.invite_accept', 'zlevent/:eventId/:list/invite_accept', 'ZLEVENT_CTRL_Base', 'inviteListAccept'));
$router->addRoute(new OW_Route('zlevent.invite_decline', 'zlevent/:eventId/:list/invite_decline', 'ZLEVENT_CTRL_Base', 'inviteListDecline'));

$eventHandler = new ZLEVENT_CLASS_EventHandler();
$eventHandler->genericInit();
$eventHandler->init();

