<?php


$plugin = OW::getPluginManager()->getPlugin('zlgroups');

//Admin Routs
OW::getRouter()->addRoute(new OW_Route('zlgroups-admin-widget-panel', 'admin/plugins/zlgroups', 'ZLGROUPS_CTRL_Admin', 'panel'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-admin-additional-features', 'admin/plugins/zlgroups/additional', 'ZLGROUPS_CTRL_Admin', 'additional'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-admin-uninstall', 'admin/plugins/zlgroups/uninstall', 'ZLGROUPS_CTRL_Admin', 'uninstall'));

//Frontend Routs
// 用于创建新乐群
OW::getRouter()->addRoute(new OW_Route('zlgroups-create', 'zlgroups/create', 'ZLGROUPS_CTRL_Groups', 'create'));
// 用于编辑指定的乐群
OW::getRouter()->addRoute(new OW_Route('zlgroups-edit', 'zlgroups/:groupId/edit', 'ZLGROUPS_CTRL_Groups', 'edit'));
// 用于显示定制的指定乐群页面
OW::getRouter()->addRoute(new OW_Route('zlgroups-customize', 'zlgroups/:groupId/customize', 'ZLGROUPS_CTRL_Groups', 'customize'));
// 用于显示指定的乐群页面
OW::getRouter()->addRoute(new OW_Route('zlgroups-view', 'zlgroups/:groupId', 'ZLGROUPS_CTRL_Groups', 'view'));
// 当前用户加入指定乐群
OW::getRouter()->addRoute(new OW_Route('zlgroups-join', 'zlgroups/:groupId/join', 'ZLGROUPS_CTRL_Groups', 'join'));
// 显示最流行乐群列表
OW::getRouter()->addRoute(new OW_Route('zlgroups-most-popular', 'zlgroups/most-popular', 'ZLGROUPS_CTRL_Groups', 'mostPopularList'));
// 显示最近乐群列表
OW::getRouter()->addRoute(new OW_Route('zlgroups-latest', 'zlgroups/latest', 'ZLGROUPS_CTRL_Groups', 'latestList'));
// 显示我被邀请加入的乐群列表
OW::getRouter()->addRoute(new OW_Route('zlgroups-invite-list', 'zlgroups/invitations', 'ZLGROUPS_CTRL_Groups', 'inviteList'));
// 显示我的乐群列表页面
OW::getRouter()->addRoute(new OW_Route('zlgroups-my-list', 'zlgroups/my', 'ZLGROUPS_CTRL_Groups', 'myGroupList'));
// 用于显示乐群列表首页面
OW::getRouter()->addRoute(new OW_Route('zlgroups-index', 'zlgroups', 'ZLGROUPS_CTRL_Groups', 'index'));
// 用于显示指定用户的所有乐群
OW::getRouter()->addRoute(new OW_Route('zlgroups-user-groups', 'users/:user/zlgroups', 'ZLGROUPS_CTRL_Groups', 'userGroupList'));
OW::getRouter()->addRoute(new OW_Route('zlgroups-leave', 'zlgroups/:groupId/leave', 'ZLGROUPS_CTRL_Groups', 'leave'));
// 用户显示指定乐群用户列表
OW::getRouter()->addRoute(new OW_Route('zlgroups-user-list', 'zlgroups/:groupId/users', 'ZLGROUPS_CTRL_Groups', 'userList'));
// 用户私有乐群
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

//
ZLGROUPS_CLASS_ConsoleBridge::getInstance()->genericInit();