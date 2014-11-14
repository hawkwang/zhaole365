<?php

class ZLEVENT_BOL_EventUserDao extends OW_BaseDao
{
    const EVENT_ID = 'eventId';
    const USER_ID = 'userId';
    const TIME_STAMP = 'timeStamp';
    const STATUS = 'status';

    const VALUE_STATUS_YES = 1;
    const VALUE_STATUS_MAYBE = 2;
    const VALUE_STATUS_NO = 3;

    const CACHE_TAG_EVENT_USER_LIST = 'zlevent_users_list_event_id_';

    const CACHE_LIFE_TIME = 86400; //24 hour

    /**
     * Singleton instance.
     *
     * @var ZLEVENT_BOL_EventUserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ZLEVENT_BOL_EventUserDao
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
        return 'ZLEVENT_BOL_EventUser';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlevent_user';
    }

    public function deleteByEventId( $id )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::EVENT_ID, (int) $id);

        $this->deleteByExample($example);
    }

    public function findListByEventIdAndStatus( $eventId, $status, $first, $count )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("e", "userId", array(
            "method" => "ZLEVENT_BOL_EventUserDao::findListByEventIdAndStatus"
        ));

        $query = " SELECT e.* FROM  " . $this->getTableName() . " e
                    " . $queryParts['join'] . "
                    WHERE " . $queryParts['where'] . "  AND e.`".self::EVENT_ID."` = :eventId AND e.`" . self::STATUS . "` = :status
                    LIMIT :first, :count " ;

        return $this->dbo->queryForObjectList( $query, $this->getDtoClassName(), array( 'eventId' => (int)$eventId, 'status' => (int)$status, 'first' => $first, 'count' => $count ) );
    }

    public function findUsersCountByEventIdAndStatus( $eventId, $status )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("e", "userId", array(
            "method" => "ZLEVENT_BOL_EventUserDao::findUsersCountByEventIdAndStatus"
        ));

        $query = " SELECT count(e.id) FROM  " . $this->getTableName() . " e
                    " . $queryParts['join'] . "
                    WHERE " . $queryParts['where'] . " AND e.`".self::EVENT_ID."` = :eventId AND e.`" . self::STATUS . "` = :status ";

        return $this->dbo->queryForColumn( $query, array( 'eventId' => (int)$eventId, 'status' => (int)$status ), self::CACHE_LIFE_TIME, array(self::CACHE_TAG_EVENT_USER_LIST . $eventId) );
    }

    /**
     * @param integer $eventId
     * @param integer $userId
     * @return ZLEVENT_BOL_EventUser
     */
    public function findObjectByEventIdAndUserId( $eventId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::EVENT_ID, (int) $eventId);
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param integer $userId
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findByUserId( $userId, $first, $count )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }
}
