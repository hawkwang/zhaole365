<?php

class ZLEVENT_BOL_EventUser extends OW_Entity
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
    public $timeStamp;
    /**
     * @var integer
     */
    public $status = 0;

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

    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    public function setTimeStamp( $timeStamp )
    {
        $this->timeStamp = $timeStamp;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus( $status )
    {
        $this->status = $status;
    }
}

