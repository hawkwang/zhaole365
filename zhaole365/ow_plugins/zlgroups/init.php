<?php


$plugin = OW::getPluginManager()->getPlugin('zlgroups');

//Admin Routs
OW::getRouter()->addRoute(new OW_Route('zlgroups-admin-widget-panel', 'admin/plugins/zlgroups', 'ZLGROUPS_CTRL_Admin', 'panel'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-admin-additional-features', 'admin/plugins/zlgroups/additional', 'ZLGROUPS_CTRL_Admin', 'additional'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-admin-uninstall', 'admin/plugins/zlgroups/uninstall', 'ZLGROUPS_CTRL_Admin', 'uninstall'));

//Frontend Routs
OW::getRouter()->addRoute(new OW_Route('zlgroups-create', 'zlgroups/create', 'ZLGROUPS_CTRL_Groups', 'create'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-edit', 'zlgroups/:groupId/edit', 'ZLGROUPS_CTRL_Groups', 'edit'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-view', 'zlgroups/:groupId', 'ZLGROUPS_CTRL_Groups', 'view'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-join', 'zlgroups/:groupId/join', 'ZLGROUPS_CTRL_Groups', 'join'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-customize', 'zlgroups/:groupId/customize', 'ZLGROUPS_CTRL_Groups', 'customize'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-most-popular', 'zlgroups/most-popular', 'ZLGROUPS_CTRL_Groups', 'mostPopularList'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-latest', 'zlgroups/latest', 'ZLGROUPS_CTRL_Groups', 'latestList'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-invite-list', 'zlgroups/invitations', 'ZLGROUPS_CTRL_Groups', 'inviteList'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-my-list', 'zlgroups/my', 'ZLGROUPS_CTRL_Groups', 'myGroupList'));

OW::getRouter()->addRoute(new OW_Route('zlgroups-index', 'zlgroups', 'ZLGROUPS_CTRL_Groups', 'index'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-user-zlgroups', 'users/:user/zlgroups', 'ZLGROUPS_CTRL_Groups', 'userGroupList'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-leave', 'zlgroups/:groupId/leave', 'ZLGROUPS_CTRL_Groups', 'leave'));

OW::getRouter()->addRoute(new OW_Route('zlgroups-user-list', 'zlgroups/:groupId/users', 'ZLGROUPS_CTRL_Groups', 'userList'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-private-group', 'zlgroups/:groupId/private', 'ZLGROUPS_CTRL_Groups', 'privateGroup'));

// ???
OW::getRegistry()->addToArray(BASE_CMP_AddNewContent::REGISTRY_DATA_KEY,
    array(
        BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_comment',
        BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('zlgroups-create'),
        BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('zlgroups', 'add_new_label')
));

// 建立事件和相应handler的关联
$eventHandler = ZLGROUPS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

// 建立论坛事件和相应handler的关联
OW::getEventManager()->bind('forum.activate_plugin', array($eventHandler, "onForumActivate"));
OW::getEventManager()->bind('forum.find_forum_caption', array($eventHandler, "onForumFindCaption"));
OW::getEventManager()->bind('forum.uninstall_plugin', array($eventHandler, "onForumUninstall"));
OW::getEventManager()->bind('forum.collect_widget_places', array($eventHandler, "onForumCollectWidgetPlaces"));

// 建立feed事件和相应handler的关联
OW::getEventManager()->bind('feed.collect_widgets', array($eventHandler, "onFeedCollectWidgets"));
OW::getEventManager()->bind('feed.on_widget_construct', array($eventHandler, "onFeedWidgetConstruct"));
OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRender"));

// 建立admin.add_admin_notification事件和相应handler的关联
OW::getEventManager()->bind('admin.add_admin_notification', array($eventHandler, "onCollectAdminNotifications"));

// 建立base.add_quick_link事件和相应handler的关联
OW::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($eventHandler, 'onCollectQuickLinks'));

//
ZLGROUPS_CLASS_ConsoleBridge::getInstance()->init();