<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Data Transfer Object for `ocsguests_guest` table.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests.bol
 * @since 1.3.1
 */
class OCSGUESTS_BOL_Guest extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $guestId;
    /**
     * @var int
     */
    public $viewed;
    /**
     * @var int
     */
    public $visitTimestamp;
}