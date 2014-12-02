<?php

class ZLGHEADER_CMP_CoverView extends OW_Component
{
    const MIN_HEIGHT = 400;

    public function __construct( $groupId )
    {
        parent::__construct();

        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverByGroupId($groupId);

        if ( empty($cover) )
        {
            $this->assign('error', OW::getLanguage()->text('zlgheader', 'cover_not_found'));

            return;
        }

        $group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($cover->groupId);
        
        $src = ZLGHEADER_BOL_Service::getInstance()->getCoverUrl($cover);

        $settings = $cover->getSettings();
        $height = $settings['dimensions']['height'];
        $width = $settings['dimensions']['width'];

        $top = 0;

        if ( $height < self::MIN_HEIGHT )
        {
            $top = (self::MIN_HEIGHT - $height) / 2;
        }
        
        $tplGroup = array(
            "thumbnail" => ZLGROUPS_BOL_Service::getInstance()->getGroupImageUrl($group),
            "url" => ZLGROUPS_BOL_Service::getInstance()->getGroupUrl($group),
            "title" => UTIL_String::truncate($group->title, 100, "..."),
            "time" => UTIL_DateTime::formatDate($group->timeStamp)
        );
        
        
        $privacyParams = array('action' => ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS, 'ownerId' => $group->userId, 'viewerId' => OW::getUser()->getId());
        $event = new OW_Event('privacy_check_permission', $privacyParams);
        
        $showAdmin = false;
        
        try {
            OW::getEventManager()->trigger($event);
            $showAdmin = true;
        }
        catch ( RedirectException $e )
        {
            $showAdmin = false;
        }
        
        if ( $showAdmin )
        {
            $tplGroup["admin"] = array(
                "name" => BOL_UserService::getInstance()->getDisplayName($group->userId),
                "url" => BOL_UserService::getInstance()->getUserUrl($group->userId)
            );
        }
        else 
        {
            $tplGroup["admin"] = null;
        }
        
        $this->assign("group", $tplGroup);
        
        $this->assign('src', $src);
        $this->assign('top', $top);
        $this->assign('dimensions', $settings['dimensions']);

        $userId = OW::getUser()->getId();
        
        $cmtParams = new BASE_CommentsParams('zlgheader', ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE);
        $cmtParams->setWrapInBox(false);
        $cmtParams->setEntityId($cover->id);
        $cmtParams->setAddComment(ZLGHEADER_BOL_Service::getInstance()->isUserCanInteract($userId, $group->id));
        $cmtParams->setOwnerId($group->userId);
        $cmtParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_TOP_FORM_WITH_PAGING);

        $photoCmts = new BASE_CMP_Comments($cmtParams);
        $this->addComponent('comments', $photoCmts);
    }
}