<?php

class ZLGROUPS_BOL_InviteDao extends OW_BaseDao
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

    protected function __construct()
    {
        parent::__construct();
    }

    public function getDtoClassName()
    {
        return 'ZLGROUPS_BOL_Invite';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlgroups_invite';
    }


    //获得被指定乐群邀请的用户信息
    public function findInvite( $groupId, $userId, $inviterId = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);
        $example->andFieldEqual('userId', (int) $userId);

        if ( $inviterId !== null )
        {
            $example->andFieldEqual('inviterId', (int) $inviterId);
        }

        return $this->findObjectByExample($example);
    }

    //获得被指定乐群邀请的所有用户
    public function findInviteList( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);

        return $this->findListByExample($example);
    }

    //获得指定用户被邀请的所有信息
    public function findInviteListByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', (int) $userId);

        return $this->findListByExample($example);
    }

    //根据乐群和邀请人获得相应邀请信息
    public function findListByGroupIdAndInviterId( $groupId, $inviterId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);
        $example->andFieldEqual('inviterId', (int) $inviterId);

        return $this->findListByExample($example);
    }

    //删除指定用户被指定乐群邀请信息
    public function deleteByUserIdAndGroupId( $groupId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);
        $example->andFieldEqual('userId', (int) $userId);

        $this->deleteByExample($example);
    }

    //删除指定用户被邀请信息
    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', (int) $userId);

        $this->deleteByExample($example);
    }


    //删除指定乐群的邀请信息
    public function deleteByGroupId( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);

        $this->deleteByExample($example);
    }


    //获得指定乐群的所有邀请信息
    public function findListByGroupId( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);

        return $this->findListByExample($example);
    }
}
