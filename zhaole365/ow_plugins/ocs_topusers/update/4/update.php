<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /init.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers
 * @since 1.2.6
 */

$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'ocstopusers');