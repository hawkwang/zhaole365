<?php

class ZLEVENT_BOL_EventInvite extends OW_Entity
{
    /**
     * @var integer
     */
    public $eventId;
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
     * @var boolean
     */
    public $displayInvitation;

    public function getEventId()
    {
        return $this->eventId;
    }

    public function setEventId( $eventId )
    {
        $this->eventId = $eventId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = $userId;
    }

    public function getInviterId()
    {
        return $this->inviterId;
    }

    public function setInviterId( $inviterId )
    {
        $this->inviterId = $inviterId;
    }

    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    public function setTimeStamp( $timeStamp )
    {
        $this->timeStamp = $timeStamp;
    }

    public function getDisplayInvitation()
    {
        return $this->displayInvitation;
    }

    public function setDisplayInvitation( $displayInvitation )
    {
        $this->displayInvitation = (boolean) $displayInvitation;
    }
}

