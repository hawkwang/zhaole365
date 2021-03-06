<?php

class ZLEVENT_CTRL_Base extends OW_ActionController
{
    /**
     * @var ZLEVENT_BOL_EventService
     */
    private $eventService;

    public function __construct()
    {
        parent::__construct();
        $this->eventService = ZLEVENT_BOL_EventService::getInstance();
    }
    
    // 删除所有乐群活动
    public function removeAll()
    {
    	if ( !OW::getUser()->isAuthenticated() )
    	{
    		throw new AuthenticateException();
    	}
    	
    	if(!isset($_GET['groupId']))
    		throw new Redirect404Exception();
    	
    	$groupId = (int) $_GET['groupId'];
    	
    	$groupDto = ZLGROUPS_BOL_Service::getInstance()->findGroupById($groupId);
    	
    	$isOwner = OW::getUser()->getId() == $groupDto->userId;
    	$isAdmin = OW::getUser()->isAdmin();
    	if(($isOwner==false)&&($isAdmin==false))
    		throw new Redirect404Exception();
    		
    	if ( $groupDto === null )
    	{
    		throw new Redirect404Exception();
    	}
    	
    	switch ( $_GET['command'] )
    	{
    		case 'removeall_group_events':
    			$events = ZLEVENT_BOL_EventService::getInstance()->findEventsByGroupId($groupId);
    			$total = count($events);
    			$current = 0;
    			foreach($events as $event)
    			{
    				$this->eventService->deleteEvent($event->getId());
    				$current++;
    				//OW::getFeedback()->info('删除乐群活动 － ' . $event->title . ' - 完成 ' . $current . '/' . $total);
    			}
    			OW::getFeedback()->info('成功删除乐群活动 － 共计：' . $total);
    			break;
    
    	}
    	 
    	$this->redirect(OW_URL_HOME . $_GET['backUri']);
    }

    public function ajaxLatestResponder()
    {
    	if ( OW::getRequest()->isAjax() )
    	{
    		if (isset($_REQUEST['parameters'])) {
    
    			$json = $_REQUEST['parameters'];
    
    			// get all these parameters
    			// parse json string to array
    			$parameters = json_decode($json, true);
    			$groupId = (int)$parameters['groupId'];
    			$offset = (int)$parameters['offset'];
    			$limit = (int)$parameters['limit'];
    			$total = (int)$parameters['total'];
    
    			// get events based on parameters
    			$events = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsByGroupId($groupId, $offset, $limit);
    			$eventinfos=array();
    			$userService = BOL_UserService::getInstance();
    			foreach ($events as $event)
    			{
    				$eventinfo = array();
    				$eventinfo['title'] = $event->title;
    				$eventinfo['description'] = $event->description;
    				$eventinfo['url'] = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->id));
    				$eventinfo['logo'] = ZLEVENT_BOL_EventService::getInstance()->getEventImageWithDefaultUrl($event);
    				$eventinfo['starttime'] = UTIL_DateTime::formatSimpleDate($event->getStartTimeStamp(),$event->getStartTimeDisable());
    				$user = $userService->findUserById($event->getUserId());
    				$eventinfo['username'] = $userService->getDisplayName($event->getUserId());
    				$eventinfo['userurl'] = $userService->getUserUrlForUsername($user->getUsername());
    				
    				$eventinfos[] = $eventinfo;
    			}
    
    			$count = 0;
    			if ($events)
    				$count = count($events);
    
    			$hasmore = true;
    			if($offset + $count >= $total)
    				$hasmore = false;
    
    			$json_result = array(
    					'events' => $eventinfos,
    					'hasmore' => $hasmore
    			);
    
    			echo json_encode($json_result);
    				
    		}
    
    		exit();
    	}
    	else
    	{
    		throw new Redirect404Exception();
    	}
    
    	if ( !OW_DEBUG_MODE )
    	{
    		ob_end_clean();
    	}
    
    	exit();
    }
    
    public function ajaxHistoryResponder()
    {
    	if ( OW::getRequest()->isAjax() )
    	{
    		if (isset($_REQUEST['parameters'])) {
    
    			$json = $_REQUEST['parameters'];
    
    			// get all these parameters
    			// parse json string to array
    			$parameters = json_decode($json, true);
    			$groupId = (int)$parameters['groupId'];
    			$offset = (int)$parameters['offset'];
    			$limit = (int)$parameters['limit'];
    			$total = (int)$parameters['total'];
    
    			// get events based on parameters
    			$events = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsByGroupId($groupId, $offset, $limit, true);
    			$eventinfos=array();
    			$userService = BOL_UserService::getInstance();
    			foreach ($events as $event)
    			{
    				$eventinfo = array();
    				$eventinfo['title'] = $event->title;
    				$eventinfo['description'] = $event->description;
    				$eventinfo['url'] = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->id));
    				$eventinfo['logo'] = ZLEVENT_BOL_EventService::getInstance()->getEventImageWithDefaultUrl($event);
    				$eventinfo['starttime'] = UTIL_DateTime::formatSimpleDate($event->getStartTimeStamp(),$event->getStartTimeDisable());
    				$user = $userService->findUserById($event->getUserId());
    				$eventinfo['username'] = $userService->getDisplayName($event->getUserId());
    				$eventinfo['userurl'] = $userService->getUserUrlForUsername($user->getUsername());
    
    				$eventinfos[] = $eventinfo;
    			}
    
    			$count = 0;
    			if ($events)
    				$count = count($events);
    
    			$hasmore = true;
    			if($offset + $count >= $total)
    				$hasmore = false;
    
    			$json_result = array(
    					'events' => $eventinfos,
    					'hasmore' => $hasmore
    			);
    
    			echo json_encode($json_result);
    
    		}
    
    		exit();
    	}
    	else
    	{
    		throw new Redirect404Exception();
    	}
    
    	if ( !OW_DEBUG_MODE )
    	{
    		ob_end_clean();
    	}
    
    	exit();
    }
    
    // my
    public function ajaxMyResponder()
    {
    	if ( OW::getRequest()->isAjax() )
    	{
    		if (isset($_REQUEST['parameters'])) {
    
    			$json = $_REQUEST['parameters'];
    
    			// get all these parameters
    			// parse json string to array
    			$parameters = json_decode($json, true);
    			$groupId = (int)$parameters['groupId'];
    			$offset = (int)$parameters['offset'];
    			$limit = (int)$parameters['limit'];
    			$total = (int)$parameters['total'];
    			$userId = OW::getUser()->getId();
    
    			// get events based on parameters
    			$events = ZLEVENT_BOL_EventService::getInstance()->findUserParticipatedGroupEvents($groupId, $userId, $offset, $limit);
    			$eventinfos=array();
    			$userService = BOL_UserService::getInstance();
    			foreach ($events as $event)
    			{
    				$eventinfo = array();
    				$eventinfo['title'] = $event->title;
    				$eventinfo['description'] = $event->description;
    				$eventinfo['url'] = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->id));
    				$eventinfo['logo'] = ZLEVENT_BOL_EventService::getInstance()->getEventImageWithDefaultUrl($event);
    				$eventinfo['starttime'] = UTIL_DateTime::formatSimpleDate($event->getStartTimeStamp(),$event->getStartTimeDisable());
    				$user = $userService->findUserById($event->getUserId());
    				$eventinfo['username'] = $userService->getDisplayName($event->getUserId());
    				$eventinfo['userurl'] = $userService->getUserUrlForUsername($user->getUsername());
    
    				$eventinfos[] = $eventinfo;
    			}
    
    			$count = 0;
    			if ($events)
    				$count = count($events);
    
    			$hasmore = true;
    			if($offset + $count >= $total)
    				$hasmore = false;
    
    			$json_result = array(
    					'events' => $eventinfos,
    					'hasmore' => $hasmore
    			);
    
    			echo json_encode($json_result);
    
    		}
    
    		exit();
    	}
    	else
    	{
    		throw new Redirect404Exception();
    	}
    
    	if ( !OW_DEBUG_MODE )
    	{
    		ob_end_clean();
    	}
    
    	exit();
    }    
    
    // invited
    public function ajaxInviteResponder()
    {
    	if ( OW::getRequest()->isAjax() )
    	{
    		if (isset($_REQUEST['parameters'])) {
    
    			$json = $_REQUEST['parameters'];
    
    			// get all these parameters
    			// parse json string to array
    			$parameters = json_decode($json, true);
    			$groupId = (int)$parameters['groupId'];
    			$offset = (int)$parameters['offset'];
    			$limit = (int)$parameters['limit'];
    			$total = (int)$parameters['total'];
    
    			// get events based on parameters
    			$userId = OW::getUser()->getId();
    			$events = ZLEVENT_BOL_EventService::getInstance()->findUserInvitedGroupEvents($groupId, $userId, $offset, $limit);
    			$eventinfos=array();
    			$userService = BOL_UserService::getInstance();
    			foreach ($events as $event)
    			{
    				$eventinfo = array();
    				$eventinfo['title'] = $event->title;
    				$eventinfo['description'] = $event->description;
    				$eventinfo['url'] = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->id));
    				$eventinfo['logo'] = ZLEVENT_BOL_EventService::getInstance()->getEventImageWithDefaultUrl($event);
    				$eventinfo['starttime'] = UTIL_DateTime::formatSimpleDate($event->getStartTimeStamp(),$event->getStartTimeDisable());
    				$user = $userService->findUserById($event->getUserId());
    				$eventinfo['username'] = $userService->getDisplayName($event->getUserId());
    				$eventinfo['userurl'] = $userService->getUserUrlForUsername($user->getUsername());
    
    				$eventinfos[] = $eventinfo;
    			}
    
    			$count = 0;
    			if ($events)
    				$count = count($events);
    
    			$hasmore = true;
    			if($offset + $count >= $total)
    				$hasmore = false;
    
    			$json_result = array(
    					'events' => $eventinfos,
    					'hasmore' => $hasmore
    			);
    
    			echo json_encode($json_result);
    
    		}
    
    		exit();
    	}
    	else
    	{
    		throw new Redirect404Exception();
    	}
    
    	if ( !OW_DEBUG_MODE )
    	{
    		ob_end_clean();
    	}
    
    	exit();
    }    

    // 添加新活动
    public function add()
    {
    	if ( !OW::getUser()->isAuthenticated() )
    	{
    		throw new AuthenticateException();
    	}
    	
        $language = OW::getLanguage();
        $this->setPageTitle($language->text('zlevent', 'add_page_title'));
        $this->setPageHeading($language->text('zlevent', 'add_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_add');

        OW::getDocument()->setDescription(OW::getLanguage()->text('zlevent', 'add_event_meta_description'));

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'zlevent', 'main_menu_item');

        // check permissions for this page
        if ( !OW::getUser()->isAuthorized('zlevent', 'add_event') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlevent', 'add_event');
            throw new AuthorizationException($status['msg']);
        }
        
        OW::getDocument()->addScript('http://api.map.baidu.com/api?v=2.0&ak=HL2OtpqEFglWT1j2RoS62eRD');
        
        // FIXME
        $searcharea = '北京市';
        $this->assign('searcharea', $searcharea);
        
        $form = new ZLEventAddForm('event_add');
        
        // added by hawk 添加隶属乐群部分
        $selectedGroupId = null;
        // 如果设置了乐群id，则判断当前用户是否是乐群的群主（FIXME － 扩充为有创建乐群活动的人），如果不是则给出提示并返回调用页面
        if ( isset($_GET['groupId']) )
        {
        	$groupId = (int)$_GET['groupId'];
        	$group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        	if(ZLGROUPS_BOL_Service::getInstance()->isUserCanCreateEvent($group,OW::getUser())==false)
        	{
        		OW::getFeedback()->error($language->text('zlevent', 'need_authority_create_group_event'));
        		$this->redirect($_GET['backUri']);
        	}
        	else 
        	{
        		$selectedGroupId = $groupId;
        	}
        }
        
        // 得到当前用户具有编辑权限的乐群, FIXME
        $groupInfos = array();
        $groups = ZLGROUPS_BOL_Service::getInstance()->findMyGroups(OW::getUser()->getId());
        //$groups = ZLGROUPS_BOL_Service::getInstance()->findAllGroupsByEditAuthorityForCurrentUser();
        $group_num = count($groups);
        if($group_num > 0)
	        foreach ( $groups as $group )
	        {
	        	$groupInfos[$group->id]['grouptitle'] = $group->title;
	        }
	    else 
	    {
	    	OW::getFeedback()->error($language->text('zlevent', 'need_group_to_create_event'));
	    	$this->redirect(OW::getRouter()->urlForRoute('zlevent.main_menu_route'));
	    }
        // 更新乐群下拉列表框
        $group = new Selectbox('group');
        foreach ( $groupInfos as $id => $value )
        {
        	$group->addOption($id, $value['grouptitle']);
        }
        $group->setRequired();
        $group->setHasInvitation(false);
        $group->setLabel($language->text('zlevent', 'add_form_group_label'));
        $form->addElement($group);
        
        if ($selectedGroupId!=null)
        	$form->getElement('group')->setValue($selectedGroupId);
        // 结束

        if ( date('n', time()) == 12 && date('j', time()) == 31 )
        {
            $defaultDate = (date('Y', time()) + 1) . '/1/1';
        }
        else if ( ( date('j', time()) + 1 ) > date('t') )
        {
            $defaultDate = date('Y', time()) . '/' . ( date('n', time()) + 1 ) . '/1';
        }
        else
        {
            $defaultDate = date('Y', time()) . '/' . date('n', time()) . '/' . ( date('j', time()) + 1 );
        }

        $form->getElement('start_date')->setValue($defaultDate);
        $form->getElement('end_date')->setValue($defaultDate);
        $form->getElement('start_time')->setValue('all_day');
        $form->getElement('end_time')->setValue('all_day');

        $checkboxId = UTIL_HtmlTag::generateAutoId('chk');
        $tdId = UTIL_HtmlTag::generateAutoId('td');
        $this->assign('tdId', $tdId);
        $this->assign('chId', $checkboxId);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("zlevent")->getStaticJsUrl() . 'event.js');
        OW::getDocument()->addOnloadScript("new eventAddForm(". json_encode(array('checkbox_id' => $checkboxId, 'end_date_id' => $form->getElement('end_date')->getId(), 'tdId' => $tdId )) .")");

        if ( OW::getRequest()->isPost() )
        {
            if ( !empty($_POST['endDateFlag']) )
            {
                $this->assign('endDateFlag', true);
            }

            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                
                $serviceEvent = new OW_Event(ZLEVENT_BOL_EventService::EVENT_BEFORE_EVENT_CREATE, array(), $data);
                OW::getEventManager()->trigger($serviceEvent);
                $data = $serviceEvent->getData();
                
                $dateArray = explode('/', $data['start_date']);

                $startStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);

                if ( $data['start_time'] != 'all_day' )
                {
                    $startStamp = mktime($data['start_time']['hour'], $data['start_time']['minute'], 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                }

                if ( !empty($_POST['endDateFlag']) && !empty($data['end_date']) )
                {
                    $dateArray = explode('/', $data['end_date']);
                    $endStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);

                    $endStamp = strtotime("+1 day", $endStamp);

                    if ( $data['end_time'] != 'all_day' )
                    {
                        $hour = 0;
                        $min = 0;

                        if( $data['end_time'] != 'all_day' )
                        {
                            $hour = $data['end_time']['hour'];
                            $min = $data['end_time']['minute'];
                        }

                        $dateArray = explode('/', $data['end_date']);
                        $endStamp = mktime($hour, $min, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                    }
                }
                
                $imageValid = true;
                $datesAreValid = true;
                $imagePosted = false;

                if ( !empty($_FILES['image']['name']) )
                {
                    if ( (int) $_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name']) )
                    {
                        $imageValid = false;
                        OW::getFeedback()->error($language->text('base', 'not_valid_image'));
                    }
                    else
                    {
                        $imagePosted = true;
                    }
                }

                if ( empty($endStamp) )
                {
                    $endStamp = strtotime("+1 day", $startStamp);
                    $endStamp = mktime(0, 0, 0, date('n',$endStamp), date('j',$endStamp), date('Y',$endStamp));
                }

                if ( !empty($endStamp) && $endStamp < $startStamp )
                {
                    $datesAreValid = false;
                    OW::getFeedback()->error($language->text('zlevent', 'add_form_invalid_end_date_error_message'));
                }

                if ( $imageValid && $datesAreValid )
                {
                    $event = new ZLEVENT_BOL_Event();
                    $event->setStartTimeStamp($startStamp);
                    $event->setEndTimeStamp($endStamp);
                    $event->setCreateTimeStamp(time());
                    $event->setTitle(htmlspecialchars($data['title']));
                    $event->setLocation(UTIL_HtmlTag::autoLink(strip_tags($data['location'])));
                    $event->setWhoCanView((int) $data['who_can_view']);
                    $event->setWhoCanInvite((int) $data['who_can_invite']);
                    $event->setDescription($data['desc']);
                    $event->setUserId(OW::getUser()->getId());
                    $event->setEndDateFlag( !empty($_POST['endDateFlag']) );
                    $event->setStartTimeDisable( $data['start_time'] == 'all_day' );
                    $event->setEndTimeDisable( $data['end_time'] == 'all_day' );

                    if ( $imagePosted )
                    {
                        $event->setImage(uniqid());
                    }
                    
                    $serviceEvent = new OW_Event(ZLEVENT_BOL_EventService::EVENT_ON_CREATE_EVENT, array('eventDto' => $event));
                    OW::getEventManager()->trigger($serviceEvent);

                    $this->eventService->saveEvent($event);
                    
			        // added by hawk
			        // 更新乐群地址信息
			        $location = $data['location'];
			        $addressinfo = $data['locationinfo'];
			        $address_details = ZLAREAS_CLASS_Utility::getInstance()->getAddressInfo($addressinfo);
			        $this->eventService->saveLocation(
			        		$event->id, 
			        		$location, 
			        		$address_details['formated_address'],
			        		$address_details['province'],
			        		$address_details['city'],
			        		$address_details['district'],
			        		$address_details['longitude'],
			        		$address_details['latitude']
			        ); 
			        
			        // 创建群乐隶属乐群信息
			        $this->eventService->saveEventGroup($event->id, $data['group']);
			        // ended by hawk
                    
                    if ( $imagePosted )
                    {
                        $this->eventService->saveEventImage($_FILES['image']['tmp_name'], $event->getImage());
                    }

                    // 将群乐创建者参与状态设为“参与”（yes）
                    $eventUser = new ZLEVENT_BOL_EventUser();
                    $eventUser->setEventId($event->getId());
                    $eventUser->setUserId(OW::getUser()->getId());
                    $eventUser->setTimeStamp(time());
                    $eventUser->setStatus(ZLEVENT_BOL_EventService::USER_STATUS_YES);
                    $this->eventService->saveEventUser($eventUser);
                    
                    OW::getFeedback()->info($language->text('zlevent', 'add_form_success_message'));

//                    if ( $event->getWhoCanView() == ZLEVENT_BOL_EventService::CAN_VIEW_ANYBODY )
//                    {
//                        $eventObj = new OW_Event('feed.action', array(
//                                'pluginKey' => 'zlevent',
//                                'entityType' => 'zlevent',
//                                'entityId' => $event->getId(),
//                                'userId' => $event->getUserId()
//                            ));
//                        OW::getEventManager()->trigger($eventObj);
//                    }
                    
                    BOL_AuthorizationService::getInstance()->trackAction('zlevent', 'add_event');

                    
                    $serviceEvent = new OW_Event(ZLEVENT_BOL_EventService::EVENT_AFTER_CREATE_EVENT, array('eventId' => $event->id, 'eventDto' => $event));
                    OW::getEventManager()->trigger($serviceEvent);
                    
                    $this->redirect(OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->getId())));
                }
            }
        }

        if( empty($_POST['endDateFlag']) )
        {
            //$form->getElement('start_time')->addAttribute('disabled', 'disabled');
            //$form->getElement('start_time')->addAttribute('style', 'display:none;');

            $form->getElement('end_date')->addAttribute('disabled', 'disabled');
            $form->getElement('end_date')->addAttribute('style', 'display:none;');

            $form->getElement('end_time')->addAttribute('disabled', 'disabled');
            $form->getElement('end_time')->addAttribute('style', 'display:none;');
        }

        $this->addForm($form);
    }

    /**
     * Get event by params(eventId)
     * 
     * @param array $params
     * @return ZLEVENT_BOL_Event 
     */
    private function getEventForParams( $params )
    {
        if ( empty($params['eventId']) )
        {
            throw new Redirect404Exception();
        }

        $event = $this->eventService->findEvent($params['eventId']);

        if ( $event === null )
        {
            throw new Redirect404Exception();
        }

        return $event;
    }


    // 编辑已有活动
    public function edit( $params )
    {
        $event = $this->getEventForParams($params);
        $language = OW::getLanguage();
        
        OW::getDocument()->addScript('http://api.map.baidu.com/api?v=2.0&ak=HL2OtpqEFglWT1j2RoS62eRD');
        
        // FIXME
        $searcharea = '北京市';
        $this->assign('searcharea', $searcharea);
        
        $form = new ZLEventAddForm('event_edit');

        $form->getElement('title')->setValue($event->getTitle());
        $form->getElement('desc')->setValue($event->getDescription());
        $form->getElement('location')->setValue($event->getLocation());
        $form->getElement('who_can_view')->setValue($event->getWhoCanView());
        $form->getElement('who_can_invite')->setValue($event->getWhoCanInvite());
        //$form->getElement('who_can_invite')->setValue($event->getWhoCanInvite());

        $startTimeArray = array('hour' => date('G', $event->getStartTimeStamp()), 'minute' => date('i', $event->getStartTimeStamp()));
        $form->getElement('start_time')->setValue($startTimeArray);

        $startDate = date('Y', $event->getStartTimeStamp()) . '/' . date('n', $event->getStartTimeStamp()) . '/' . date('j', $event->getStartTimeStamp());
        $form->getElement('start_date')->setValue($startDate);

        if ( $event->getEndTimeStamp() !== null )
        {
            $endTimeArray = array('hour' => date('G', $event->getEndTimeStamp()), 'minute' => date('i', $event->getEndTimeStamp()));
            $form->getElement('end_time')->setValue($endTimeArray);


            $endTimeStamp = $event->getEndTimeStamp();
            if ( $event->getEndTimeDisable() )
            {
                $endTimeStamp = strtotime("-1 day", $endTimeStamp);
            }

            $endDate = date('Y', $endTimeStamp) . '/' . date('n', $endTimeStamp) . '/' . date('j', $endTimeStamp);
            $form->getElement('end_date')->setValue($endDate);
        }

        if ( $event->getStartTimeDisable() )
        {
            $form->getElement('start_time')->setValue('all_day');
        }

        if ( $event->getEndTimeDisable() )
        {
            $form->getElement('end_time')->setValue('all_day');
        }
        
        // added by hawk, for location
        $field = new HiddenField('origin_lng');
        $form->addElement($field);
        $field = new HiddenField('origin_lat');
        $form->addElement($field);
        
        $detailedLocationInfo = ZLEVENT_BOL_EventService::getInstance()->findLocationDetailedInfoByEventId($event->id);
        $form->getElement('origin_lng')->setValue($detailedLocationInfo['longitude']);
        $form->getElement('origin_lat')->setValue($detailedLocationInfo['latitude']);
        $form->getElement('location')->setValue($detailedLocationInfo['location']);
        $form->getElement('locationinfo')->setValue($detailedLocationInfo['locationinfo']);
        
        // 更新乐群下拉列表框
        $selectedGroup = ZLEVENT_BOL_EventService::getInstance()->findGroupByEventId($event->id);
        
        $groupInfos = array();
        $groups = ZLGROUPS_BOL_Service::getInstance()->findMyGroups(OW::getUser()->getId());
        //$groups = ZLGROUPS_BOL_Service::getInstance()->findAllGroupsByEditAuthorityForCurrentUser();
        $group_num = count($groups);
        if($group_num > 0)
        foreach ( $groups as $group )
        {
        	$groupInfos[$group->id]['grouptitle'] = $group->title;
        }
        else
        {
        	OW::getFeedback()->error($language->text('zlevent', 'need_group_to_create_event'));
        	$this->redirect(OW::getRouter()->urlForRoute('zlevent.main_menu_route'));
        }
        
        $group = new Selectbox('group');
        foreach ( $groupInfos as $id => $value )
        {
        	$group->addOption($id, $value['grouptitle']);
        }
        $group->setRequired();
        $group->setHasInvitation(false);
        $group->setLabel($language->text('zlevent', 'add_form_group_label'));
        $form->addElement($group);
        
        if ($selectedGroup!=null)
        	$form->getElement('group')->setValue($selectedGroup->id);
        // 结束
        
        // ended by hawk

        $form->getSubmitElement('submit')->setValue(OW::getLanguage()->text('zlevent', 'edit_form_submit_label'));

        $checkboxId = UTIL_HtmlTag::generateAutoId('chk');
        $tdId = UTIL_HtmlTag::generateAutoId('td');
        $this->assign('tdId', $tdId);
        $this->assign('chId', $checkboxId);
        
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("zlevent")->getStaticJsUrl() . 'event.js');
        OW::getDocument()->addOnloadScript("new eventAddForm(". json_encode(array('checkbox_id' => $checkboxId, 'end_date_id' => $form->getElement('end_date')->getId(), 'tdId' => $tdId )) .")");

        if ( $event->getImage() )
        {
            $this->assign('imgsrc', $this->eventService->generateImageUrl($event->getImage(), true));
        }

        $endDateFlag = $event->getEndDateFlag();

        if ( OW::getRequest()->isPost() )
        {
            $endDateFlag = !empty($_POST['endDateFlag']);

            //$this->assign('endDateFlag', !empty($_POST['endDateFlag']));

            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                
                $serviceEvent = new OW_Event(ZLEVENT_BOL_EventService::EVENT_BEFORE_EVENT_EDIT, array('eventId' => $event->id), $data);
                OW::getEventManager()->trigger($serviceEvent);
                $data = $serviceEvent->getData();
                
                $dateArray = explode('/', $data['start_date']);

                $startStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);

                if ( $data['start_time'] != 'all_day' )
                {
                    $startStamp = mktime($data['start_time']['hour'], $data['start_time']['minute'], 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                }

                if ( !empty($_POST['endDateFlag']) && !empty($data['end_date']) )
                {
                        $dateArray = explode('/', $data['end_date']);
                        $endStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                        $endStamp = strtotime("+1 day", $endStamp);

                        if ( $data['end_time'] != 'all_day' )
                        {
                            $hour = 0;
                            $min = 0;

                            if( $data['end_time'] != 'all_day' )
                            {
                                $hour = $data['end_time']['hour'];
                                $min = $data['end_time']['minute'];
                            }

                            $dateArray = explode('/', $data['end_date']);
                            $endStamp = mktime($hour, $min, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                        }
                }

                $event->setStartTimeStamp($startStamp);
                
                if ( empty($endStamp) )
                {
                    $endStamp = strtotime("+1 day", $startStamp);
                    $endStamp = mktime(0, 0, 0, date('n',$endStamp), date('j',$endStamp), date('Y',$endStamp));
                }
                
                if ( $startStamp > $endStamp )
                {
                    OW::getFeedback()->error($language->text('zlevent', 'add_form_invalid_end_date_error_message'));
                    $this->redirect();
                }
                else
                {
                    $event->setEndTimeStamp($endStamp);

                    if ( !empty($_FILES['image']['name']) )
                    {
                        if ( (int) $_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name']) )
                        {
                            OW::getFeedback()->error($language->text('base', 'not_valid_image'));
                            $this->redirect();
                        }
                        else
                        {
                            $event->setImage(uniqid());
                            $this->eventService->saveEventImage($_FILES['image']['tmp_name'], $event->getImage());

                        }
                    }
                                        
                    $event->setTitle(htmlspecialchars($data['title']));
                    $event->setLocation(UTIL_HtmlTag::autoLink(strip_tags($data['location'])));
                    $event->setWhoCanView((int) $data['who_can_view']);
                    $event->setWhoCanInvite((int) $data['who_can_invite']);
                    $event->setDescription($data['desc']);
                    $event->setEndDateFlag(!empty($_POST['endDateFlag']));
                    $event->setStartTimeDisable( $data['start_time'] == 'all_day' );
                    $event->setEndTimeDisable( $data['end_time'] == 'all_day' );

                    $this->eventService->saveEvent($event);
                    
                    // added by hawk, 更新地址信息
                    $location = $data['location'];
                    $addressinfo = $data['locationinfo'];
                    $address_details = ZLAREAS_CLASS_Utility::getInstance()->getAddressInfo($addressinfo);
                    $this->eventService->saveLocation(
                    		$event->id,
                    		$location,
                    		$address_details['formated_address'],
                    		$address_details['province'],
                    		$address_details['city'],
                    		$address_details['district'],
                    		$address_details['longitude'],
                    		$address_details['latitude']
                    );                    
                    // ended by hawk
                    
                    $e = new OW_Event(ZLEVENT_BOL_EventService::EVENT_AFTER_EVENT_EDIT, array('eventId' => $event->id));
                    OW::getEventManager()->trigger($e);
                    
                    OW::getFeedback()->info($language->text('zlevent', 'edit_form_success_message'));
                    $this->redirect(OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->getId())));
                }
            }
        }

        if( !$endDateFlag )
        {
           // $form->getElement('start_time')->addAttribute('disabled', 'disabled');
           // $form->getElement('start_time')->addAttribute('style', 'display:none;');

            $form->getElement('end_date')->addAttribute('disabled', 'disabled');
            $form->getElement('end_date')->addAttribute('style', 'display:none;');

            $form->getElement('end_time')->addAttribute('disabled', 'disabled');
            $form->getElement('end_time')->addAttribute('style', 'display:none;');
        }

        $this->assign('endDateFlag', $endDateFlag);

        $this->setPageHeading($language->text('zlevent', 'edit_page_heading'));
        $this->setPageTitle($language->text('zlevent', 'edit_page_title'));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'zlevent', 'main_menu_item');
        $this->addForm($form);
    }

    // 删除指定活动
    public function delete( $params )
    {
        $event = $this->getEventForParams($params);

        if ( !OW::getUser()->isAuthenticated() || ( OW::getUser()->getId() != $event->getUserId() && !OW::getUser()->isAuthorized('zlevent') ) )
        {
            throw new Redirect403Exception();
        }

        $this->eventService->deleteEvent($event->getId());
        OW::getFeedback()->info(OW::getLanguage()->text('zlevent', 'delete_success_message'));
        $this->redirect(OW::getRouter()->urlForRoute('zlevent.main_menu_route'));
    }

    
	// 查看指定活动信息
    public function view( $params )
    {
        $event = $this->getEventForParams($params);

        $cmpId = UTIL_HtmlTag::generateAutoId('cmp');

        $this->assign('contId', $cmpId);

        if ( !OW::getUser()->isAuthorized('zlevent', 'view_event') && $event->getUserId() != OW::getUser()->getId() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlevent', 'view_event');
            throw new AuthorizationException($status['msg']);
        }

        // guest gan't view private events
        if ( (int) $event->getWhoCanView() === ZLEVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && !OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->urlForRoute('zlevent.private_event', array('eventId' => $event->getId())));
        }

        $eventInvite = $this->eventService->findEventInvite($event->getId(), OW::getUser()->getId());
        $eventUser = $this->eventService->findEventUser($event->getId(), OW::getUser()->getId());

        // check if user can view event
        if ( (int) $event->getWhoCanView() === ZLEVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY 
        		&& $eventUser === null && $eventInvite === null && !OW::getUser()->isAuthorized('zlevent') )
        {
            $this->redirect(OW::getRouter()->urlForRoute('zlevent.private_event', array('eventId' => $event->getId())));
        }

        if ( OW::getUser()->isAuthorized('zlevent') || OW::getUser()->getId() == $event->getUserId() )
        {
            $this->assign('editArray', array(
                'edit' => array('url' => OW::getRouter()->urlForRoute('zlevent.edit', array('eventId' => $event->getId())), 'label' => OW::getLanguage()->text('zlevent', 'edit_button_label')),
                'delete' =>
                array(
                    'url' => OW::getRouter()->urlForRoute('zlevent.delete', array('eventId' => $event->getId())),
                    'label' => OW::getLanguage()->text('zlevent', 'delete_button_label'),
                    'confirmMessage' => OW::getLanguage()->text('zlevent', 'delete_confirm_message')
                ),
                )
            );
        }
        
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'zlevent', 'main_menu_item');
        
        $this->setPageHeading($event->getTitle());
        $this->setPageTitle(OW::getLanguage()->text('zlevent', 'event_view_page_heading', array('event_title' => $event->getTitle())));
        $this->setPageHeadingIconClass('ow_ic_calendar');
        OW::getDocument()->setDescription(UTIL_String::truncate(strip_tags($event->getDescription()), 200, '...'));

        // 添加bootstrap支持
//         OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('zlareas')->getStaticCssUrl() . 'bootstrap.min.css');
//         OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlareas')->getStaticJsUrl() . 'jquery-1.10.2.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY+10);
//         OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlareas')->getStaticJsUrl() . 'bootstrap.min.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY);
        
        // 将活动详细信息作为变量传给视图（view）
        $eventId = $event->getId();
        $belongingGroup = ZLEVENT_BOL_EventService::getInstance()->findGroupByEventId($event->getId());
        $groupTitle = null;
        $groupLink = null;
        $groupImage = null;
        $totalhistorical = null;
        $totalupcoming = null;
        $group_founder_image = null;
        $group_founder_url = null;
        $group_founder_title = null;
        
        if($belongingGroup != null) 
        {
        	$groupTitle = $belongingGroup->title;
        	$groupLink = OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $belongingGroup->getId()));
        	$groupImage = empty($belongingGroup->imageHash) ? false : ZLGROUPS_BOL_Service::getInstance()->getGroupImageUrl($belongingGroup, ZLGROUPS_BOL_Service::IMAGE_SIZE_BIG);
        	$totalhistorical = 'FIXME';
        	$totalupcoming = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsCountByGroupId($belongingGroup->getId(), false);
        	$totalhistorical = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsCountByGroupId($belongingGroup->getId(), true);
        	$idlist = array();
        	$idlist[] = $belongingGroup->userId;
        	$data = BOL_AvatarService::getInstance()->getDataForUserAvatars($idlist); // 得到用户详细信息
        	$group_founder_image = $data[$belongingGroup->userId][ 'src'];
        	$group_founder_url = $data[$belongingGroup->userId]['url'];
        	$group_founder_title = $data[$belongingGroup->userId]['title'];
        }
        
        $imageurl = ( $event->getImage() ? $this->eventService->generateImageUrl($event->getImage(), false) : null );
        
        // 信息来源
        $originurlvalue = ZLBASE_BOL_Service::getInstance()->getValue('zlevent', $event->getId(), 'originurl');
        if($originurlvalue!=null)
        	$this->assign('originurl', $originurlvalue);
        
        $infoArray = array(
            'id' => $event->getId(),
            'image' => $imageurl,
            'date' => UTIL_DateTime::formatSimpleDate($event->getStartTimeStamp(), $event->getStartTimeDisable()),
            'endDate' => $event->getEndTimeStamp() === null || !$event->getEndDateFlag() ? null : UTIL_DateTime::formatSimpleDate($event->getEndTimeDisable() ? strtotime("-1 day", $event->getEndTimeStamp()) : $event->getEndTimeStamp(),$event->getEndTimeDisable()),
            'location' => $event->getLocation(),
        	'locationinfo' => '', // TBD - 将地址详细信息作为数组
            'desc' => UTIL_HtmlTag::autoLink($event->getDescription()),
            'title' => $event->getTitle(),
            'creatorName' => BOL_UserService::getInstance()->getDisplayName($event->getUserId()),
            'creatorLink' => BOL_UserService::getInstance()->getUserUrl($event->getUserId()),
        	'groupTitle' => $groupTitle,
        	'groupLink' => $groupLink,
        	'groupImage' => $groupImage,
        	'totalhistorical' => $totalhistorical,
        	'totalupcoming' =>  $totalupcoming,
       		'group_founder_image' =>  $group_founder_image,
       		'group_founder_url' =>  $group_founder_url,
       		'group_founder_title' =>  $group_founder_title
        );
        $this->assign('info', $infoArray);


        // 百度分享
        $current_url = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $eventId));
        $this->assign('current_url', $current_url);
        $logoiconurl = ZLEVENT_BOL_EventService::getInstance()->getEventImageWithDefaultUrl($event);
        $this->assign('logoiconurl', $logoiconurl);
        $this->assign('title', $event->title);
        $this->assign('description', UTIL_String::truncate(strip_tags($event->description), 100, '...'));
        
        
        // event attend form
        // 用户改变参加状态部分
        if ( OW::getUser()->isAuthenticated() && $event->getEndTimeStamp() > time() )
        {
            if ( $eventUser !== null )
            {
                $this->assign('currentStatus', OW::getLanguage()->text('zlevent', 'user_status_label_' . $eventUser->getStatus()));
            }
            $this->addForm(new ZLAttendForm($event->getId(), $cmpId));

            $onloadJs = "
                var \$context = $('#" . $cmpId . "');
                $('#event_attend_yes_btn').click(
                    function(){
                        $('input[name=attend_status]', \$context).val(" . ZLEVENT_BOL_EventService::USER_STATUS_YES . ");
                    }
                );
                $('#event_attend_maybe_btn').click(
                    function(){
                        $('input[name=attend_status]', \$context).val(" . ZLEVENT_BOL_EventService::USER_STATUS_MAYBE . ");
                    }
                );
                $('#event_attend_no_btn').click(
                    function(){
                        $('input[name=attend_status]', \$context).val(" . ZLEVENT_BOL_EventService::USER_STATUS_NO . ");
                    }
                );

                $('.current_status a', \$context).click(
                    function(){
                        $('.attend_buttons .buttons', \$context).fadeIn(500);
                    }
                );
            ";

            OW::getDocument()->addOnloadScript($onloadJs);
        }
        else
        {
            $this->assign('no_attend_form', true);
        }
        
        // 动态构建邀请部分代码
        if ( $event->getEndTimeStamp() > time() && ((int) $event->getUserId() === OW::getUser()->getId() 
        		|| ( (int) $event->getWhoCanInvite() === ZLEVENT_BOL_EventService::CAN_INVITE_PARTICIPANT && $eventUser !== null) ) )
        {
            $params = array(
                $event->id
            );

            $this->assign('inviteLink', true);
            OW::getDocument()->addOnloadScript("
                var eventFloatBox;
                $('#inviteLink', $('#" . $cmpId . "')).click(
                    function(){
                        eventFloatBox = OW.ajaxFloatBox('ZLEVENT_CMP_InviteUserListSelect', " . json_encode($params) . ", {width:600, iconClass: 'ow_ic_user', title: " . json_encode(OW::getLanguage()->text('zlevent', 'friends_invite_button_label')) . "});
                    }
                );
                OW.bind('base.avatar_user_list_select',
                    function(list){
                        eventFloatBox.close();
                        $.ajax({
                            type: 'POST',
                            url: " . json_encode(OW::getRouter()->urlFor('ZLEVENT_CTRL_Base', 'inviteResponder')) . ",
                            data: 'eventId=" . json_encode($event->getId()) . "&userIdList='+JSON.stringify(list),
                            dataType: 'json',
                            success : function(data){
                                if( data.messageType == 'error' ){
                                    OW.error(data.message);
                                }
                                else{
                                    OW.info(data.message);
                                }
                            },
                            error : function( XMLHttpRequest, textStatus, errorThrown ){
                                OW.error(textStatus);
                            }
                        });
                    }
                );
            ");
        }

        // 添加组件： 1）评论； 2）活动用户列表
        $cmntParams = new BASE_CommentsParams('zlevent', 'zlevent');
        $cmntParams->setEntityId($event->getId());
        $cmntParams->setOwnerId($event->getUserId());
        $this->addComponent('comments', new BASE_CMP_Comments($cmntParams));
        $this->addComponent('userListCmp', new ZLEVENT_CMP_EventUsers($event->getId()));
        
        // 添加标签部分
        
        $tagParams = new ZLTAGS_CLASS_Params('zlevent', ZLEVENT_BOL_EventService::ENTITY_TYPE_TAG);
        $tagParams->setEntityId($event->getId());
        $isOwner = ( (OW::getUser()->getId())== $event->userId);
        $tagParams->setAddTag(true);
        
         $this->addComponent('tags', new ZLTAGS_CMP_Tags($tagParams));        
        
        // 添加地址部分
        $detailedLocationInfo = ZLEVENT_BOL_EventService::getInstance()->findLocationDetailedInfoByEventId($event->getId());
        $this->assign("location", $detailedLocationInfo['location']);
        $this->assign("formated_address", $detailedLocationInfo['formated_address']);
        $this->assign("longitude", $detailedLocationInfo['longitude']);
        $this->assign("latitude", $detailedLocationInfo['latitude']);
        

        // 搜集工具栏项
        $event = new BASE_CLASS_EventCollector(ZLEVENT_BOL_EventService::EVENT_COLLECT_TOOLBAR, array(
            "eventId" => $event->getId()
        ));
        OW::getEventManager()->trigger($event);
        $this->assign("toolbar", $event->getData());
    }

	// 显示
    public function eventsList( $params )
    {
        if ( empty($params['list']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthorized('zlevent', 'view_event') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlevent', 'view_event');
            throw new AuthorizationException($status['msg']);
        }

        $configs = $this->eventService->getConfigs();
        $page = ( empty($_GET['page']) || (int) $_GET['page'] < 0 ) ? 1 : (int) $_GET['page'];

        $language = OW::getLanguage();

        $toolbarList = array();

        switch ( trim($params['list']) )
        {
            case 'created':		// 我创建的活动列表
                if ( !OW::getUser()->isAuthenticated() )
                {
                    throw new Redirect403Exception();
                }

                $this->setPageHeading($language->text('zlevent', 'event_created_by_me_page_heading'));
                $this->setPageTitle($language->text('zlevent', 'event_created_by_me_page_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                $events = $this->eventService->findUserEvents(OW::getUser()->getId(), $page);
                $eventsCount = $this->eventService->findUserEventsCount(OW::getUser()->getId());
                break;

            case 'joined':		// 我参加的活动列表
                if ( !OW::getUser()->isAuthenticated() )
                {
                    throw new Redirect403Exception();
                }
                $contentMenu = ZLEVENT_BOL_EventService::getInstance()->getContentMenu();
                $this->addComponent('contentMenu', $contentMenu);
                $this->setPageHeading($language->text('zlevent', 'event_joined_by_me_page_heading'));
                $this->setPageTitle($language->text('zlevent', 'event_joined_by_me_page_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');

                $events = $this->eventService->findUserParticipatedEvents(OW::getUser()->getId(), $page);
                $eventsCount = $this->eventService->findUserParticipatedEventsCount(OW::getUser()->getId());
                break;

            case 'latest':		// 最近要发生的活动列表
                $contentMenu = ZLEVENT_BOL_EventService::getInstance()->getContentMenu();
                $contentMenu->getElement('latest')->setActive(true);
                $this->addComponent('contentMenu', $contentMenu);
                $this->setPageHeading($language->text('zlevent', 'latest_events_page_heading'));
                $this->setPageTitle($language->text('zlevent', 'latest_events_page_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                OW::getDocument()->setDescription($language->text('zlevent', 'latest_events_page_desc'));
                $events = $this->eventService->findPublicEvents($page);
                $eventsCount = $this->eventService->findPublicEventsCount();
                break;

            case 'user-participated-events':	// 用户参与的活动列表

                if ( empty($_GET['userId']) )
                {
                    throw new Redirect404Exception();
                }

                $user = BOL_UserService::getInstance()->findUserById($_GET['userId']);

                if ( $user === null )
                {
                    throw new Redirect404Exception();
                }

                $eventParams = array(
                    'action' => 'zlevent_view_attend_events',
                    'ownerId' => $user->getId(),
                    'viewerId' => OW::getUser()->getId()
                );

                OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);

                $displayName = BOL_UserService::getInstance()->getDisplayName($user->getId());

                $this->setPageHeading($language->text('zlevent', 'user_participated_events_page_heading', array('display_name' => $displayName)));
                $this->setPageTitle($language->text('zlevent', 'user_participated_events_page_title', array('display_name' => $displayName)));
                OW::getDocument()->setDescription($language->text('zlevent', 'user_participated_events_page_desc', array('display_name' => $displayName)));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                $events = $this->eventService->findUserParticipatedPublicEvents($user->getId(), $page);
                $eventsCount = $this->eventService->findUserParticipatedPublicEventsCount($user->getId());
                break;

            case 'past':		// 已发生活动列表
                $contentMenu = ZLEVENT_BOL_EventService::getInstance()->getContentMenu();
                $this->addComponent('contentMenu', $contentMenu);
                $this->setPageHeading($language->text('zlevent', 'past_events_page_heading'));
                $this->setPageTitle($language->text('zlevent', 'past_events_page_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                OW::getDocument()->setDescription($language->text('zlevent', 'past_events_page_desc'));
                $events = $this->eventService->findPublicEvents($page, null, true);
                $eventsCount = $this->eventService->findPublicEventsCount(true);
                break;

            case 'invited':		// 被邀请参加的活动列表
                if ( !OW::getUser()->isAuthenticated() )
                {
                    throw new Redirect403Exception();
                }

                $this->eventService->hideInvitationByUserId(OW::getUser()->getId());

                $contentMenu = ZLEVENT_BOL_EventService::getInstance()->getContentMenu();
                $this->addComponent('contentMenu', $contentMenu);
                $this->setPageHeading($language->text('zlevent', 'invited_events_page_heading'));
                $this->setPageTitle($language->text('zlevent', 'invited_events_page_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                $events = $this->eventService->findUserInvitedEvents(OW::getUser()->getId(), $page);
                $eventsCount = $this->eventService->findUserInvitedEventsCount(OW::getUser()->getId());
                
                foreach( $events as $event )
                {
                    $toolbarList[$event->getId()] = array();

                    $paramsList = array( 'eventId' => $event->getId(), 'page' => $page, 'list' => trim($params['list']) );

                    $acceptUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('zlevent.invite_accept', $paramsList), array('page' => $page));
                    $ignoreUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('zlevent.invite_decline', $paramsList), array('page' => $page));

                    $toolbarList[$event->getId()][] = array('label' => $language->text('zlevent', 'accept_request'),'href' => $acceptUrl);
                    $toolbarList[$event->getId()][] = array('label' => $language->text('zlevent', 'ignore_request'),'href' => $ignoreUrl);
                    
                }

                break;

            default:
                throw new Redirect404Exception();
        }

        // 添加paging控件
        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($eventsCount / $configs[ZLEVENT_BOL_EventService::CONF_EVENTS_COUNT_ON_PAGE]), 5));

        // 添加“建乐子”按钮
        $addUrl = OW::getRouter()->urlForRoute('zlevent.add');

        $script = '$("input.add_event_button").click(function() {
                window.location='.json_encode($addUrl).';
            });';

        if ( !OW::getUser()->isAuthorized('zlevent', 'add_event') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlevent', 'add_event');

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $script = '$("input.add_event_button").click(function() {
                        OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                    });';
            }
            else if ( $status['status'] == BOL_AuthorizationService::STATUS_DISABLED )
            {
                $this->assign('noButton', true);
            }
        }

        OW::getDocument()->addOnloadScript($script);
		// end of 添加“建乐子”按钮
        
        if ( empty($events) )
        {
            $this->assign('no_events', true);
        }
        
        $this->assign('listType', trim($params['list']));
        $this->assign('page', $page);
        $this->assign('events', $this->eventService->getListingDataWithToolbar($events, $toolbarList));
        $this->assign('toolbarList', $toolbarList);
        $this->assign('add_new_url', OW::getRouter()->urlForRoute('zlevent.add'));
        
        // 激活主菜单上的“群乐”项
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'zlevent', 'main_menu_item');
    }

    // 接受活动参加邀请
    public function inviteListAccept( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();
        $feedback = array('messageType' => 'error');
        $exit = false;
        $attendedStatus = 1;

        if ( !empty($attendedStatus) && !empty($params['eventId']) && $this->eventService->canUserView($params['eventId'], $userId) )
        {
            $event = $this->eventService->findEvent($params['eventId']);

            if ( $event->getEndTimeStamp() < time() )
            {
                throw new Redirect404Exception();
            }

            $eventUser = $this->eventService->findEventUser($params['eventId'], $userId);

            if ( $eventUser !== null && (int) $eventUser->getStatus() === (int) $attendedStatus )
            {
                $feedback['message'] = OW::getLanguage()->text('zlevent', 'user_status_not_changed_error');
                //exit(json_encode($feedback));
            }

            if ( $event->getUserId() == OW::getUser()->getId() && (int) $attendedStatus == ZLEVENT_BOL_EventService::USER_STATUS_NO )
            {
                $feedback['message'] = OW::getLanguage()->text('zlevent', 'user_status_author_cant_leave_error');
                //exit(json_encode($feedback));
            }

            if ( !$exit )
            {
                if ( $eventUser === null )
                {
                    $eventUser = new ZLEVENT_BOL_EventUser();
                    $eventUser->setUserId($userId);
                    $eventUser->setEventId((int) $params['eventId']);
                }

                $eventUser->setStatus((int) $attendedStatus);
                $eventUser->setTimeStamp(time());
                $this->eventService->saveEventUser($eventUser);
                $this->eventService->deleteUserEventInvites((int)$params['eventId'], OW::getUser()->getId());

                $feedback['message'] = OW::getLanguage()->text('zlevent', 'user_status_updated');
                $feedback['messageType'] = 'info';

                if ( $eventUser->getStatus() == ZLEVENT_BOL_EventService::USER_STATUS_YES && $event->getWhoCanView() == ZLEVENT_BOL_EventService::CAN_VIEW_ANYBODY )
                {
                    $userName = BOL_UserService::getInstance()->getDisplayName($event->getUserId());
                    $userUrl = BOL_UserService::getInstance()->getUserUrl($event->getUserId());
                    $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

                    OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                            'activityType' => 'zlevent-join',
                            'activityId' => $eventUser->getId(),
                            'entityId' => $event->getId(),
                            'entityType' => 'zlevent',
                            'userId' => $eventUser->getUserId(),
                            'pluginKey' => 'zlevent'
                            ), array(
                            'eventId' => $event->getId(),
                            'userId' => $eventUser->getUserId(),
                            'eventUserId' => $eventUser->getId(),
                            'string' =>  OW::getLanguage()->text('zlevent', 'feed_actiovity_attend_string' ,  array( 'user' => $userEmbed )),
                            'feature' => array()
                        )));
                }
            }
        }
        else
        {
            $feedback['message'] = OW::getLanguage()->text('zlevent', 'user_status_update_error');
        }

        if ( !empty($feedback['message']) )
        {
            switch( $feedback['messageType'] )
            {
                case 'info':
                    OW::getFeedback()->info($feedback['message']);
                    break;
                case 'warning':
                    OW::getFeedback()->warning($feedback['message']);
                    break;
                case 'error':
                    OW::getFeedback()->error($feedback['message']);
                    break;
            }
        }

        $paramsList = array();

        if ( !empty($params['page']) )
        {
            $paramsList['page'] = $params['page'];
        }

        if ( !empty($params['list']) )
        {
            $paramsList['list'] = $params['list'];
        }

        $this->redirect(OW::getRouter()->urlForRoute('zlevent.view_event_list', $paramsList));
    }

    public function inviteListDecline( $params )
    {
        if ( !empty($params['eventId']) )
        {
            $this->eventService->deleteUserEventInvites((int)$params['eventId'], OW::getUser()->getId());
            OW::getLanguage()->text('zlevent', 'user_status_updated');
        }
        else
        {
            OW::getLanguage()->text('zlevent', 'user_status_update_error');
        }

        if ( !empty($params['page']) )
        {
            $paramsList['page'] = $params['page'];
        }

        if ( !empty($params['list']) )
        {
            $paramsList['list'] = $params['list'];
        }

        $this->redirect(OW::getRouter()->urlForRoute('zlevent.view_event_list', $paramsList));
    }


    // 显示活动用户列表
    public function eventUserLists( $params )
    {
        if ( empty($params['eventId']) || empty($params['list']) )
        {
            throw new Redirect404Exception();
        }

        $event = $this->eventService->findEvent((int) $params['eventId']);

        if ( $event === null )
        {
            throw new Redirect404Exception();
        }

        $listArray = array_flip($this->eventService->getUserListsArray());

        if ( !array_key_exists($params['list'], $listArray) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthorized('zlevent', 'view_event') && $event->getUserId() != OW::getUser()->getId() && !OW::getUser()->isAuthorized('zlevent') )
        {
            $this->assign('authErrorText', OW::getLanguage()->text('zlevent', 'event_view_permission_error_message'));
            return;
        }

        // guest gan't view private events
        if ( (int) $event->getWhoCanView() === ZLEVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && !OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->urlForRoute('zlevent.private_event', array('eventId' => $event->getId())));
        }

        $eventInvite = $this->eventService->findEventInvite($event->getId(), OW::getUser()->getId());
        $eventUser = $this->eventService->findEventUser($event->getId(), OW::getUser()->getId());

        // check if user can view event
        if ( (int) $event->getWhoCanView() === ZLEVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && $eventUser === null && $eventInvite === null && !OW::getUser()->isAuthorized('zlevent') )
        {
            $this->redirect(OW::getRouter()->urlForRoute('zlevent.private_event', array('eventId' => $event->getId())));
        }

        $language = OW::getLanguage();
        $configs = $this->eventService->getConfigs();
        $page = ( empty($_GET['page']) || (int) $_GET['page'] < 0 ) ? 1 : (int) $_GET['page'];
        $status = $listArray[$params['list']];
        $eventUsers = $this->eventService->findEventUsers($event->getId(), $status, $page);
        $eventUsersCount = $this->eventService->findEventUsersCount($event->getId(), $status);

        $userIdList = array();

        /* @var $eventUser ZLEVENT_BOL_EventUser */
        foreach ( $eventUsers as $eventUser )
        {
            $userIdList[] = $eventUser->getUserId();
        }

        $userDtoList = BOL_UserService::getInstance()->findUserListByIdList($userIdList);

        $this->addComponent('users', new ZLEVENT_CMP_EventUsersList($userDtoList, $eventUsersCount, $configs[ZLEVENT_BOL_EventService::CONF_EVENT_USERS_COUNT_ON_PAGE], true));

        $this->setPageHeading($language->text('zlevent', 'user_list_page_heading_' . $status, array('eventTitle' => $event->getTitle())));
        $this->setPageTitle($language->text('zlevent', 'user_list_page_heading_' . $status, array('eventTitle' => $event->getTitle())));
        OW::getDocument()->setDescription($language->text('zlevent', 'user_list_page_desc_' . $status, array('eventTitle' => $event->getTitle())));

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'zlevent', 'main_menu_item');
        
        $this->assign("eventId", $event->id);
    }

    public function privateEvent( $params )
    {
        $language = OW::getLanguage();

        $this->setPageTitle($language->text('zlevent', 'private_page_title'));
        $this->setPageHeading($language->text('zlevent', 'private_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_lock');

        $eventId = $params['eventId'];
        $event = $this->eventService->findEvent((int) $eventId);

        $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($event->userId));
        $displayName = BOL_UserService::getInstance()->getDisplayName($event->userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($event->userId);

        $this->assign('zlevent', $event);
        $this->assign('avatar', $avatarList[$event->userId]);
        $this->assign('displayName', $displayName);
        $this->assign('userUrl', $userUrl);
        $this->assign('creator', $language->text('zlevent', 'creator'));
    }
    
    /**
     * Responder for event attend form
     */
    public function attendFormResponder()
    {
        if ( !OW::getRequest()->isAjax() || !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();
        $respondArray = array('messageType' => 'error');
        
        if ( !empty($_POST['attend_status']) && in_array((int) $_POST['attend_status'], array(1, 2, 3)) && !empty($_POST['eventId']) && $this->eventService->canUserView($_POST['eventId'], $userId) )
        {
            $event = $this->eventService->findEvent($_POST['eventId']);
            
            if ( $event->getEndTimeStamp() < time() )
            {
                throw new Redirect404Exception();
            }

            $eventUser = $this->eventService->findEventUser($_POST['eventId'], $userId);

            if ( $eventUser !== null && (int) $eventUser->getStatus() === (int) $_POST['attend_status'] )
            {
                $respondArray['message'] = OW::getLanguage()->text('zlevent', 'user_status_not_changed_error');
                exit(json_encode($respondArray));
            }

            if ( $event->getUserId() == OW::getUser()->getId() && (int) $_POST['attend_status'] == ZLEVENT_BOL_EventService::USER_STATUS_NO )
            {
                $respondArray['message'] = OW::getLanguage()->text('zlevent', 'user_status_author_cant_leave_error');
                exit(json_encode($respondArray));
            }

            if ( $eventUser === null )
            {
                $eventUser = new ZLEVENT_BOL_EventUser();
                $eventUser->setUserId($userId);
                $eventUser->setEventId((int) $_POST['eventId']);
            }

            $eventUser->setStatus((int) $_POST['attend_status']);
            $eventUser->setTimeStamp(time());
            $this->eventService->saveEventUser($eventUser);

            $this->eventService->deleteUserEventInvites((int)$_POST['eventId'], OW::getUser()->getId());

            $e = new OW_Event(ZLEVENT_BOL_EventService::EVENT_ON_CHANGE_USER_STATUS, array('eventId' => $event->id, 'userId' => $eventUser->userId));
            OW::getEventManager()->trigger($e);
            
            $respondArray['message'] = OW::getLanguage()->text('zlevent', 'user_status_updated');
            $respondArray['messageType'] = 'info';
            $respondArray['currentLabel'] = OW::getLanguage()->text('zlevent', 'user_status_label_' . $eventUser->getStatus());
            $respondArray['eventId'] = (int) $_POST['eventId'];
            //$eventUsersCmp = new ZLEVENT_CMP_EventUsers((int) $_POST['eventId']);
            //$respondArray['eventUsersCmp'] = $eventUsersCmp->render();
            $respondArray['newInvCount'] = $this->eventService->findUserInvitedEventsCount(OW::getUser()->getId());

            if ( $eventUser->getStatus() == ZLEVENT_BOL_EventService::USER_STATUS_YES && $event->getWhoCanView() == ZLEVENT_BOL_EventService::CAN_VIEW_ANYBODY )
            {
//                $params = array(
//                    'pluginKey' => 'zlevent',
//                    'entityType' => 'zlevent_join',
//                    'entityId' => $eventUser->getUserId(),
//                    'userId' => $eventUser->getUserId()
//                );
//
//                $url = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->getId()));
//                $thumb = $this->eventService->generateImageUrl($event->getId(), true);
//
//
//
//                $dataValue = array(
//                    'time' => $eventUser->getTimeStamp(),
//                    'string' => OW::getLanguage()->text('zlevent', 'feed_user_join_string'),
//                    'content' => '<div class="clearfix"><div class="ow_newsfeed_item_picture">
//                        <a href="' . $url . '"><img src="' . $thumb . '" /></a>
//                        </div><div class="ow_newsfeed_item_content">
//                        <a class="ow_newsfeed_item_title" href="' . $url . '">' . $event->getTitle() . '</a><div class="ow_remark">' . strip_tags($event->getDescription()) . '</div></div></div>',
//                    'view' => array(
//                        'iconClass' => 'ow_ic_calendar'
//                    )
//                );
//
//                $fEvent = new OW_Event('feed.action', $params, $dataValue);
//                OW::getEventManager()->trigger($fEvent);

                $userName = BOL_UserService::getInstance()->getDisplayName($event->getUserId());
                $userUrl = BOL_UserService::getInstance()->getUserUrl($event->getUserId());
                $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

                OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                        'activityType' => 'zlevent-join',
                        'activityId' => $eventUser->getId(),
                        'entityId' => $event->getId(),
                        'entityType' => 'zlevent',
                        'userId' => $eventUser->getUserId(),
                        'pluginKey' => 'zlevent'
                        ), array(
                        'eventId' => $event->getId(),
                        'userId' => $eventUser->getUserId(),
                        'eventUserId' => $eventUser->getId(),
                        'string' =>  OW::getLanguage()->text('zlevent', 'feed_actiovity_attend_string' ,  array( 'user' => $userEmbed )),
                        'feature' => array()
                    )));
            }
        }
        else
        {
            $respondArray['message'] = OW::getLanguage()->text('zlevent', 'user_status_update_error');
        }

        exit(json_encode($respondArray));
    }

    /**
     * Responder for event invite form
     */
    public function inviteResponder()
    {
        $respondArray = array();

        if ( empty($_POST['eventId']) || empty($_POST['userIdList']) || !OW::getUser()->isAuthenticated() )
        {
            $respondArray['messageType'] = 'error';
            $respondArray['message'] = '_ERROR_';
            echo json_encode($respondArray);
            exit;
        }

        $idList = json_decode($_POST['userIdList']);

        if ( empty($_POST['eventId']) || empty($idList) )
        {
            $respondArray['messageType'] = 'error';
            $respondArray['message'] = '_EMPTY_EVENT_ID_';
            echo json_encode($respondArray);
            exit;
        }

        $event = $this->eventService->findEvent($_POST['eventId']);

        if ( $event->getEndTimeStamp() < time() )
        {
            throw new Redirect404Exception();
        }

        if ( $event === null )
        {
            $respondArray['messageType'] = 'error';
            $respondArray['message'] = '_EMPTY_EVENT_';
            echo json_encode($respondArray);
            exit;
        }

        if ( (int) $event->getUserId() === OW::getUser()->getId() || (int) $event->getWhoCanInvite() === ZLEVENT_BOL_EventService::CAN_INVITE_PARTICIPANT )
        {
            $count = 0;

            $userList = BOL_UserService::getInstance()->findUserListByIdList($idList);

            foreach ( $userList as $user )
            {
                $userId = $user->id;
                $eventInvite = $this->eventService->findEventInvite($event->getId(), $userId);

                if ( $eventInvite === null )
                {
                    $eventInvite = $this->eventService->inviteUser($event->getId(), $userId, OW::getUser()->getId());
                    $eventObj = new OW_Event('zlevent.invite_user', array('userId' => $userId, 'inviterId' => OW::getUser()->getId(), 'eventId' => $event->getId(), 'imageId' => $event->getImage(), 'eventTitle' => $event->getTitle(), 'eventDesc' => $event->getDescription(), 'displayInvitation' => $eventInvite->displayInvitation));
                    OW::getEventManager()->trigger($eventObj);
                    $count++;
                }
            }
        }

        $respondArray['messageType'] = 'info';
        $respondArray['message'] = OW::getLanguage()->text('zlevent', 'users_invite_success_message', array('count' => $count));

        exit(json_encode($respondArray));
    }
}

/**
 * Event attend form
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.zlevent.forms
 * @since 1.0
 */
class ZLAttendForm extends Form
{

    public function __construct( $eventId, $contId )
    {
        parent::__construct('event_attend');
        $this->setAction(OW::getRouter()->urlFor('ZLEVENT_CTRL_Base', 'attendFormResponder'));
        $this->setAjax();
        $hidden = new HiddenField('attend_status');
        $this->addElement($hidden);
        $eventIdField = new HiddenField('eventId');
        $eventIdField->setValue($eventId);
        $this->addElement($eventIdField);
        $this->setAjaxResetOnSuccess(false);
        $this->bindJsFunction(Form::BIND_SUCCESS, "function(data){
            var \$context = $('#" . $contId . "');

            

            if(data.messageType == 'error'){
                OW.error(data.message);
            }
            else{
                $('.current_status span.status', \$context).empty().html(data.currentLabel);
                $('.current_status span.link', \$context).css({display:'inline'});
                $('.attend_buttons .buttons', \$context).fadeOut(500);

                if ( data.eventId != 'undefuned' )
                {
                    OW.loadComponent('ZLEVENT_CMP_EventUsers', {eventId: data.eventId},
                    {
                      onReady: function( html ){
                         $('.userList', \$context).empty().html(html);

                      }
                    });
                }

                $('.userList', \$context).empty().html(data.eventUsersCmp);
                OW.trigger('zlevent_notifications_update', {count:data.newInvCount});
                OW.info(data.message);
            }
        }");
    }
}


/**
 * Add new event form
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.zlevent.forms
 * @since 1.0
 */
class ZLEventAddForm extends Form
{

    const EVENT_NAME = 'zlevent.event_add_form.get_element';

    public function __construct( $name )
    {
        parent::__construct($name);

        $militaryTime = Ow::getConfig()->getValue('base', 'military_time');

        $language = OW::getLanguage();

        $currentYear = date('Y', time());
        
        $title = new TextField('title');
        $title->setRequired();
        $title->setLabel($language->text('zlevent', 'add_form_title_label'));

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'title' ), $title);
        OW::getEventManager()->trigger($event);
        $title = $event->getData();

        $this->addElement($title);

        $startDate = new DateField('start_date');
        $startDate->setMinYear($currentYear);
        $startDate->setMaxYear($currentYear + 5);
        $startDate->setRequired();

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'start_date' ), $startDate);
        OW::getEventManager()->trigger($event);
        $startDate = $event->getData();

        $this->addElement($startDate);

        $startTime = new ZLEventTimeField('start_time');
        $startTime->setMilitaryTime($militaryTime);
        
        if ( !empty($_POST['endDateFlag']) )
        {
            $startTime->setRequired();
        }

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'start_time' ), $startTime);
        OW::getEventManager()->trigger($event);
        $startTime = $event->getData();

        $this->addElement($startTime);

        $endDate = new DateField('end_date');
        $endDate->setMinYear($currentYear);
        $endDate->setMaxYear($currentYear + 5);

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'end_date' ), $endDate);
        OW::getEventManager()->trigger($event);
        $endDate = $event->getData();

        $this->addElement($endDate);

        $endTime = new ZLEventTimeField('end_time');
        $endTime->setMilitaryTime($militaryTime);

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'end_time' ), $endTime);
        OW::getEventManager()->trigger($event);
        $endTime = $event->getData();

        $this->addElement($endTime);

        $location = new TextField('location');
        //$location->setRequired();
        $location->setLabel($language->text('zlevent', 'add_form_location_label'));

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'location' ), $location);
        OW::getEventManager()->trigger($event);
        $location = $event->getData();

        $this->addElement($location);

        $field = new HiddenField('locationinfo');
        $field->addValidator(new ZLEVENT_RequiredLoactionValidator());
        $this->addElement($field);
        
        $whoCanView = new RadioField('who_can_view');
        $whoCanView->setRequired();
        $whoCanView->addOptions(
            array(
                '1' => $language->text('zlevent', 'add_form_who_can_view_option_anybody'),
                '2' => $language->text('zlevent', 'add_form_who_can_view_option_invit_only')
            )
        );
        $whoCanView->setLabel($language->text('zlevent', 'add_form_who_can_view_label'));

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'who_can_view' ), $whoCanView);
        OW::getEventManager()->trigger($event);
        $whoCanView = $event->getData();

        $this->addElement($whoCanView);

        $whoCanInvite = new RadioField('who_can_invite');
        $whoCanInvite->setRequired();
        $whoCanInvite->addOptions(
            array(
                ZLEVENT_BOL_EventService::CAN_INVITE_PARTICIPANT => $language->text('zlevent', 'add_form_who_can_invite_option_participants'),
                ZLEVENT_BOL_EventService::CAN_INVITE_CREATOR => $language->text('zlevent', 'add_form_who_can_invite_option_creator')
            )
        );
        $whoCanInvite->setLabel($language->text('zlevent', 'add_form_who_can_invite_label'));

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'who_can_invite' ), $whoCanInvite);
        OW::getEventManager()->trigger($event);
        $whoCanInvite = $event->getData();

        $this->addElement($whoCanInvite);

        $submit = new Submit('submit');
        $submit->setValue($language->text('zlevent', 'add_form_submit_label'));
        $this->addElement($submit);

        $desc = new WysiwygTextarea('desc');
        $desc->setLabel($language->text('zlevent', 'add_form_desc_label'));
        $desc->setRequired();

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'desc' ), $desc);
        OW::getEventManager()->trigger($event);
        $desc = $event->getData();

        $this->addElement($desc);

        $imageField = new FileField('image');
        $imageField->setLabel($language->text('zlevent', 'add_form_image_label'));
        $this->addElement($imageField);

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'image' ), $imageField);
        OW::getEventManager()->trigger($event);
        $imageField = $event->getData();

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
    }
}

/**
 * Form element: CheckboxField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class ZLEventTimeField extends FormElement
{
    private $militaryTime;

    private $allDay = false;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);
        $this->militaryTime = false;
    }

    public function setMilitaryTime( $militaryTime )
    {
        $this->militaryTime = (bool) $militaryTime;
    }

    public function setValue( $value )
    {
        if ( $value === null )
        {
            $this->value = null;
        }

        $this->allDay = false;
        
        if ( $value === 'all_day' )
        {
            $this->allDay = true;
            $this->value = null;
            return;
        }

        if ( is_array($value) && isset($value['hour']) && isset($value['minute']) )
        {
            $this->value = array_map('intval', $value);
        }

        if ( is_string($value) && strstr($value, ':') )
        {
            $parts = explode(':', $value);
            $this->value['hour'] = (int) $parts[0];
            $this->value['minute'] = (int) $parts[1];
        }
    }

    public function getValue()
    {
        if ( $this->allDay === true )
        {
            return 'all_day';
        }

        return $this->value;
    }

    /**
     *
     * @return string
     */
    public function getElementJs()
    {
        $jsString = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $jsString;
    }

    private function getTimeString( $hour, $minute )
    {
        if ( $this->militaryTime )
        {
            $hour = $hour < 10 ? '0' . $hour : $hour;
            return $hour . ':' . $minute;
        }
        else
        {
            if ( $hour == 12 )
            {
                $dp = 'pm';
            }
            else if ( $hour > 12 )
            {
                $hour = $hour - 12;
                $dp = 'pm';
            }
            else
            {
                $dp = 'am';
            }

            $hour = $hour < 10 ? '0' . $hour : $hour;
            return $hour . ':' . $minute . $dp;
        }
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);
        
        for ( $hour = 0; $hour <= 23; $hour++ )
        {
            $valuesArray[$hour . ':0'] = array('label' => $this->getTimeString($hour, '00'), 'hour' => $hour, 'minute' => 0);
            $valuesArray[$hour . ':30'] = array('label' => $this->getTimeString($hour, '30'), 'hour' => $hour, 'minute' => 30);
        }

        $optionsString = UTIL_HtmlTag::generateTag('option', array('value' => ""), true, OW::getLanguage()->text('zlevent', 'time_field_invitation_label'));

        $allDayAttrs = array( 'value' => "all_day"  );
        
        if ( $this->allDay )
        {
            $allDayAttrs['selected'] = 'selected';
        }
        
        $optionsString = UTIL_HtmlTag::generateTag('option', $allDayAttrs, true, OW::getLanguage()->text('zlevent', 'all_day'));

        foreach ( $valuesArray as $value => $labelArr )
        {
            $attrs = array('value' => $value);

            if ( !empty($this->value) && $this->value['hour'] === $labelArr['hour'] && $this->value['minute'] === $labelArr['minute'] )
            {
                $attrs['selected'] = 'selected';
            }

            $optionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, $labelArr['label']);
        } 

        return UTIL_HtmlTag::generateTag('select', $this->attributes, true, $optionsString);
    }
}

class ZLEVENT_CMP_EventUsersList extends BASE_CMP_Users
{

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate', 'sex');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $q )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($q['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($q['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            if ( !empty($q['sex']) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $q['sex']) . ' ' . $age
                );
            }

            if ( !empty($q['birthdate']) )
            {
                $dinfo = date_parse($q['birthdate']);
            }
        }

        return $fields;
    }
}

class ZLEVENT_RequiredLoactionValidator extends OW_Validator
{
	/**
	 * Constructor.
	 *
	 * @param array $params
	 */
	public function __construct()
	{
		$errorMessage = OW::getLanguage()->text('zlevent', 'errors_location');

		if ( empty($errorMessage) )
		{
			$errorMessage = 'Required Validator Error!';
		}

		$this->setErrorMessage($errorMessage);
	}

	/**
	 * @see OW_Validator::isValid()
	 *
	 * @param mixed $value
	 */
	public function isValid( $value )
	{
		if ( is_array($value) )
		{
			if ( sizeof($value) === 0 )
			{
				return false;
			}
		}
		else if ( $value === null || mb_strlen(trim($value)) === 0 )
		{
			return false;
		}

		return true;
	}

	/**
	 * @see OW_Validator::getJsValidator()
	 *
	 * @return string
	 */
	public function getJsValidator()
	{
		return "{
        	validate : function( value ){
                if(  $.isArray(value) ){ if(value.length == 0  ) throw " . json_encode($this->getError()) . "; return;}
                else if( !value || $.trim(value).length == 0 ){ throw " . json_encode($this->getError()) . "; }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
	}
	

}
