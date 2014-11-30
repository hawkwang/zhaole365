<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.components
 */
class HINT_CMP_UserHint extends OW_Component
{
    const COVER_WIDTH = 350;
    const COVER_HEIGHT = 350;
    const CORNER_OFFSET = 7;

    protected $userId;
    protected $uniqId;

    protected $cover;
    
    protected $hasButtons = false;

    public function __construct($userId)
    {
        parent::__construct();

        $this->userId = $userId;
        $this->uniqId = uniqid("hint-");

        $this->cover = $this->getCover();
    }


    protected function scale( $x, $y, $toX )
    {
        return $y * $toX / $x;
    }

    public function getCover()
    {
        $bridge = HINT_CLASS_UheaderBridge::getInstance();
        
        if ( !$bridge->isActive() || !$bridge->isEnabled() )
        {
            return null;
        }
        
        $cover = $bridge->getCoverForUser($this->userId);

        if ( $cover === null )
        {
            return null;
        }

        $settings = $cover["data"];
        $coverUrl = $cover["src"];

        $canvasHeight = $settings['canvas']['height'];
        $canvasWidth = $settings['canvas']['width'];
        $imageHeight = $settings['dimensions']['height'];
        $imageWidth = $settings['dimensions']['width'];

        $itemCanvasHeight = $canvasHeight * self::COVER_WIDTH / $canvasWidth;

        $tmp = ( $canvasWidth * $imageHeight ) / $imageWidth;
        $css = $settings['css'];
        
        if ( $css["width"] == "100%" )
        {
            $css["width"] = (self::COVER_WIDTH + self::CORNER_OFFSET) . "px";
        }
        
        $topOffset = 0;
        
        if ( $tmp >= $canvasHeight )
        {
            $itemHeight = $this->scale($imageWidth, $imageHeight, self::COVER_WIDTH);
            $coverHeight = $this->scale($settings['dimensions']['width'], $settings['dimensions']['height'], $canvasWidth);
            $k = $coverHeight / $itemHeight;

            $topOffset = ($settings['position']['top'] / $k );
        }
        else
        {
            $itemWidth = $this->scale($imageHeight, $imageWidth, $itemCanvasHeight);
            $coverWidth = $this->scale($imageHeight, $imageWidth, $canvasHeight);

            $k = $coverWidth / $itemWidth;
            
            $css['left'] = ($settings['position']['left'] / $k) . 'px';
        }
        
        if ( $css["height"] == "100%" )
        {
            $css["height"] = $itemCanvasHeight . "px";
        }
        
        if ( abs($topOffset) <= self::CORNER_OFFSET )
        {
            $itemCanvasHeight = $itemCanvasHeight - ( self::CORNER_OFFSET - $topOffset );
            $topOffset = -self::CORNER_OFFSET;
        }
        
        if ( $topOffset )
        {
            $css['top'] = $topOffset . 'px';
        }

        $cssStr = '';
        foreach ( $css as $k => $v )
        {
            $cssStr .= $k . ': ' . $v  . '; ';
        }

        return array(
            'url' => $coverUrl,
            'height' => $itemCanvasHeight,
            'imageCss' => $cssStr
        );
    }
    
    protected function getUserInfo()
    {
        $user = array();

        $user['id'] = $this->userId;

        $onlineUser = BOL_UserService::getInstance()->findOnlineUserById($this->userId);
        $user['isOnline'] = $onlineUser !== null;

        $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($this->userId, 2);
        $user['avatar'] =  $avatar ? $avatar : BOL_AvatarService::getInstance()->getDefaultAvatarUrl(2);

        $roles = BOL_AuthorizationService::getInstance()->getRoleListOfUsers(array($this->userId));

        $user['role'] = !empty($roles[$this->userId]) ? $roles[$this->userId] : null;

        $user['displayName'] = BOL_UserService::getInstance()->getDisplayName($this->userId);
        $user['url'] = BOL_UserService::getInstance()->getUserUrl($this->userId);
        
        $user["fgift"] = OW::getEventManager()->call("fgift.get_user_gift", array(
            "userId" => $this->userId
        ));

        return $user;
    }

    public function renderTopCover()
    {
        if ( !$this->cover )
        {
            return '<div class="uhint-top-corner"></div>';
        }

        return '<div class="uhint-top-corner-cover uhint-corner-cover" style="height: ' . $this->cover['height'] . 'px;"><img class="uhint-corner-cover-img" src="' . $this->cover['url'] . '" style="' . $this->cover['imageCss'] . '" /></div>';
    }
    
    public function renderRightCover()
    {
        if ( !$this->cover )
        {
            return '<div class="uhint-right-corner"></div>';
        }

        return '<div class="uhint-right-corner-cover uhint-corner-cover" style="height: ' . $this->cover['height'] . 'px;"><img class="uhint-corner-cover-img" src="' . $this->cover['url'] . '" style="' . $this->cover['imageCss'] . '" /></div>';
    }
    
    public function renderBottomCover()
    {
        if ( $this->hasButtons )
        {
            return '<div class="uhint-bottom-corner"></div>';
        }
        
        return '<div class="uhint-bottom-corner ow_bg_color"></div>';
    }

    public function getButtonList()
    {
        $defaults = array(
            "label" => "---",
            "attrs" => array(
                "href" => "javascript://"
            )
        );

        $btns = HINT_BOL_Service::getInstance()->getButtonList(HINT_BOL_Service::ENTITY_TYPE_USER, $this->userId);
        $out = array();
        foreach ( $btns as $btn )
        {
            $btn = array_merge($defaults, $btn);
            $attrs = array();
            foreach ( array_merge($defaults["attrs"], $btn["attrs"]) as $k => $v )
            {
                $attrs[] = $k . '="' . $v . '"';
            }

            $out[] = array_merge($btn, array(
                "attrs" => implode(" ", $attrs)
            ));
        }

        $this->hasButtons = count($out) > 0;
        
        return $out;
    }
    
    public function getInfo()
    {
        return array(
            HINT_BOL_Service::INFO_LINE0 => HINT_BOL_Service::getInstance()->getInfoLine(HINT_BOL_Service::ENTITY_TYPE_USER, $this->userId, HINT_BOL_Service::INFO_LINE0),
            HINT_BOL_Service::INFO_LINE1 => HINT_BOL_Service::getInstance()->getInfoLine(HINT_BOL_Service::ENTITY_TYPE_USER, $this->userId, HINT_BOL_Service::INFO_LINE1),
            HINT_BOL_Service::INFO_LINE2 => HINT_BOL_Service::getInstance()->getInfoLine(HINT_BOL_Service::ENTITY_TYPE_USER, $this->userId, HINT_BOL_Service::INFO_LINE2)
        );
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new OW_Event(HINT_BOL_Service::EVENT_HINT_RENDER, array(
            "entityType" => HINT_BOL_Service::ENTITY_TYPE_USER,
            "entityId" => $this->userId
        ));
        OW::getEventManager()->trigger($event);

        OW::getEventManager()->call("uavatars.init_for_node", array(
            "userId" => $this->userId,
            "node" => "#" . $this->uniqId . " .uhint-avatar-image"
        ));

        $this->assign('user', $this->getUserInfo());
        $this->assign('cover', $this->cover);
        $this->assign("buttons", $this->getButtonList());
        $this->assign("info", $this->getInfo());

        $this->assign('uniqId', $this->uniqId);
    }
}
