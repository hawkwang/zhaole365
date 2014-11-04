<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /deactivate.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers
 * @since 1.2.6
 */
BOL_ComponentAdminService::getInstance()->deleteWidget('OCSTOPUSERS_CMP_RateUserWidget');

BOL_ComponentAdminService::getInstance()->deleteWidget('OCSTOPUSERS_CMP_IndexWidget');

BOL_ComponentAdminService::getInstance()->deleteWidget('OCSTOPUSERS_CMP_DashboardWidget');