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
        
        // get tags with count
//         $tagsWithCount = ZLTAGS_BOL_TagService::getInstance()->findTagsWithCount();
//         $this->assign('tagsWithCount', $tagsWithCount);
        
//         $document->addScriptDeclarationBeforeIncludes(
//         		';window.tagsWithCount = ' . json_encode($tagsWithCount) . ';'
//         );
        
        $document->addOnloadScript(';window.searchBar.init();');
        
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'bootstrap-theme.min.css');
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'bootstrap.min.css');
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'search_index.css');
        //$document->addScript($plugin->getStaticJsUrl() . 'jquery-1.11.1.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'bootstrap.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'masonry.pkgd.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'imagesloaded.pkgd.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'search_index.js');
        
        $params = array();
        OW::getDocument()->addOnloadScript("
                var eventFloatBox;
                $('#simple-tag').click(
                    function(){
                        eventFloatBox = OW.ajaxFloatBox('ZLSEARCH_CMP_EntityTags', " . json_encode($params) . ", {width:600, iconClass: 'ow_ic_user', title: " . json_encode(OW::getLanguage()->text('zlsearch', 'tags_select_button_label')) . "});
                    }
                );
                OW.bind('zlsearch.entity_tag_list_select',
                    function(list){
                        eventFloatBox.close();
                        // do something with list
                    }
                );
            ");
        
        $this->assign('staticurl', $plugin->getStaticUrl());
        
        //
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
        
        // for baidu share
        $current_url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        $this->assign('current_url', $current_url);
        $logoiconurl = OW::getPluginManager()->getPlugin('zlbase')->getStaticUrl() . 'img/le-32.ico';
        $this->assign('logoiconurl', $logoiconurl);
        
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
    		
    			$cacheMgr = OW::getCacheManager();
    			$key = md5($json);
    			$result = $cacheMgr->load($key);
    			if($result!=null)
    			{
    				echo $result;
    				exit();
    			}

    			 
    			// get all these parameters
    			// parse json string to array
    			$parameters = json_decode($json, true);
    			//$parameters['type'] = '0';
    		
    			switch ($parameters['type']) {
    				case 'group':
    					$result = $this->build_groupinfo_content($parameters);
    					break;
    				case 'event':
    					$result =  $this->build_eventinfo_content($parameters);
    					break;
    			} 
    			
    			$cacheMgr->save($result, $key);
    			echo $result;
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
    			//$url = empty($group->imageHash) ? false : ZLGROUPS_BOL_Service::getInstance()->getGroupImageUrl($group, ZLGROUPS_BOL_Service::IMAGE_SIZE_BIG);
    			$url = ZLGROUPS_BOL_Service::getInstance()->getGroupImageWithDefaultUrl($group);
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
    		  
//     			if (!isset($url) ||  empty($url)) {
//     				// 设置缺省图片
//     				$url = OW::getPluginManager()->getPlugin('zlsearch')->getStaticUrl() . 'img/group-default.jpg';
//     			}
    			
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
    						'latesteventtime' => $latest_event_time,
    						'joinurl' => OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'join', array(
    								'groupId' => $group->getId()
    						))
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
// 				$url = $this->getEventImageUrl($event);
				$url = ZLEVENT_BOL_EventService::getInstance()->getEventImageWithDefaultUrl($event);
	
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
					
					// 获得第一标签
					$entityType = 'zlevent_tag';
					$entityId = $eventid;
					$tags = ZLTAGS_BOL_TagService::getInstance()->findAllTags($entityType, $entityId);
					$category = '';
					if(count($tags))
						$category = $tags[0];
					
					$eventinfo = array(
					   'eid' => $eventid,
					   'category' => $category,
					   'url' => OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $eventid)),
					   'pcount' => $pcount,
					   'mcount' => $mcount,
					   'logo' => $url,
					   'title' => $event->title,
					   'description' => $event->description,
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
    
//     private function getEventImageUrl($event)
//     {
//     	$url = ( $event->getImage() ? ZLEVENT_BOL_EventService::getInstance()->generateImageUrl($event->getImage(), false) : null );
    	
//     	if (!isset($url)||empty($url))
//     	{
//     		$belongingGroup = ZLEVENT_BOL_EventService::getInstance()->findGroupByEventId($event->getId());
//     		$url = ZLGROUPS_BOL_Service::getInstance()->getGroupImageWithDefaultUrl($belongingGroup);
//     	}
    	
//     	return $url;
//     }
    
    
    
}