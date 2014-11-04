<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /init.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers
 * @since 1.2.6
 */
OW::getRouter()->addRoute(new OW_Route('ocstopusers.list', 'users/top/', 'OCSTOPUSERS_CTRL_List', 'index'));

OW::getRouter()->addRoute(new OW_Route('ocstopusers.rated_list', 'users/rated-me/', 'OCSTOPUSERS_CTRL_List', 'rated'));


function ocstopusers_delete_user_rates( OW_Event $event )
{
    $params = $event->getParams();
    $userId = (int) $params['userId'];

    if ( $userId > 0 )
    {
    	BOL_RateService::getInstance()->deleteEntityRates($userId, 'user_rates');
    }
}

OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, 'ocstopusers_delete_user_rates');


function ocstopusers_add_notify_action( BASE_CLASS_EventCollector $e )
{
    $e->add(array(
        'section' => 'ocstopusers',
        'action' => 'ocstopusers-rate_user',
        'sectionIcon' => 'ow_ic_star',
        'sectionLabel' => OW::getLanguage()->text('ocstopusers', 'email_notifications_section_label'),
        'description' => OW::getLanguage()->text('ocstopusers', 'email_notifications_setting_rate'),
        'selected' => true
    ));
}
OW::getEventManager()->bind('notifications.collect_actions', 'ocstopusers_add_notify_action');


function ocstopusers_on_rate_user( OW_Event $e )
{
    $params = $e->getParams();

    $rate = (int) $params['rate'];
    $ownerId = (int) $params['ownerId'];
    $userId = (int) $params['userId'];
    
    $userService = BOL_UserService::getInstance();
    
    $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));

    $params = array(
        'pluginKey' => 'ocstopusers',
        'entityType' => 'ocstopusers_rate_user',
        'entityId' => $userId . '-' . $ownerId,
        'action' => 'ocstopusers-rate_user',
        'userId' => $ownerId,
        'time' => time()
    );
    
    /*$content = '<div class="rates_cont clearfix">';
    for ( $i = 1; $i < BOL_RateService::getInstance()->getConfig(BOL_RateService::CONFIG_MAX_RATE); $i++ )
    {
        $isActive = $i <= $rate ? ' active' : '';
        $content .= '<a class="rate_item'.$isActive.'" href="javascript://">&nbsp;</a>';
    }
    $content .= '</div>';*/
    
    $data = array(
        'avatar' => $avatars[$userId],
        'string' => array(
            'key' => 'ocstopusers+email_notifications_rate_user',
            'vars' =>array(
                'userName' => $userService->getDisplayName($userId),
                'userUrl' => $userService->getUserUrl($userId),
                'rate' => $rate
            )
        ),
        //'content' => $content,
        'url' => $userService->getUserUrl($ownerId)
    );

    $event = new OW_Event('notifications.add', $params, $data);
    OW::getEventManager()->trigger($event);
}
OW::getEventManager()->bind('ocstopusers.rate_user', 'ocstopusers_on_rate_user');
