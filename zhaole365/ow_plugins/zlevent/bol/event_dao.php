<?php

class ZLEVENT_BOL_EventDao extends OW_BaseDao
{
    const TITLE = 'title';
    const LOCATION = 'location';
    const CREATE_TIME_STAMP = 'createTimeStamp';
    const START_TIME_STAMP = 'startTimeStamp';
    const END_TIME_STAMP = 'endTimeStamp';
    const USER_ID = 'userId';
    const WHO_CAN_VIEW = 'whoCanView';
    const WHO_CAN_INVITE = 'whoCanInvite';
    const STATUS = 'status';

    const VALUE_WHO_CAN_INVITE_CREATOR = 1;
    const VALUE_WHO_CAN_INVITE_PARTICIPANT = 2;
    const VALUE_WHO_CAN_VIEW_ANYBODY = 1;
    const VALUE_WHO_CAN_VIEW_INVITATION_ONLY = 2;

    const CACHE_LIFE_TIME = 86400;

    const CACHE_TAG_PUBLIC_EVENT_LIST = 'zlevent_public_event_list';
    const CACHE_TAG_EVENT_LIST = 'zlevent_event_list';

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        parent::__construct();
    }

    public function getDtoClassName()
    {
        return 'ZLEVENT_BOL_Event';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlevent_item';
    }

    public function findPublicEvents( $first, $count, $past = false )
    {
        $where = " `" . self::WHO_CAN_VIEW . "` = :wcv ";
        $params = array('wcv' => self::VALUE_WHO_CAN_VIEW_ANYBODY, 'startTime' => time(), 'endTime' => time(), 'first' => (int) $first, 'count' => (int) $count);

        if ( OW::getUser()->isAuthorized('zlevent') )
        {
            $params = array('startTime' => time(), 'endTime' => time(), 'first' => (int) $first, 'count' => (int) $count);
            $where = " 1 ";
        }

        if ( $past )
        {
            $query = "SELECT * FROM `" . $this->getTableName() . "` WHERE " . $where . "
                AND " . $this->getTimeClause(true) . " ORDER BY `startTimeStamp` DESC LIMIT :first, :count";
        }
        else
        {
            $query = "SELECT * FROM `" . $this->getTableName() . "` WHERE " . $where . "
                AND " . $this->getTimeClause() . " ORDER BY `startTimeStamp` LIMIT :first, :count";
        }

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
    }

    // FIXME－ 没有考虑事件时间的因素
    public function findExpiredEventsForCronJobs( $first, $count )
    {        
        $params = array('first' => (int) $first, 'count' => (int) $count);
        
        $query = " SELECT DISTINCT `e`.* FROM `" . $this->getTableName() . "` as `e` "
               . " INNER JOIN `" . ZLEVENT_BOL_EventInviteDao::getInstance()->getTableName() . "` as `ei` ON ( `ei`.eventId = e.id ) "
               . " WHERE 1 ORDER BY `e`.`startTimeStamp` DESC LIMIT :first, :count";
        
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
    }
    
    /**
     * @return integer
     */
    public function findPublicEventsCount( $past = false )
    {
        if ( $past )
        {
            $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` WHERE `" . self::WHO_CAN_VIEW . "` = :wcv AND " . $this->getTimeClause(true);
        }
        else
        {
            $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` WHERE `" . self::WHO_CAN_VIEW . "` = :wcv AND " . $this->getTimeClause();
        }

        return $this->dbo->queryForColumn($query, array('wcv' => self::VALUE_WHO_CAN_VIEW_ANYBODY, 'startTime' => time(), 'endTime' => time()));
    }

    /**
     * Returns events with user status.
     *
     * @param integer $userId
     * @param integer $userStatus
     * @param integer $first
     * @param inetger $count
     * @return array
     */
    public function findUserEventsWithStatus( $userId, $userStatus, $first, $count )
    {
        $query = "SELECT `e`.* FROM `" . $this->getTableName() . "` AS `e`
            LEFT JOIN `" . ZLEVENT_BOL_EventUserDao::getInstance()->getTableName() . "` AS `eu` ON (`e`.`id` = `eu`.`eventId`)
            WHERE `eu`.`userId` = :userId AND `eu`.`" . ZLEVENT_BOL_EventUserDao::STATUS . "` = :status AND " . $this->getTimeClause(false, 'e') . "
            ORDER BY `" . self::START_TIME_STAMP . "` LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('userId' => $userId, 'status' => $userStatus, 'first' => $first, 'count' => $count, 'startTime' => time(), 'endTime' => time()));
    }

    /**
     * @param integer $userId
     * @param integer $status
     * @return integer
     */
    public function findUserEventsCountWithStatus( $userId, $status )
    {
        $query = "SELECT COUNT(*) AS `count` FROM `" . $this->getTableName() . "` AS `e`
            LEFT JOIN `" . ZLEVENT_BOL_EventUserDao::getInstance()->getTableName() . "` AS `eu` ON (`e`.`id` = `eu`.`eventId`)
            WHERE `eu`.`userId` = :userId AND `eu`.`" . ZLEVENT_BOL_EventUserDao::STATUS . "` = :status AND " . $this->getTimeClause(false, 'e');

        return (int) $this->dbo->queryForColumn($query, array('userId' => $userId, 'status' => $status, 'startTime' => time(), 'endTime' => time()));
    }

    /**
     * Returns events with user status.
     *
     * @param integer $userId
     * @param integer $userStatus
     * @param integer $first
     * @param inetger $count
     * @return array
     */
    public function findPublicUserEventsWithStatus( $userId, $userStatus, $first, $count )
    {
        $query = "SELECT `e`.* FROM `" . $this->getTableName() . "` AS `e`
            LEFT JOIN `" . ZLEVENT_BOL_EventUserDao::getInstance()->getTableName() . "` AS `eu` ON (`e`.`id` = `eu`.`eventId`)
            WHERE `eu`.`userId` = :userId AND `eu`.`" . ZLEVENT_BOL_EventUserDao::STATUS . "` = :status AND " . $this->getTimeClause(false, 'e') . " AND `e`.`" . self::WHO_CAN_VIEW . "` = " . self::VALUE_WHO_CAN_VIEW_ANYBODY . "
            ORDER BY `" . self::START_TIME_STAMP . "` LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('userId' => $userId, 'status' => $userStatus, 'first' => $first, 'count' => $count, 'startTime' => time(), 'endTime' => time()));
    }

    /**
     * @param integer $userId
     * @param integer $status
     * @return integer
     */
    public function findPublicUserEventsCountWithStatus( $userId, $status )
    {
        $query = "SELECT COUNT(*) AS `count` FROM `" . $this->getTableName() . "` AS `e`
            LEFT JOIN `" . ZLEVENT_BOL_EventUserDao::getInstance()->getTableName() . "` AS `eu` ON (`e`.`id` = `eu`.`eventId`)
            WHERE `eu`.`userId` = :userId AND `eu`.`" . ZLEVENT_BOL_EventUserDao::STATUS . "` = :status AND " . $this->getTimeClause(false, 'e') . " AND `e`.`" . self::WHO_CAN_VIEW . "` = " . self::VALUE_WHO_CAN_VIEW_ANYBODY . "";

        return (int) $this->dbo->queryForColumn($query, array('userId' => $userId, 'status' => $status, 'startTime' => time(), 'endTime' => time()));
    }

    /**
     * Returns user created events.
     *
     * @param integer $userId
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findUserCreatedEvents( $userId, $first, $count )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        $example->setOrder(self::START_TIME_STAMP );
        $example->andFieldGreaterThan(self::START_TIME_STAMP, time());
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }

    /**
     * @param integer $userId
     * @return integer
     */
    public function findUserCreatedEventsCount( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        $example->andFieldGreaterThan(self::START_TIME_STAMP, time());

        return $this->countByExample($example);
    }

    /**
     * @param integer $userId
     * @return array<ZLEVENT_BOL_Event>
     */
    public function findUserInvitedEvents( $userId, $first, $count )
    {
        $query = "SELECT `e`.* FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . ZLEVENT_BOL_EventInviteDao::getInstance()->getTableName() . "` AS `ei` ON ( `e`.`id` = `ei`.`" . ZLEVENT_BOL_EventInviteDao::EVENT_ID . "` )
            WHERE `ei`.`" . ZLEVENT_BOL_EventInviteDao::USER_ID . "` = :userId AND " . $this->getTimeClause(false, 'e') . "
            GROUP BY `e`.`id` LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('userId' => (int) $userId, 'first' => (int) $first, 'count' => (int) $count, 'startTime' => time(), 'endTime' => time()));
    }

    /**
     * @param integer $userId
     * @return integer
     */
    public function findUserInvitedEventsCount( $userId )
    {
        $query = "SELECT COUNT(*) AS `count` FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . ZLEVENT_BOL_EventInviteDao::getInstance()->getTableName() . "` AS `ei` ON ( `e`.`id` = `ei`.`" . ZLEVENT_BOL_EventInviteDao::EVENT_ID . "` )
            WHERE `ei`.`" . ZLEVENT_BOL_EventInviteDao::USER_ID . "` = :userId AND " . $this->getTimeClause(false, 'e') . " GROUP BY `e`.`id`";

        return $this->dbo->queryForColumn($query, array('userId' => (int) $userId, 'startTime' => time(), 'endTime' => time()));
    }

    /**
     * @param integer $userId
     * @return integer
     */
    public function findDisplayedUserInvitationCount( $userId )
    {
        $query = "SELECT COUNT(*) AS `count` FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . ZLEVENT_BOL_EventInviteDao::getInstance()->getTableName() . "` AS `ei` ON ( `e`.`id` = `ei`.`" . ZLEVENT_BOL_EventInviteDao::EVENT_ID . "` )
            WHERE `ei`.`" . ZLEVENT_BOL_EventInviteDao::USER_ID . "` = :userId AND `ei`.`displayInvitation` = true AND " . $this->getTimeClause(false, 'e') . " GROUP BY `e`.`id`";

        return $this->dbo->queryForColumn($query, array('userId' => (int) $userId, 'startTime' => time(), 'endTime' => time()));
    }


    /**
     * @param integer $userId
     * @return array<ZLEVENT_BOL_Event>
     */
    public function findAllUserEvents( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        
        return $this->findListByExample($example);
    }

    private function getTimeClause( $past = false, $alias = null )
    {
        if ( $past )
        {
            return "( " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::START_TIME_STAMP . "` <= :startTime AND ( " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::END_TIME_STAMP . "` IS NULL OR " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::END_TIME_STAMP . "` <= :endTime ) )";
        }

        return "( " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::START_TIME_STAMP . "` > :startTime OR ( " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::END_TIME_STAMP . "` IS NOT NULL AND " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::END_TIME_STAMP . "` > :endTime ) )";
    }
    
    // 乐群相关
    
    // 得到隶属乐群的公众可见群乐对象列表
    public function findPublicEventsCountByGroupId( $groupId, $past = false )
    {
    		$query = "SELECT COUNT(*) AS `count` FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . ZLEVENT_BOL_EventGroupDao::getInstance()->getTableName() . "` AS `eg` ON ( `e`.`id` = `eg`.`" . ZLEVENT_BOL_EventGroupDao::EVENT_ID . "` )
            WHERE `eg`.`" . ZLEVENT_BOL_EventGroupDao::GROUP_ID . "` = :groupId AND " . $this->getTimeClause($past, 'e') . " AND `e`.`" .self::WHO_CAN_VIEW . "` = :wcv";
    
    	return $this->dbo->queryForColumn($query, array( 'groupId' => $groupId , 'wcv' => self::VALUE_WHO_CAN_VIEW_ANYBODY, 'startTime' => time(), 'endTime' => time()));
    }
    
    public function findPublicEventsByGroupId( $groupId, $first, $count, $past = false )
    {
    	$sortstyle = "ASC";
    	if($past)
    		$sortstyle = "DESC";
    	
    	$query = "SELECT * FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . ZLEVENT_BOL_EventGroupDao::getInstance()->getTableName() . "` AS `eg` ON ( `e`.`id` = `eg`.`" . ZLEVENT_BOL_EventGroupDao::EVENT_ID . "` )
            WHERE `eg`.`" . ZLEVENT_BOL_EventGroupDao::GROUP_ID . "` = :groupId AND " . $this->getTimeClause($past, 'e') . " 
            AND `e`.`" .self::WHO_CAN_VIEW . "` = :wcv order by " . self::START_TIME_STAMP  . " " . $sortstyle . " LIMIT :first, :count";
    
    	return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array( 'groupId' => $groupId , 'first' => (int) $first, 'count' => (int) $count, 'wcv' => self::VALUE_WHO_CAN_VIEW_ANYBODY, 'startTime' => time(), 'endTime' => time()));
    }

    
    // 得到隶属乐群的所有群乐对象列表
    public function findAllEventsCountByGroupId( $groupId, $past = false )
    {
    	$query = "SELECT * FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . ZLEVENT_BOL_EventGroupDao::getInstance()->getTableName() . "` AS `eg` ON ( `e`.`id` = `eg`.`" . ZLEVENT_BOL_EventGroupDao::EVENT_ID . "` )
            WHERE `eg`.`" . ZLEVENT_BOL_EventGroupDao::GROUP_ID . "` = :groupId AND " . $this->getTimeClause($past, 'e') ;
    
    	return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array( 'groupId' => $groupId , 'startTime' => time(), 'endTime' => time()));
    }
    
    public function findAllEventsByGroupId( $groupId, $past = false )
    {
    	$sortstyle = "ASC";
    	if($past)
    		$sortstyle = "DESC";
    	
    	$query = "SELECT * FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . ZLEVENT_BOL_EventGroupDao::getInstance()->getTableName() . "` AS `eg` ON ( `e`.`id` = `eg`.`" . ZLEVENT_BOL_EventGroupDao::EVENT_ID . "` )
            WHERE `eg`.`" . ZLEVENT_BOL_EventGroupDao::GROUP_ID . "` = :groupId 
            AND " . $this->getTimeClause($past, 'e') . " order by " . self::START_TIME_STAMP  . " " . $sortstyle;
    
    	return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array( 'groupId' => $groupId , 'startTime' => time(), 'endTime' => time()));
    }
    
    public function findLatestEventByGroupId( $groupId, $past = false )
    {
    	$sortstyle = "ASC";
    	if($past)
    		$sortstyle = "DESC";
    	 
    	$query = "SELECT * FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . ZLEVENT_BOL_EventGroupDao::getInstance()->getTableName() . "` AS `eg` ON ( `e`.`id` = `eg`.`" . ZLEVENT_BOL_EventGroupDao::EVENT_ID . "` )
            WHERE `eg`.`" . ZLEVENT_BOL_EventGroupDao::GROUP_ID . "` = :groupId
            AND " . $this->getTimeClause($past, 'e') . " order by " . self::START_TIME_STAMP  . " " . $sortstyle;
    
    	return $this->dbo->queryForObject($query, $this->getDtoClassName(), array( 'groupId' => $groupId , 'startTime' => time(), 'endTime' => time()));
    }
    
}
