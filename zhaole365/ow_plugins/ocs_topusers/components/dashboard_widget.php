<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Rate user dashboard widget
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers.components
 * @since 1.2.6
 */
class OCSTOPUSERS_CMP_DashboardWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        
        $lang = OW::getLanguage();
        $service = OCSTOPUSERS_BOL_Service::getInstance();
        
        $limit = $params->customParamList['userCount'];
        
        $list = $service->findList(1, $limit);
        if ( $list )
        {
	        $this->assign('list', $list);
	        
	        $total = $service->countUsers();
	        $this->assign('total', $total);
	        
	        $idList = array();
	        $scores = array(); 
	        $rates = array();
	        
	        foreach ( $list as $user )
	        {
	        	array_push($idList, $user['dto']->id);
	        	$scores[$user['dto']->id] = $user['score'];
	        	$rates[$user['dto']->id] = $user['rates'];
	        }
	        
	        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);
	        foreach ( $avatars as $userId => &$av )
	        {
	        	$av['title'] .= ' - ' . $lang->text('ocstopusers', 'rate_info', array('rates' => $rates[$userId], 'score' => floatval($scores[$userId])));
	        }
	        
	        $this->assign('avatars', $avatars);
	        $this->assign('scores', $scores);
	        
	        if ( $total > $limit )
	        {
	        	$toolbar = array(array('href' => OW::getRouter()->urlForRoute('ocstopusers.list'), 'label' => OW::getLanguage()->text('base', 'view_all')));
	            $this->setSettingValue(self::SETTING_TOOLBAR, $toolbar);
	        }
        }
        else
        {
        	$this->assign('list', null);
        }
    }

    public static function getSettingList()
    {
        $settingList = array();

        $settingList['userCount'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('ocstopusers', 'cmp_widget_user_count'),
            'value' => 12
        );

        return $settingList;
    }
    
    public static function validateSettingList( $settingList )
    {
        $validationMessage = OW::getLanguage()->text('ocstopusers', 'cmp_widget_user_count_msg');

        if ( !preg_match('/^\d+$/', $settingList['userCount']) || (int) $settingList['userCount'] > 100 )
        {
            throw new WidgetSettingValidateException($validationMessage, 'userCount');
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('ocstopusers', 'index_widget_title'),
            self::SETTING_ICON => 'ow_ic_star',
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}