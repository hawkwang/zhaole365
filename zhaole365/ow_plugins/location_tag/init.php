<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.location_tag
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('locationtag_admin', 'admin/plugins/locationtag', 'LOCATIONTAG_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('locationtag_map', 'tag/map/:tagId', 'LOCATIONTAG_CTRL_Map', 'map'));

$handler = new LOCATIONTAG_CLASS_EventHandler();
$handler->genericInit();
$handler->init();