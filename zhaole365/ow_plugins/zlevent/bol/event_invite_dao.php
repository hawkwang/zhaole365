<?php

class ZLEVENT_BOL_EventInviteDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const INVITER_ID = 'inviterId';
    const TIME_STAMP = 'timeStamp';
    const EVENT_ID = 'eventId';

    /**
     * Singleton instance.
     *
     * @var ZLEVENT_BOL_EventInviteDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ZLEVENT_BOL_EventInviteDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'ZLEVENT_BOL_EventInvite';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlevent_invite';
    }

    /**
     * @param integer $eventId
     * @param integer $userId
     * @return ZLEVENT_BOL_EventInvite
     */
    public function findObjectByUserIdAndEventId( $eventId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::EVENT_ID, (int) $eventId);
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param integer $eventId
     * @param integer $userId
     */
    public function hideInvitationByUserId( $userId )
    {
        $query = "UPDATE `" . ZLEVENT_BOL_EventInviteDao::getInstance()->getTableName() . "` SET `displayInvitation` = false 
            WHERE `" . ZLEVENT_BOL_EventInviteDao::USER_ID . "` = :userId AND `displayInvitation` = true ";

        return $this->dbo->update($query, array('userId' => (int) $userId));
    }

    /**
     * @param integer $eventId
     */
    public function deleteByEventId( $eventId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::EVENT_ID, (int) $eventId);

        $this->deleteByExample($example);
    }

    /**
     * @param integer $eventId
     * @param integer $userId
     */
    public function deleteByUserIdAndEventId( $eventId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::EVENT_ID, (int)$eventId);
        $example->andFieldEqual(self::USER_ID, (int)$userId);

        $this->deleteByExample($example);
    }

    /**
     * @param integer $eventId
     */
    public function findInviteListByEventId( $eventId)
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::EVENT_ID, (int)$eventId);

        return $this->findListByExample($example);
    }

    /**
     * @param integer $eventId
     */
    public function findUserListForInvite( $eventId, $first, $count, $friendList = null )
    {

        $userDao = BOL_UserDao::getInstance();
        $eventDao = ZLEVENT_BOL_EventDao::getInstance();
        $eventUserDao = ZLEVENT_BOL_EventUserDao::getInstance();

        $where = "";
        if ( isset($friendList) && empty($friendList) )
        {
            return array();
        }
        else if ( !empty($friendList) )
        {
            $where = " AND `u`.id IN ( " . $this->dbo->mergeInClause($friendList) . " ) ";
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "ZLEVENT_BOL_EventUserDao::findUserListForInvite"
        ));

        $query = "SELECT `u`.`id`
    		FROM `{$userDao->getTableName()}` as `u`
            " . $queryParts['join'] . "
                
            LEFT JOIN `" . $eventDao->getTableName() . "` as `e`
    			ON( `u`.`id` = `e`.`userId` AND e.id = :event )
                
            LEFT JOIN `" . $this->getTableName() . "` as `ei`
    			ON( `u`.`id` = `ei`.`userId` AND `ei`.eventId = :event )

            LEFT JOIN `" . $eventUserDao->getTableName() . "` as `eu`
    			ON( `u`.`id` = `eu`.`userId` AND `eu`.eventId = :event )

    		WHERE  " . $queryParts['where'] . " AND `e`.`id` IS NULL AND `ei`.`id` IS NULL AND `eu`.`id` IS NULL ". $where ."
    		ORDER BY `u`.`activityStamp` DESC
    		LIMIT :first, :count ";

        return $this->dbo->queryForColumnList($query, array('event' => $eventId, 'first' => $first, 'count' => $count));
    }
}
