<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Top users list controller
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers.controllers
 * @since 1.2.6
 */
class OCSTOPUSERS_CTRL_List extends OW_ActionController
{    
    /**
     * Default action
     */
    public function index()
    {
        $lang = OW::getLanguage();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        
        $perPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
        
        $service = OCSTOPUSERS_BOL_Service::getInstance();
        
        $users = $service->findList($page, $perPage);
        
        if ( $users )
        {
            $count = $service->countUsers();
            
            $list = array();
            $fields = array();
            
            foreach ( $users as $user )
            {
            	$list[] = $user['dto'];
            	$fields[$user['dto']->id] = array('score' => $user['score'], 'rates' => $user['rates']);
            }
            
            $cmp = new OCSTOPUSERS_CMP_List($list, $count, $perPage, false, $fields);
            $this->addComponent('users', $cmp);
        }
        else 
        {
        	$this->assign($users, null);
        }
                
        OW::getDocument()->setHeading($lang->text('ocstopusers', 'index_widget_title'));
        OW::getDocument()->setHeadingIconClass('ow_ic_star');
        OW::getNavigation()->activateMenuItem(BOL_NavigationService::MENU_TYPE_MAIN, 'base', 'users_main_menu_item');
    }
    
    public function rated()
    {
    	if ( !OW::getUser()->isAuthenticated() )
    	{
    		throw new AuthenticateException();
    	}
    	
        $lang = OW::getLanguage();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
        
        $service = OCSTOPUSERS_BOL_Service::getInstance();
        
        $userId = OW::getUser()->getId();
        $users = $service->findRateUserList($userId, $page, $perPage);
        
        if ( $users )
        {
            $count = $service->countRateUsers($userId);

            $list = array();
            $fields = array();
            foreach ( $users as $user )
            {
            	$list[] = $user['dto'];
                $fields[$user['dto']->id] = array('score' => $user['score'], 'timeStamp' => $user['timeStamp']);
            }
            
            $cmp = new OCSTOPUSERS_CMP_RatedList($list, $count, $perPage, false, $fields);
            $this->addComponent('users', $cmp);
        }
        else 
        {
            $this->assign($users, null);
        }
                
        OW::getDocument()->setHeading($lang->text('ocstopusers', 'rated_me'));
        OW::getDocument()->setHeadingIconClass('ow_ic_star');
        
        OW::getNavigation()->activateMenuItem(BOL_NavigationService::MENU_TYPE_MAIN, 'base', 'users_main_menu_item');
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('ocstopusers')->getCtrlViewDir() . 'list_index.html');
    }
    
    public function updateRate()
    {
        $service = BOL_RateService::getInstance();

        $entityId = (int) $_POST['entityId'];
        $entityType = trim($_POST['entityType']);
        $rate = (int) $_POST['rate'];
        $ownerId = (int) $_POST['ownerId'];
        $userId = OW::getUser()->getId();

        if ( !OW::getUser()->isAuthenticated() )
        {
            echo json_encode(array('errorMessage' => OW::getLanguage()->text('base', 'rate_cmp_auth_error_message')));
            exit();
        }

        if ( $userId === $ownerId )
        {
            echo json_encode(array('errorMessage' => OW::getLanguage()->text('base', 'rate_cmp_owner_cant_rate_error_message')));
            exit();
        }

        if ( false )
        {
            echo json_encode(array('errorMessage' => 'Auth error'));
            exit();
        }

        $rateItem = $service->findRate($entityId, $entityType, $userId);

        if ( $rateItem === null )
        {
            $rateItem = new BOL_Rate();
            $rateItem->setEntityId($entityId)->setEntityType($entityType)->setUserId($userId)->setActive(true);
        }

        $rateItem->setScore($rate)->setTimeStamp(time());

        $service->saveRate($rateItem);
        
        $event = new OW_Event('ocstopusers.rate_user', array(
            'ownerId' => $entityId,
            'userId' => $userId,
            'rate' => $rate
        ));
        OW::getEventManager()->trigger($event);

        $totalScoreCmp = new OCSTOPUSERS_CMP_TotalScore($entityId, $entityType);

        echo json_encode(array('totalScoreCmp' => $totalScoreCmp->render(), 'message' => OW::getLanguage()->text('base', 'rate_cmp_success_message')));
        exit();
    }
}

class OCSTOPUSERS_CMP_List extends BASE_CMP_Users
{
    private $fieds;
    
    public function __construct( $list, $itemCount, $usersOnPage, $showOnline, $fields )
    {
        $this->fields = $fields;

        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
    }

    public function getFields( $userIdList )
    {
        $lang = OW::getLanguage();
        
        $fields = array();
        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate', 'sex');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        $sm = new Smarty();
        
        foreach ( $questionList as $uid => $question )
        {
            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }
         
            $string = $lang->text('ocstopusers', 'rate_info', array('rates' => $this->fields[$uid]['rates'], 'score' => floatval($this->fields[$uid]['score'])));
            $fields[$uid][] = array('label' => '', 'value' => $string);
            
            $rate = BASE_CTRL_Rate::displayRate(array('avg_rate' => $this->fields[$uid]['score']), $sm);
            $fields[$uid][] = array('label' => '', 'value' => $rate);
        }

        return $fields;
    }
}


class OCSTOPUSERS_CMP_RatedList extends BASE_CMP_Users
{
    private $fieds;
    
    public function __construct( $list, $itemCount, $usersOnPage, $showOnline, $fields )
    {
        $this->fields = $fields;

        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
    }

    public function getFields( $userIdList )
    {
        $lang = OW::getLanguage();
        
        $fields = array();
        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate', 'sex');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        $sm = new Smarty();
        
        foreach ( $questionList as $uid => $question )
        {
            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }
            
            $rate = BASE_CTRL_Rate::displayRate(array('avg_rate' => $this->fields[$uid]['score']), $sm);
            $fields[$uid][] = array('label' => '', 'value' => $rate);
            
            $fields[$uid][] = array('label' => '', 'value' => UTIL_DateTime::formatDate($this->fields[$uid]['timeStamp']));
        }

        return $fields;
    }
}