<?php

class ZLEVENT_BOL_Event extends OW_Entity
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $location;
    /**
     * @var string
     */
    public $description;
    /**
     * @var integer
     */
    public $createTimeStamp;
    /**
     * @var integer
     */
    public $startTimeStamp;
    /**
     * @var integer
     */
    public $endTimeStamp;
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $whoCanView;
    /**
     * @var integer
     */
    public $whoCanInvite;
    /**
     * @var integer
     */
    public $status = 1;
    /**
     * @var string
     */
    public $image = null;
    /**
     * @var boolean
     */
    public $endDateFlag = false;
    /**
     * @var boolean
     */
    public $startTimeDisabled = false;
    /**
     * @var boolean
     */
    public $endTimeDisabled = false;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle( $title )
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription( $description )
    {
        $this->description = $description;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation( $location )
    {
        $this->location = $location;
    }

    public function getCreateTimeStamp()
    {
        return $this->createTimeStamp;
    }

    public function setCreateTimeStamp( $createTimeStamp )
    {
        $this->createTimeStamp = $createTimeStamp;
    }

    public function getStartTimeStamp()
    {
        return $this->startTimeStamp;
    }

    public function setStartTimeStamp( $startTimeStamp )
    {
        $this->startTimeStamp = $startTimeStamp;
    }

    public function getEndTimeStamp()
    {
        return $this->endTimeStamp;
    }

    public function setEndTimeStamp( $endTimeStamp )
    {
        $this->endTimeStamp = $endTimeStamp;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = $userId;
    }

    public function getWhoCanView()
    {
        return $this->whoCanView;
    }

    public function setWhoCanView( $whoCanView )
    {
        $this->whoCanView = $whoCanView;
    }

    public function getWhoCanInvite()
    {
        return $this->whoCanInvite;
    }

    public function setWhoCanInvite( $whoCanInvite )
    {
        $this->whoCanInvite = $whoCanInvite;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus( $status )
    {
        $this->status = $status;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage( $image )
    {
        $this->image = $image;
    }

    public function getEndDateFlag()
    {
        return $this->endDateFlag;
    }

    public function setEndDateFlag( $flag )
    {
        $this->endDateFlag = (boolean)$flag;
    }

    public function getStartTimeDisable()
    {
        return $this->startTimeDisabled;
    }

    public function setStartTimeDisable( $flag )
    {
        $this->startTimeDisabled = (boolean)$flag;
    }
    
    public function getEndTimeDisable()
    {
        return $this->endTimeDisabled;
    }

    public function setEndTimeDisable( $flag )
    {
        $this->endTimeDisabled = (boolean)$flag;
    }

}

