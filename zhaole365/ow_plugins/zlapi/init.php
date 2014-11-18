<?php


$plugin = OW::getPluginManager()->getPlugin('zlapi');

//init routes
OW::getRouter()->addRoute(new OW_Route('zlapi.index', 'anotherapi', 'ZLAPI_CTRL_Api', 'index'));

