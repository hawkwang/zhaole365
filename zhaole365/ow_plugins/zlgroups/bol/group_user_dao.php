<?php

class ZLGROUPS_BOL_GroupUserDao extends OW_BaseDao
{

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
        return 'ZLGROUPS_BOL_GroupUser';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlgroups_group_user';
    }

    //获得指定乐群的用户列表
    public function findListByGroupId( $groupId, $first, $count )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "userId", array(
            "method" => "ZLGROUPS_BOL_GroupUserDao::findListByGroupId"
        ));
        
        $query = "SELECT u.* FROM " . $this->getTableName() . " u " . $queryParts["join"] 
                . " WHERE " . $queryParts["where"] . " AND u.groupId=:g AND u.privacy=:p LIMIT :lf, :lc";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            "g" => $groupId,
            "p" => ZLGROUPS_BOL_Service::PRIVACY_EVERYBODY,
            "lf" => $first,
            "lc" => $count
        ));
    }

    //获得所有指定乐群和隐私权的用户列表
    public function findByGroupId( $groupId, $privacy = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);

        if ( $privacy !== null )
        {
            $example->andFieldEqual('privacy', $privacy);
        }

        return $this->findListByExample($example);
    }

    //获得指定乐趣的用户数
    public function findCountByGroupId( $groupId )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "userId", array(
            "method" => "ZLGROUPS_BOL_GroupUserDao::findCountByGroupId"
        ));
        
        $query = "SELECT COUNT(DISTINCT u.id) FROM " . $this->getTableName() . " u " . $queryParts["join"] 
                . " WHERE " . $queryParts["where"] . " AND u.groupId=:g";

        return $this->dbo->queryForColumn($query, array(
            "g" => $groupId
        ));
    }

    //获得指定乐群ID列表对应的用户数列表
    public function findCountByGroupIdList( $groupIdList )
    {
        if ( empty($groupIdList) )
        {
            return array();
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "userId", array(
            "method" => "ZLGROUPS_BOL_GroupUserDao::findCountByGroupIdList"
        ));
        
        $query = 'SELECT u.groupId, COUNT(*) count FROM ' . $this->getTableName() . ' u '
                . $queryParts["join"]
                . ' WHERE ' . $queryParts["where"] . ' AND u.groupId IN (' . implode(',', $groupIdList) . ') GROUP BY u.groupId';

        $list = $this->dbo->queryForList($query, null
                , ZLGROUPS_BOL_GroupDao::LIST_CACHE_LIFETIME, array(ZLGROUPS_BOL_GroupDao::LIST_CACHE_TAG));

        $resultList = array();
        foreach ( $list as $item )
        {
            $resultList[$item['groupId']] = $item['count'];
        }

        foreach ( $groupIdList as $groupId )
        {
            $resultList[$groupId] = empty($resultList[$groupId]) ? 0 : $resultList[$groupId];
        }

        return $resultList;
    }

    //获得指定乐群ID和用户ID的GroupUser对象
    public function findGroupUser( $groupId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    //删除乐群所有用户
    public function deleteByGroupId( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);

        return $this->deleteByExample($example);
    }

    //删除指定用户的所有关注乐群纪录
    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->deleteByExample($example);
    }

    //删除指定用户对乐群的关注纪录
    public function deleteByGroupAndUserId( $groupId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('userId', $userId);

        return $this->deleteByExample($example);
    }

    //设置用户的隐私权
    public function setPrivacy( $userId, $privacy )
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET privacy=:p WHERE userId=:u';

        $this->dbo->query($query, array(
            'p' => $privacy,
            'u' => $userId
        ));
    }
}