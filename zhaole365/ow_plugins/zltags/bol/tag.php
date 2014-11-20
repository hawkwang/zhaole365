<?php

class ZLTAGS_BOL_Tag extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var string
     */
    public $tag;
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

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag( $tag )
    {
        $this->tag = trim($tag);
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

