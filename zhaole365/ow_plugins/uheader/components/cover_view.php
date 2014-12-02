<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package uheader.components
 */
class UHEADER_CMP_CoverView extends OW_Component
{
    const MIN_HEIGHT = 400;

    public function __construct( $userId )
    {
        parent::__construct();

        $cover = UHEADER_BOL_Service::getInstance()->findCoverByUserId($userId);

        if ( empty($cover) )
        {
            $this->assign('error', OW::getLanguage()->text('uheader', 'cover_not_found'));

            return;
        }

        $src = UHEADER_BOL_Service::getInstance()->getCoverUrl($cover);

        $settings = $cover->getSettings();
        $height = $settings['dimensions']['height'];
        $width = $settings['dimensions']['width'];

        $top = 0;

        if ( $height < self::MIN_HEIGHT )
        {
            $top = (self::MIN_HEIGHT - $height) / 2;
        }

        $avatarsData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $this->assign('user', $avatarsData[$userId]);
        
        $this->assign('src', $src);
        $this->assign('top', $top);
        $this->assign('dimensions', $settings['dimensions']);

        $cmtParams = new BASE_CommentsParams('uheader', UHEADER_CLASS_CommentsBridge::ENTITY_TYPE);
        $cmtParams->setWrapInBox(false);
        $cmtParams->setEntityId($cover->id);
        $cmtParams->setOwnerId($userId);
        $cmtParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_TOP_FORM_WITH_PAGING);

        $photoCmts = new BASE_CMP_Comments($cmtParams);
        $this->addComponent('comments', $photoCmts);
    }
}