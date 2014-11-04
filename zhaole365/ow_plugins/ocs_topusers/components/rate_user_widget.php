<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Rate user widget
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers.components
 * @since 1.2.6
 */
class OCSTOPUSERS_CMP_RateUserWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $userId = $params->additionalParamList['entityId'];
        
        $service = BOL_RateService::getInstance();
        
        $ownerMode = $userId == OW::getUser()->getId();
        $this->assign('ownerMode', $ownerMode);
        
        if ( $ownerMode )
        {
        	$limit = $params->customParamList['userCount'];
        	$topService = OCSTOPUSERS_BOL_Service::getInstance();
        	$ratedList = $topService->findRateUserList($userId, 1, $limit);
        	
        	if ( $ratedList )
        	{
        		$idList = array();
        		foreach ( $ratedList as $user )
        		{
        			array_push($idList, $user['dto']->id);
        		}
        		
        		$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);
        		$this->assign('avatars', $avatars);
        		$this->assign('list', $ratedList);
        		
        		$total = $topService->countRateUsers($userId);
        		
        		if ( $total > $limit )
        		{
        			$toolbar = array('label' => OW::getLanguage()->text('base', 'view_all'), 'href' => OW::getRouter()->urlForRoute('ocstopusers.rated_list'));
        			$this->assign('toolbar', array($toolbar));
        		}
        		else 
        		{
        			$this->assign('toolbar', null);
        		}
        	}
        	else 
        	{
        		$this->assign('list', null);
        	}
        }
        
        $maxRate = $service->getConfig(BOL_RateService::CONFIG_MAX_RATE);
        $this->assign('maxRate', $maxRate);
        
        $cmpId = rand(1, 100000);
        $this->assign('cmpId', $cmpId);
        
        $entityId = $userId;
        $entityType = 'user_rates';
        $ownerId = $ownerMode ? $userId : null;

        if ( OW::getUser()->isAuthenticated() )
        {
            $userRateItem = $service->findRate($entityId, $entityType, OW::getUser()->getId());

            if ( $userRateItem !== null )
            {
                $userRate = $userRateItem->getScore();
            }
            else
            {
                $userRate = null;
            }
        }
        else
        {
            $userRate = null;
        }
        
        $this->addComponent('total_score', new OCSTOPUSERS_CMP_TotalScore($entityId, $entityType));
        
        $jsParamsArray = array(
            'cmpId' => $cmpId,
            'userRate' => $userRate,
            'entityId' => $entityId,
            'entityType' => $entityType,
            'itemsCount' => $maxRate,
            'respondUrl' => OW::getRouter()->urlFor('OCSTOPUSERS_CTRL_List', 'updateRate'),
            'ownerId' => $ownerId
        );

        OW::getDocument()->addOnloadScript("var rate$cmpId = new OwRate(" . json_encode($jsParamsArray) . "); rate$cmpId.init();");
    }
    
    public static function getSettingList()
    {
        $settingList = array();

        $settingList['userCount'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('ocstopusers', 'cmp_widget_user_count'),
            'value' => 6
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
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_SHOW_TITLE => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}
