<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /deactivate.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests
 * @since 1.3.1
 */

BOL_ComponentAdminService::getInstance()->deleteWidget('OCSGUESTS_CMP_MyGuestsWidget');