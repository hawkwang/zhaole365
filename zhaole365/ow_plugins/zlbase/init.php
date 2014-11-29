<?php


$plugin = OW::getPluginManager()->getPlugin('zlbase');

//init routes
OW::getRouter()->addRoute(new OW_Route('zlbase.admin', 'admin/plugins/zlbase', 'ZLBASE_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('zlbase.about', 'about', 'ZLBASE_CTRL_Base', 'about'));
OW::getRouter()->addRoute(new OW_Route('zlbase.duty', 'duty', 'ZLBASE_CTRL_Base', 'duty'));
OW::getRouter()->addRoute(new OW_Route('zlbase.agreement', 'agreement', 'ZLBASE_CTRL_Base', 'agreement'));



