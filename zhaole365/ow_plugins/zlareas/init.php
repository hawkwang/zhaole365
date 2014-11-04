<?php


$plugin = OW::getPluginManager()->getPlugin('zlareas');

//init routes
OW::getRouter()->addRoute(new OW_Route('zlareas.admin', 'admin/plugins/areas', 'ZLAREAS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('zlareas.index', 'areas', 'ZLAREAS_CTRL_Areas', 'index'));

//测试解决首页问题 FIXME
OW::getRouter()->addRoute(new OW_Route('zlareas.index.default', '/', 'ZLAREAS_CTRL_Areas', 'index'));

//init components
// OW::getRegistry()->addToArray(BASE_CMP_AddNewContent::REGISTRY_DATA_KEY,
//     array(
//         BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_comment',
//         BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('groups-create'),
//         BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('groups', 'add_new_label')
// ));

//init event handler
// $eventHandler = ZLAREAS_CLASS_EventHandler::getInstance();
// $eventHandler->genericInit();

function zlareas_handler_after_install( BASE_CLASS_EventCollector $event )
{
	if ( count(ZLAREAS_BOL_Service::getInstance()->getAreaList()) < 1 )
	{
		$url = OW::getRouter()->urlForRoute('zlareas.admin');
		$event->add(OW::getLanguage()->text('zlareas', 'after_install_notification', array('url' => $url)));
	}
}

OW::getEventManager()->bind('admin.add_admin_notification', 'zlareas_handler_after_install');


OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'ZLAREAS_CTRL_Areas');

