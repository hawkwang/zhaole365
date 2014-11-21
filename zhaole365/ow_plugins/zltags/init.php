<?php


$plugin = OW::getPluginManager()->getPlugin('zltags');

//init routes
// OW::getRouter()->addRoute(new OW_Route('zltags.add', 'tags/add', 'ZLTAGS_CTRL_tags', 'add'));
// OW::getRouter()->addRoute(new OW_Route('zltags.delete', 'tags/delete', 'ZLTAGS_CTRL_tags', 'delete'));

$eventHandler = new ZLTAGS_CLASS_EventHandler();
$eventHandler->genericInit();


