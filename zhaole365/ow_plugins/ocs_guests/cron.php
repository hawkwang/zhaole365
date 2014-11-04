<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Guests plugin cron job.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests
 * @since 1.3.1
 */
class OCSGUESTS_Cron extends OW_Cron
{    
    public function __construct()
    {
        parent::__construct();

        $this->addJob('guestsCheckProcess', 60);
    }

    public function run() { }

    public function guestsCheckProcess()
    {
        OCSGUESTS_BOL_Service::getInstance()->checkExpiredGuests();
    }
}