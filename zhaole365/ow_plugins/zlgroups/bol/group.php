<?php

class ZLGROUPS_BOL_Group extends OW_Entity
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $timeStamp;

    /**
     *
     * @var string
     */
    public $imageHash;

    /**
     *
     * @var int
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $whoCanView;

    /**
     *
     * @var string
     */
    public $whoCanInvite;
}
