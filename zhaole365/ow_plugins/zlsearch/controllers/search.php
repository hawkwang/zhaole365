<?php

class ZLSEARCH_CTRL_Search extends OW_ActionController
{

    public function index()
    {
    	$plugin = OW::getPluginManager()->getPlugin('zlsearch');
    	 
        $this->setPageTitle('找乐365');
        $this->setPageHeading('寻乐群，找乐子');
    	
    	//$this->assign('areas', $areainfos);
        $document = OW::getDocument();
        
        $document->addOnloadScript(';window.searchBar.init();');
        
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'bootstrap-theme.min.css');
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'bootstrap.min.css');
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'search_index.css');
        $document->addScript($plugin->getStaticJsUrl() . 'jquery-1.11.1.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'bootstrap.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'masonry.pkgd.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'imagesloaded.pkgd.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'search_index.js');
        
        $this->assign('staticurl', $plugin->getStaticUrl());
        
        $action = 'noaction';
        if (isset($_REQUEST['action'])) {
        	$action = $_REQUEST['action'];
        }
        $this->assign('action', $action);
        
        $this->assign('remoteaddr', $_SERVER['REMOTE_ADDR']);
        
        $baseurl = OW::getRouter()->urlFor('ZLSEARCH_CTRL_Search', 'ajaxResponder');
        $this->assign('baseurl', $baseurl);
        $createventurl = OW::getRouter()->urlFor('ZLEVENT_CTRL_Base', 'add');
        $this->assign('createeventurl', $createventurl);
        $creategroupurl = OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'create');
        $this->assign('creategroupurl', $creategroupurl);
        
    }

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }
    
    public function ajaxResponder()
    {
    	if ( OW::getRequest()->isAjax() )
    	{
    		if (isset($_REQUEST['parameters'])) {
    		
    			$json = $_REQUEST['parameters'];
    		
    			// we should consider information contains:
    			// 1. type - privateevent, publicevent, group, calendar
    			// 2. offset
    			// 3. limit
    			// 4. category
    			// 5. key
    			// 6. area
    			// 7. position (longitude, latitude)
    			// 8. radius
    			// 9. timerange, indicate the time range [0,timerange]
    		
    			// get all these parameters
    			// parse json string to array
    			$parameters = json_decode($json, true);
    			//$parameters['type'] = '0';
    		
    			switch ($parameters['type']) {
    				case 'group':
    					echo $this->build_groupinfo_content($parameters);
    					break;
    				case 'privateevent':
    					echo $this->build_eventinfo_content($parameters);
    					break;
    			} 
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
    
//     	header('Content-Type: application/json');
//     	exit(json_encode($result));
		exit();
    }
    
    private function build_groupinfo_content($parameters)
    {
    	// use search engine to generate result
    	$searchengine = new ZLSEARCHENGINE_CLASS_SearchEngineSolrGroup();
    	 
    	$options = array(
    			'keyword' => $parameters['key'],
    			'type' => $parameters['type'],
    			'category' => $parameters['category'],
    			'sort' => $parameters['sort'],
    			'timerange' => $parameters['timerange'],
    			'location' => $parameters['latitude'] . ',' .
    			$parameters['longitude'],
    			'areacode' => $parameters['areacode'],
    			'distancerange' => $parameters['radius'],
    			'start' => $parameters['offset'],
    			'rows' => $parameters['limit']
    	);
    	 
    	$result = $searchengine->SearchMe($options);
    	 
    	$groups = $result['items'];
    	$numFound = $result['numFound'];
    	$rows = $result['rows'];
    	$hasmore = $result['hasmore'];
    	 
    	$groupinfos = array();
    	 
    	if ($rows > 0) {
    		foreach ($groups as $group) {
    			$url = empty($group->imageHash) ? false : ZLGROUPS_BOL_Service::getInstance()->getGroupImageUrl($group, ZLGROUPS_BOL_Service::IMAGE_SIZE_BIG);
    			$latest_event = ZLEVENT_BOL_EventService::getInstance()->findLatestEventByGroupId($group->getId());
    			$latest_event_time = '待定';
    			$latest_event_url = '';
    			if ($latest_event != null) {
    				$event_dateinfo = ZLAREAS_CLASS_Utility::getInstance()->getTimeInfo($latest_event->startTimeStamp);
    				$latest_event_time = /*$event_dateinfo['weekday'] . '/' . */
    				$event_dateinfo['date'] . '/' .
    				$event_dateinfo['time'];
    				$latest_event_url = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $latest_event->getId()));
    			}
    		  
    			if (!isset($url) ||  empty($url)) {
    				// 设置缺省图片
    				$url = OW::getPluginManager()->getPlugin('zlsearch')->getStaticUrl() . 'img/group-default.jpg';
    			}
    			
    			if (isset($url) && ! empty($url)) {
//     				$category = $group->get_category();
//     				$separator = ' ';
//     				$output = '';
//     				$categoryUrl = '/event/category/?category=' . $category;
//     				$categoryName = $category;

    				// 获得乐群乐友数
    				$count_members = ZLGROUPS_BOL_Service::getInstance()->findUserListCount($group->getId());
    	
    				/////////////////////////
    				$groupinfo = array(
    						'url' => OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $group->getId())),
    						'logo' => $url,
    						'title' => $group->title,
    						'category' => '待定',
    						'description' => $group->description ,
    						'members' => $count_members,
    						'latesteventurl' => $latest_event_url,
    						'latesteventtime' => $latest_event_time
    				);
    				$groupinfos[] = $groupinfo;
    				 
    			}
    		}
    	} else {
    		// $html .= '<article class="noposts">';
    		// $html .= '<p>还没有这样的乐子？您搜搜其他的吧？你也可以为大家创造一个吧？</p>';
    		// $html .= 'get_search_form()';
    		// $html .= '</article>';
    	}
    		 
    		$json_result = array(
    								'queryurlstatement' => $result['queryurlstatement'],
    								'items' => $groupinfos,
    								'numFound' => $numFound,
    								'rows' => $rows,
    								'hasmore' => $hasmore
    		);
    		 
    		return json_encode($json_result);
    	    
    }

    private function build_eventinfo_content($parameters)
    {
		// use search engine to generate result
		$searchengine = new ZLSEARCHENGINE_CLASS_SearchEngineSolrEvent();
	
		$options = array (
				'keyword' => $parameters['key'],
				'type' => $parameters['type'],
				'category' => $parameters['category'],
				'sort' => $parameters['sort'],
				'timerange' => $parameters['timerange'],
				'location' => $parameters['latitude'] . ',' . $parameters['longitude'],
				'areacode' => $parameters['areacode'],
				'distancerange' => $parameters['radius'],
				'start' => $parameters['offset'],
				'rows' => $parameters['limit']
		);
	
	
		$result = $searchengine->SearchMe($options);
	
		$events = $result['events'];
		$numFound = $result['numFound'];
		$rows = $result['rows'];
		$hasmore = $result['hasmore'];
	
		// get all related events info
		$eventinfos = array(); 
	
		if ( $rows > 0 )
		{
			foreach ( $events as $event )
			{
// 				$url = ( $event->getImage() ? ZLEVENT_BOL_EventService::getInstance()->generateImageUrl($event->getImage(), false) : null );
				$url = $this->getEventImageUrl($event);
	
				if (isset($url)&&!empty($url))
				{
					$separator = ' ';
					$output = '';
					$event_dateinfo = ZLAREAS_CLASS_Utility::getInstance()->getTimeInfo($event->startTimeStamp);
	
					$event_location = $event->location;
					// default value, 北京中心
					$event_lat = 39.904030;
					$event_lng = 116.407516;
					if($event_location != null)
					{
						$detailedLocationInfo = ZLEVENT_BOL_EventService::getInstance()->findLocationDetailedInfoByEventId($event->getId());
						$event_lat = $detailedLocationInfo['latitude'];
						$event_lng = $detailedLocationInfo['longitude'];
					}
	
					/////////////////////////
					//$belongingGroup = ZLEVENT_BOL_EventService::getInstance()->findGroupByEventId($event->getId());
					
					//获得pin用户
					// FIXME
					$pcount = 0;
					
					//获得关注用户
					$eventid = $event->getId();
					$mcount = ZLEVENT_BOL_EventService::getInstance()->findEventUsersCount($eventid, ZLEVENT_BOL_EventService::USER_STATUS_YES);
					
					$eventinfo = array(
					   'eid' => $eventid,
					   'url' => OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $eventid)),
					   'pcount' => $pcount,
					   'mcount' => $mcount,
					   'logo' => $url,
					   'title' => $event->title,
					   'time' => $event_dateinfo['weekday'] . '/' .$event_dateinfo['date'] . '/' . $event_dateinfo['time'],
					   'location' => $event_location,
					   'latitude' => $event_lat,
					   'longitude' => $event_lng
					);
					$eventinfos[] = $eventinfo;
						
				}
			}
		}
		else
		{
			// 			$html .= '<article class="noposts">';
			// 			$html .= '<p>还没有这样的乐子？您搜搜其他的吧？你也可以为大家创造一个吧？</p>';
			// 			$html .= 'get_search_form()';
			// 			$html .= '</article>';
    	}
    	
    	$json_result = array(
    			'queryurlstatement' => $result['queryurlstatement'],
    			'items' => $eventinfos,
    			'numFound' => $numFound,
    			'rows' => $rows,
    			'hasmore' => $hasmore
    	);
    	
    	//return $html;
    	return json_encode( $json_result ) ;
    	
    }
    
    private function getEventImageUrl($event)
    {
    	$url = ( $event->getImage() ? ZLEVENT_BOL_EventService::getInstance()->generateImageUrl($event->getImage(), false) : null );
    	
    	if (!isset($url)||empty($url))
    	{
    		$belongingGroup = ZLEVENT_BOL_EventService::getInstance()->findGroupByEventId($event->getId());
    		$url = $this->getGroupImageUrl($belongingGroup);
    	}
    	
    	return $url;
    }
    
    private function getGroupImageUrl($group)
    {
    	$url = empty($group->imageHash) ? false : ZLGROUPS_BOL_Service::getInstance()->getGroupImageUrl($group, ZLGROUPS_BOL_Service::IMAGE_SIZE_BIG);
    	 
    	if (!isset($url) ||  empty($url)) {
    		// 设置缺省图片
    		$url = OW::getPluginManager()->getPlugin('zlsearch')->getStaticUrl() . 'img/group-default.jpg';
    	}
    	 
    	return $url;
    }
    
    
}