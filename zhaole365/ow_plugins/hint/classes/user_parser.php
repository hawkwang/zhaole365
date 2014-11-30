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
 * @package hint.classes
 */
class HINT_CLASS_UserParser extends HINT_CLASS_Parser
{
    const USER_ROUTE_NAME = 'base_user_profile';

    private $parseMask;

    public function __construct()
    {
        $routeMask = OW::getRouter()->urlForRoute(self::USER_ROUTE_NAME, array(
            'username' => '--PLACEHOLDER--'
        ));

        $this->parseMask = "^" . str_replace('--PLACEHOLDER--', '([\w]{1,32})$', $routeMask);
        
        parent::__construct($this->parseMask, array(
            ".index-BASE_CMP_MyAvatarWidget a",
            ".ow_menu_wrap a",
            ".ow_console_dropdown_hover a",
            ".ow_footer_menu a"
        ));
     }

    public function parse($url)
    {
        $match = array();
        preg_match('~' . $this->parseMask . '~', $url, $match);

        $userName = $match[1];
        $user = BOL_UserService::getInstance()->findByUsername($userName);

        if ( $user === null )
        {
            return null;
        }

        return array(
            'userId' => $user->id
        );
    }

    public function renderHint( array $params )
    {
        $hint = new HINT_CMP_UserHint($params['userId']);

        return array(
            'body' => $hint->render(),
            'topCorner' => $hint->renderTopCover(),
            'rightCorner' => $hint->renderRightCover(),
            'bottomCorner' => $hint->renderBottomCover()
        );
    }
}

