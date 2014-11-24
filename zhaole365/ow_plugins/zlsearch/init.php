<?php


$plugin = OW::getPluginManager()->getPlugin('zlsearch');

//init routes
OW::getRouter()->addRoute(new OW_Route('zlsearch.index', 'search', 'ZLSEARCH_CTRL_Search', 'index'));


