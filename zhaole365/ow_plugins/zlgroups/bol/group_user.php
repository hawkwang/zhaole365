<?php

class ZLGROUPS_BOL_GroupUser extends OW_Entity
{
    /**
     * @var int
     */
    public $groupId;
    /**
     * @var int
     */
    public $userId;
    /**
     * 
     * @var string
     */
    public $timeStamp;
    
    public $privacy;
    
    public function __construct()
    {
        $this->privacy = ZLGROUPS_BOL_Service::PRIVACY_EVERYBODY;
    }
}
