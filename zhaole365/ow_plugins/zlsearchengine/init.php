<?php


$plugin = OW::getPluginManager()->getPlugin('zlsearchengine');

//init routes
OW::getRouter()->addRoute(new OW_Route('zlsearchengine.admin', 'admin/plugins/searchengine', 'ZLSEARCHENGINE_CTRL_Admin', 'index'));

$handler = new ZLSEARCHENGINE_CLASS_EventHandler();
$handler->init();