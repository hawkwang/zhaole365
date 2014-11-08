<?php
//"乐群"数据访问对象
class ZLGROUPS_BOL_GroupDao extends OW_BaseDao
{
    const LIST_CACHE_LIFETIME = 86400;
    const LIST_CACHE_TAG = 'zlgroups.list';
    const LIST_CACHE_TAG_LATEST = 'zlgroups.list.latest';
    const LIST_CACHE_TAG_POPULAR = 'zlgroups.list.popular';

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'ZLGROUPS_BOL_Group';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlgroups_group';
    }

    //TODO Privacy filter
    //根据first和count获得按照乐群创建时间降序排序（先最新）后的列表
    public function findOrderedList( $first, $count )
    {
        $example = new OW_Example();
        $example->setOrder('`timeStamp` DESC');
        $example->setLimitClause($first, $count);

        if ( !OW::getUser()->isAuthorized('zlgroups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $example->andFieldEqual('whoCanView', ZLGROUPS_BOL_Service::WCV_ANYONE);
        }

        return $this->findListByExample($example, self::LIST_CACHE_LIFETIME, array( self::LIST_CACHE_TAG, self::LIST_CACHE_TAG_LATEST ));
    }

    //获得count个乐群列表
    public function findLimitedList( $count )
    {
        $example = new OW_Example();
        $example->setLimitClause(0, $count);

        return $this->findListByExample($example);
    }

    //获得最受欢迎的乐群列表
    //受欢迎程度度量 是 乐群用户数
    public function findMostPupularList( $first, $count )
    {
        $groupUserTable = ZLGROUPS_BOL_GroupUserDao::getInstance()->getTableName();

        $where = '';

        if ( !OW::getUser()->isAuthorized('zlgroups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $where = 'WHERE g.whoCanView="' . ZLGROUPS_BOL_Service::WCV_ANYONE . '"';
        }

        $query = "SELECT `g`.* FROM `" . $this->getTableName() . "` AS `g`
            LEFT JOIN `" . $groupUserTable . "` AS `gu` ON `g`.`id` = `gu`.`groupId`
            $where
            GROUP BY `g`.`id` ORDER BY COUNT(`gu`.`id`) DESC LIMIT :f, :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'f' => $first,
            'c' => $count
        ), self::LIST_CACHE_LIFETIME, array( self::LIST_CACHE_TAG, self::LIST_CACHE_TAG_POPULAR ));
    }

    //获得乐群总数
    public function findAllCount()
    {
        $example = new OW_Example();

        // TBU -  to be understood
        if ( !OW::getUser()->isAuthorized('zlgroups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $example->andFieldEqual('whoCanView', GROUPS_BOL_Service::WCV_ANYONE);
        }

        return $this->countByExample($example);
    }

    //根据名称获得乐群对象
    public function findByTitle( $title )
    {
        $example = new OW_Example();
        $example->andFieldEqual('title', $title);

        return $this->findObjectByExample($example);
    }

    //获得指定用户的所有隶属乐群
    public function findAllUserGroups( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }

    //获得指定用户的部分关注乐群
    public function findByUserId( $userId, $first = null, $count = null )
    {
        $groupUserDao = ZLGROUPS_BOL_GroupUserDao::getInstance();

        $limit = '';
        if ( $first !== null && $count !== null )
        {
            $limit = "LIMIT $first, $count";
        }

        $wcvWhere = '1';

        if ( !OW::getUser()->isAuthorized('zlgroups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $wcvWhere = 'g.whoCanView="' . ZLGROUPS_BOL_Service::WCV_ANYONE . '"';
        }

        $query = "SELECT g.* FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId
            WHERE u.userId=:u AND $wcvWhere " . $limit;

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'u' => $userId
        ));
    }

    //获得指定用户关注的乐群总数
    public function findCountByUserId( $userId )
    {
        $groupUserDao = ZLGROUPS_BOL_GroupUserDao::getInstance();

        $wcvWhere = '1';

        if ( !OW::getUser()->isAuthorized('zlgroups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $wcvWhere = 'g.whoCanView="' . ZLGROUPS_BOL_Service::WCV_ANYONE . '"';
        }

        $query = "SELECT COUNT(g.id) FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId
            WHERE u.userId=:u AND $wcvWhere ";

        return (int) $this->dbo->queryForColumn($query, array(
            'u' => $userId
        ));
    }

    //获得指定用户的部分关注乐群  TBU － 和findByUserId的区别？？？
    public function findMyGroups( $userId, $first = null, $count = null )
    {
        $groupUserDao = ZLGROUPS_BOL_GroupUserDao::getInstance();

        $limit = '';
        if ( $first !== null && $count !== null )
        {
            $limit = "LIMIT $first, $count";
        }

        $query = "SELECT g.* FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId
            WHERE u.userId=:u " . $limit;

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'u' => $userId
        ));
    }

    //获得指定用户关注的乐群总数
    public function findMyGroupsCount( $userId )
    {
        $groupUserDao = ZLGROUPS_BOL_GroupUserDao::getInstance();

        $query = "SELECT COUNT(g.id) FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId
            WHERE u.userId=:u";

        return (int) $this->dbo->queryForColumn($query, array(
            'u' => $userId
        ));
    }

    //设定乐群创建人的隐私权（everybody：表示对所有人可见）
    public function setPrivacy( $userId, $privacy )
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET privacy=:p WHERE userId=:u';

        $this->dbo->query($query, array(
            'p' => $privacy,
            'u' => $userId
        ));
    }


    //查找指定用户被邀请的乐群列表
    public function findUserInvitedGroups( $userId, $first, $count )
    {
        $query = "SELECT DISTINCT `g`.* FROM `" . $this->getTableName() . "` AS `g`
            INNER JOIN `" . ZLGROUPS_BOL_InviteDao::getInstance()->getTableName() . "` AS `i` ON ( `g`.`id` = `i`.`groupId` )
            WHERE `i`.`userId` = :u
            ORDER BY `i`.`timeStamp` DESC LIMIT :f, :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'u' => (int) $userId,
            'f' => (int) $first,
            'c' => (int) $count
        ));
    }

    //获取用户被邀请的乐群个数（$newOnly为true，表明只统计用户未查看过的邀请）
    public function findUserInvitedGroupsCount( $userId, $newOnly = false )
    {
        $addWhere = $newOnly ? 'i.viewed=0' : '1';

        $query = "SELECT COUNT(DISTINCT g.id) AS `count` FROM `" . $this->getTableName() . "` AS `g`
            INNER JOIN `" . ZLGROUPS_BOL_InviteDao::getInstance()->getTableName() . "` AS `i` ON ( `g`.`id` = `i`.`groupId` )
            WHERE `i`.`userId` = :u AND " . $addWhere;

        return $this->dbo->queryForColumn($query, array(
            'u' => (int) $userId
        ));
    }

    //获得所有乐群列表
    public function findAllLimited( $first = null, $count = null )
    {
        $example = new OW_Example();

        $example->setOrder(" id DESC ");

        if ( $first != null && $count !=null )
        {
            $example->setLimitClause($first, $count);
        }

        return $this->findListByExample($example);
    }

}