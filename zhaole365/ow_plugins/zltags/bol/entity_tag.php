<?php

class ZLTAGS_BOL_EntityTag extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $tagEntityId;
    /**
     * @var integer
     */
    public $tagId;
    /**
     * @var integer
     */
    public $createStamp;

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function getTagEntityId()
    {
        return $this->tagEntityId;
    }

    public function setTagEntityId( $tagEntityId )
    {
        $this->tagEntityId = (int) $tagEntityId;
    }

    public function getTagId()
    {
    	return $this->tagId;
    }
    
    public function setTagId( $tagId )
    {
    	$this->tagId = (int) $tagId;
    }
        
    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage( $message )
    {
        $this->message = trim($message);
    }

    public function getCreateStamp()
    {
        return $this->createStamp;
    }

    public function setCreateStamp( $createStamp )
    {
        $this->createStamp = (int) $createStamp;
    }
}

