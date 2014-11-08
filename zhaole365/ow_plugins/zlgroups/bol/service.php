<?php

class ZLGROUPS_BOL_Service
{
	// 定义乐群图片大小对应的宽度
    const IMAGE_WIDTH_SMALL = 100;
    const IMAGE_WIDTH_BIG = 400;
    
    // 定义乐群图片所需指定的大小
    const IMAGE_SIZE_SMALL = 1;
    const IMAGE_SIZE_BIG = 2;
    
    //定义乐群相关widget面板的名称
    const WIDGET_PANEL_NAME = 'zlgroup';

    //定义乐群相关事件 (TBD - 1、乐子的处理；2、searchengine的处理；3、文件的处理；4、群文件的处理 等等 都需要根据事件进行处理)
    const EVENT_ON_DELETE = 'zlgroups_on_group_delete';					// 乐群删除
    const EVENT_DELETE_COMPLETE = 'zlgroups_group_delete_complete';		// 乐群删除完成
    const EVENT_CREATE = 'zlgroups_group_create_complete';				// 乐群创建完成
    const EVENT_BEFORE_CREATE = 'zlgroups_group_before_create';			// 乐群创建
    const EVENT_EDIT = 'zlgroups_group_edit_complete';					// 乐群编辑完成
    const EVENT_USER_ADDED = 'zlgroups_user_signed';					// 乐群添加用户
    const EVENT_USER_BEFORE_ADDED = 'zlgroups_before_user_signed';		// 乐群添加用户完成
    const EVENT_USER_DELETED = 'zlgroups_user_left';					// 乐群用户取消关注
    const EVENT_USER_DELETED_COMPLETE = 'zlgroups_user_left_complete';  // 乐群用户取消关注完成
    const EVENT_INVITE_ADDED = 'zlgroups.invite_user';					// 乐群用户邀请添加
    const EVENT_INVITE_DELETED = 'zlgroups.invite_removed';				// 乐群用户邀请删除
    
    //定义乐群相关论坛事件
    const EVENT_DELETE_FORUM = 'forum.delete_group';

    //定义WCV（who can view）的类型
    const WCV_ANYONE = 'anyone';
    const WCV_INVITE = 'invite';

    //定义WCI（who can invite）的类型
    const WCI_CREATOR = 'creator';
    const WCI_PARTICIPANT = 'participant';

    //定义隐私类型
    const PRIVACY_EVERYBODY = 'everybody';
    const PRIVACY_ACTION_VIEW_MY_GROUPS = 'view_my_groups';

    //定义显示列表类型
    const LIST_MOST_POPULAR = 'most_popular';
    const LIST_LATEST = 'latest';
    const LIST_ALL = 'all';

    //定义
    const ENTITY_TYPE_WAL = 'zlgroups_wal';
    const ENTITY_TYPE_GROUP = 'zlgroups';
    const FEED_ENTITY_TYPE = 'zlgroup';

    private static $classInstance;

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $groupInviteDao;

    private $groupDao;

    private $groupUserDao;
    
    private $groupLocationDao;

    protected function __construct()
    {
        $this->groupDao = ZLGROUPS_BOL_GroupDao::getInstance();
        $this->groupUserDao = ZLGROUPS_BOL_GroupUserDao::getInstance();
        $this->groupLocationDao = ZLGROUPS_BOL_GroupLocationDao::getInstance();
        $this->groupInviteDao = ZLGROUPS_BOL_InviteDao::getInstance();
    }

    //保存乐群信息
    public function saveGroup( ZLGROUPS_BOL_Group $groupDto )
    {
        $this->groupDao->save($groupDto);
    }

    // 删除指定乐群
    public function deleteGroup( $groupId )
    {
        $event = new OW_Event(self::EVENT_ON_DELETE, array('groupId' => $groupId));
        OW::getEventManager()->trigger($event);

        //删除乐群信息, TBU － 是否应该放在后面 （考虑到没有使用主外键约束，需要自己完全处理关联关系）
        $this->groupDao->deleteById($groupId);

        //$this->groupUserDao->deleteByGroupId($groupId);
        $groupUsers = $this->groupUserDao->findByGroupId($groupId);
        foreach ( $groupUsers as $groupUser )
        {
            $this->deleteUser($groupId, $groupUser->userId);
        }

        $this->groupInviteDao->deleteByGroupId($groupId);

        // added by hawk, to delete group location
        $this->groupLocationDao->deleteByGroupId($groupId);
        
        $is_forum_connected = OW::getConfig()->getValue('zlgroups', 'is_forum_connected');
        // Delete forum group
        if ( $is_forum_connected )
        {
            $event = new OW_Event(self::EVENT_DELETE_FORUM, array('entityId' => $groupId, 'entity' => 'zlgroups'));
            OW::getEventManager()->trigger($event);
        }

        $event = new OW_Event(self::EVENT_DELETE_COMPLETE, array('groupId' => $groupId));
        OW::getEventManager()->trigger($event);
    }

    // 删除用户
    public function deleteUser( $groupId, $userId )
    {
        $groupUserDto = $this->groupUserDao->findGroupUser($groupId, $userId);
        
        $event = new OW_Event(self::EVENT_USER_DELETED, array(
            'groupId' => $groupId,
            'userId' => $userId,
            'groupUserId' => $groupUserDto->id
        ));

        OW::getEventManager()->trigger($event);
        
        $this->groupUserDao->delete($groupUserDto);
    }

    // 相应用户退出注册的情况
    public function onUserUnregister( $userId, $withContent )
    {
    	// 是否标识要删除所有相关内容
        if ( $withContent )
        {
            $groups = $this->groupDao->findAllUserGroups($userId);

            foreach ( $groups as $groups )
            {
                ZLGROUPS_BOL_Service::getInstance()->deleteGroup($groups->id);
            }
        }

        $this->groupInviteDao->deleteByUserId($userId);
        $this->groupUserDao->deleteByUserId($userId);
    }

    // 获得指定用户的乐群列表
    public function findUserGroupList( $userId, $first = null, $count = null )
    {
        return $this->groupDao->findByUserId($userId, $first, $count);
    }

    // 获得指定用户的乐群数
    public function findUserGroupListCount( $userId )
    {
        return $this->groupDao->findCountByUserId($userId);
    }

    // 获得指定乐群
    public function findGroupById( $groupId )
    {
        return $this->groupDao->findById((int) $groupId);
    }

    // 根据指定类型获得乐群列表
    public function findGroupList( $listType, $first=null, $count=null )
    {
        switch ( $listType )
        {
            case self::LIST_MOST_POPULAR:
                return $this->groupDao->findMostPupularList($first, $count);

            case self::LIST_LATEST:
                return $this->groupDao->findOrderedList($first, $count);

            case self::LIST_ALL:
                return $this->groupDao->findAllLimited( $first, $count );
        }

        throw new InvalidArgumentException('Undefined list type');
    }

    // 获得指定类型的乐群数
    public function findGroupListCount( $listType )
    {
        switch ( $listType )
        {
            case self::LIST_MOST_POPULAR:
            case self::LIST_LATEST:
                return $this->groupDao->findAllCount();
        }

        throw new InvalidArgumentException('Undefined list type');
    }

    // 获得指定用户被邀请的乐群
    public function findInvitedGroups( $userId, $first=null, $count=null )
    {
        return $this->groupDao->findUserInvitedGroups($userId, $first, $count);
    }

    // 获得指定用户被邀请的乐群数
    public function findInvitedGroupsCount( $userId )
    {
        return $this->groupDao->findUserInvitedGroupsCount($userId);
    }

    // 获得指定用户关注的乐群
    public function findMyGroups( $userId, $first=null, $count=null )
    {
        return $this->groupDao->findMyGroups($userId, $first, $count);
    }

    // 获得指定用户关注的乐群数
    public function findMyGroupsCount( $userId )
    {
        return $this->groupDao->findMyGroupsCount($userId);
    }

    // 获得所有乐群数
    public function findAllGroupCount()
    {
        return $this->groupDao->findAll();
    }

    // 获得指定标题的乐群
    public function findByTitle( $title )
    {
        return $this->groupDao->findByTitle($title);
    }

    // 获得指定数目的乐群
    public function findLimitedList( $count )
    {
        return $this->groupDao->findLimitedList($count);
    }

    // 获得指定乐群的用户数
    public function findUserListCount( $groupId )
    {
        return $this->groupUserDao->findCountByGroupId($groupId);
    }

    // 获得指定乐群ID列表对应的用户数列表
    public function findUserCountForList( $groupIdList )
    {
        return $this->groupUserDao->findCountByGroupIdList($groupIdList);
    }

    // 获得指定乐群的用户ID列表
    public function findUserList( $groupId, $first, $count )
    {
        $groupUserList = $this->groupUserDao->findListByGroupId($groupId, $first, $count);
        $idList = array();
        foreach ( $groupUserList as $groupUser )
        {
            $idList[] = $groupUser->userId;
        }

        return BOL_UserService::getInstance()->findUserListByIdList($idList);
    }

    // 获得指定乐群和隐私权的用户ID列表
    public function findGroupUserIdList( $groupId, $privacy = null )
    {
        $groupUserList = $this->groupUserDao->findByGroupId($groupId, $privacy);
        $idList = array();
        foreach ( $groupUserList as $groupUser )
        {
            $idList[] = $groupUser->userId;
        }

        return $idList;
    }

    // 为乐群添加用户
    public function addUser( $groupId, $userId )
    {
        $dto = $this->findUser($groupId, $userId);
        if ( $dto !== null )
        {
            return true;
        }

        $dto = new ZLGROUPS_BOL_GroupUser();
        $dto->timeStamp = time();

        $dto->groupId = $groupId;
        $dto->userId = $userId;

        $data = array();
        foreach ( $dto as $key => $value )
        {
            $data[$key] = $value;
        }

        $event = new OW_Event(self::EVENT_USER_BEFORE_ADDED, array(
            'groupId' => $groupId,
            'userId' => $userId
        ), $data);

        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        foreach ( $data as $k => $v )
        {
            $dto->$k = $v;
        }

        $this->groupUserDao->save($dto);

        //用户加入乐群关注后，需要删除邀请信息。 TBF － 对于不需要邀请的乐群，用户可以自己加入关注，不需要邀请
        $this->deleteInvite($groupId, $userId);

        $event = new OW_Event(self::EVENT_USER_ADDED, array(
                'groupId' => $groupId,
                'userId' => $userId,
                'groupUserId' => $dto->id
            ));
        OW::getEventManager()->trigger($event);
    }

    // 获得指定乐群ID和用户ID的关注信息
    public function findUser( $groupId, $userId )
    {
        return $this->groupUserDao->findGroupUser($groupId, $userId);
    }

    // 获得乐群的图片文件名称
    public function getGroupImageFileName( ZLGROUPS_BOL_Group $group, $size = self::IMAGE_SIZE_SMALL )
    {
        if ( empty($group->imageHash) )
        {
            return null;
        }

        $suffix = $size == self::IMAGE_SIZE_BIG ? "big-" : "";
        
        return 'group-' . $group->id . '-'  . $suffix . $group->imageHash . '.jpg';
    }

    // 获得乐群图片地址
    public function getGroupImageUrl( ZLGROUPS_BOL_Group $group, $size = self::IMAGE_SIZE_SMALL )
    {
        $path = $this->getGroupImagePath($group, $size);
        $noPictureUrl = OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'no-picture.png';
        
        return empty($path) ? $noPictureUrl : OW::getStorage()->getFileUrl($path);
    }

    // 获得乐群图片路径
    public function getGroupImagePath( ZLGROUPS_BOL_Group $group, $size = self::IMAGE_SIZE_SMALL )
    {
        $fileName = $this->getGroupImageFileName($group, $size);

        return empty($fileName) ? null : OW::getPluginManager()->getPlugin('zlgroups')->getUserFilesDir() . $fileName;
    }

    // 获得乐群URL
    public function getGroupUrl( ZLGROUPS_BOL_Group $group )
    {
        return OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $group->id));
    }

    // 判断当前用户是否具有编辑指定乐群的权利
    public function isCurrentUserCanEdit( ZLGROUPS_BOL_Group $group )
    {
        return $group->userId == OW::getUser()->getId() || OW::getUser()->isAuthorized('zlgroups');
    }

    // 判断当前用户是否具有创建乐群的权利
    public function isCurrentUserCanCreate()
    {
        return OW::getUser()->isAuthorized('zlgroups', 'create');
    }

    // 判断指定用户是否具有查看乐群的权利
    public function isCurrentUserCanView( $ownerId )
    {
        return $ownerId == OW::getUser()->getId() || OW::getUser()->isAuthorized('zlgroups', 'view');
    }

    // 判断当前用户是否具有查看乐群的权利
    public function isCurrentUserCanViewList()
    {
        return OW::getUser()->isAuthorized('zlgroups', 'view');
    }

    // 判断当前用户是否具有邀请的权利
    public function isCurrentUserInvite( $groupId )
    {
        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            return false;
        }

        $group = $this->findGroupById($groupId);

        if ( $group->whoCanInvite == self::WCI_CREATOR )
        {
            return $group->userId == $userId;
        }

        if ( $group->whoCanInvite == self::WCI_PARTICIPANT  )
        {
            return $this->findUser($groupId, $userId) !== null;
        }

        return false;
    }

    // 邀请用户
    public function inviteUser( $groupId, $userId, $inviterId )
    {
        $invite = $this->groupInviteDao->findInvite($groupId, $userId, $inviterId);

        if ( $invite !== null  )
        {
            return;
        }

        $invite = new ZLGROUPS_BOL_Invite();
        $invite->userId = $userId;
        $invite->groupId = $groupId;
        $invite->inviterId = $inviterId;
        $invite->timeStamp = time();
        $invite->viewed = false;

        $this->groupInviteDao->save($invite);

        $event = new OW_Event(self::EVENT_INVITE_ADDED, array(
            'groupId' => $groupId,
            'userId' => $userId,
            'inviterId' => $inviterId,
            'inviteId' => $invite->id
        ));

        OW::getEventManager()->trigger($event);
    }

    // 删除邀请
    public function deleteInvite( $groupId, $userId )
    {
        $this->groupInviteDao->deleteByUserIdAndGroupId($groupId, $userId);

        $event = new OW_Event(self::EVENT_INVITE_DELETED, array(
            'groupId' => $groupId,
            'userId' => $userId
        ));

        OW::getEventManager()->trigger($event);
    }

    // 获得邀请信息
    public function findInvite( $groupId, $userId, $inviterId = null )
    {
        return $this->groupInviteDao->findInvite($groupId, $userId, $inviterId);
    }

    // 将指定邀请标识为已浏览的
    public function markInviteAsViewed( $groupId, $userId, $inviterId = null )
    {
        $invite = $this->groupInviteDao->findInvite($groupId, $userId, $inviterId);

        if ( empty($invite) )
        {
            return false;
        }

        $invite->viewed = true;
        $this->groupInviteDao->save($invite);

        return true;
    }

    // 将指定用户的所有邀请都标识为已浏览的
    public function markAllInvitesAsViewed( $userId )
    {
        $list = $this->groupInviteDao->findInviteListByUserId($userId);

        foreach ( $list as $item )
        {
            $item->viewed = true;

            $this->groupInviteDao->save($item);
        }
    }

    // 获得指定乐群的所有邀请信息
    public function findAllInviteList( $groupId )
    {
        return $this->groupInviteDao->findInviteList($groupId);
    }

    // 获得指定乐群和邀请人邀请的所有用户列表
    public function findInvitedUserIdList( $groupId, $inviterId )
    {
        $list = $this->groupInviteDao->findListByGroupIdAndInviterId($groupId, $inviterId);
        $out = array();
        foreach ( $list as $item )
        {
            $out[] = $item->userId;
        }

        return $out;
    }

    // 获取用户被邀请关注的类群数
    public function findUserInvitedGroupsCount( $userId, $newOnly = false )
    {
        return $this->groupDao->findUserInvitedGroupsCount($userId, $newOnly);
    }

    // 获取所有乐群的关注用户列表
    public function findAllGroupsUserList()
    {
        $users = $this->groupUserDao->findAll();

        $out = array();
        foreach ( $users as $user )
        {
            /* @var $user GROUPS_BOL_GroupUser */
            $out[$user->groupId][] = $user->userId;
        }

        return $out;
    }

    // 设置乐群隐私权
    public function setGroupsPrivacy( $ownerId, $privacy )
    {
        $this->groupDao->setPrivacy($ownerId, $privacy);
    }

    // 设置乐群关注用户的隐私权
    public function setGroupUserPrivacy( $userId, $privacy )
    {
        $this->groupUserDao->setPrivacy($userId, $privacy);
    }

    // 清除乐群列表缓存
    public function clearListingCache()
    {
        OW::getCacheManager()->clean(array( ZLGROUPS_BOL_GroupDao::LIST_CACHE_TAG ));
    }
}