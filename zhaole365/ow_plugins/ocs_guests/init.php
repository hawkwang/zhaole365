<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /init.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests
 * @since 1.3.1
 */

OW::getRouter()->addRoute(
    new OW_Route('ocsguests.admin', '/admin/plugins/ocsguests', 'OCSGUESTS_CTRL_Admin', 'index')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsguests.list', '/guests/list', 'OCSGUESTS_CTRL_List', 'index')
);

OCSGUESTS_CLASS_EventHandler::getInstance()->init();