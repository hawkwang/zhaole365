<?php

class ZLGROUPS_BOL_Invite extends OW_Entity
{
    /**
     * @var integer
     */
    public $groupId;
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $inviterId;
    /**
     * @var integer
     */
    public $timeStamp;

    /**
     *
     * @var integer
     */
    public $viewed;
}

