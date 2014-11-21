<?php


$plugin = OW::getPluginManager()->getPlugin('zlareas');

//init routes
OW::getRouter()->addRoute(new OW_Route('zlareas.admin', 'admin/plugins/areas', 'ZLAREAS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('zlareas.index', 'areas', 'ZLAREAS_CTRL_Areas', 'index'));

//测试解决首页问题 FIXME
//OW::getRouter()->addRoute(new OW_Route('zlareas.index.default', '/', 'ZLAREAS_CTRL_Areas', 'index'));
//OW::getRouter()->removeRoute('zlareas.index.default');
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

