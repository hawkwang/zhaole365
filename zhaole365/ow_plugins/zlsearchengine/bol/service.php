<?php

class ZLSEARCHENGINE_BOL_Service
{
    private static $classInstance;
    
    private $searchengine_url = 'http://localhost:8983/solr/';

    private function __construct()
    {
    	$searchengine_url = Ow::getConfig()->getValue('zlsearchengine', 'searchengine_url');
    	
    	if ( !empty($searchengine_url) )
    	{
    		$this->searchengine_url = $searchengine_url;
    	}
    }
    
    public static function getInstance()
    {
    	if ( !isset(self::$classInstance) )
    		self::$classInstance = new self();
    
    	return self::$classInstance;
    }
    
    public function getServiceUrl()
    {
    	return $this->searchengine_url;
    }
    
    public function convert2UTC($timestamp) {
    
    	// 		$datetime = new DateTime ();
    	// 		$datetime->setTimestamp ( $timestamp ); //$this->ts_created
    
    	$utc_str = gmdate('Y-m-d\TH:i:s\Z', $timestamp);
    	return $utc_str;
    }
    
    public function get_happen_time_string($timestamp, $format = 'Y.m.d H:i') {
    	$datetime = new DateTime ();
    	$datetime->setTimestamp ( $timestamp );
    	$strDateTime = $datetime->format ( $format );
    	return $strDateTime;
    }
    
    public function getIndexableGroupDocument($groupId)
    {
    	//
    	$groupService = ZLGROUPS_BOL_Service::getInstance();
    	$group = $groupService->findGroupById($groupId);
    	
    	// location
    	$detailedLocationInfo = ZLGROUPS_BOL_Service::getInstance()->findLocationDetailedInfoByGroupId($groupId);
    	$locationpoint = '' . $detailedLocationInfo['latitude'] . ',' . $detailedLocationInfo['longitude'];
    	$memberIds = ZLGROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId);
    	//$total_members = count($memberIds);
    	 
    	// wrap information into the document the search engine needs
    	$doc = array (
    			'id' => $groupId,
    			'userid' => $group->userId,
    			'title' => $group->title,
    			'description' => $group->description,
    			'type' => 1,
    			'category' => ZLTAGS_BOL_TagService::getInstance()->findAllTags('zlgroups_tag',$groupId),
    			'ts_created' => $this->convert2UTC($group->timeStamp),
    			'location' => $locationpoint,
    			'location_description' => array (
    					$detailedLocationInfo['location'],
    					$detailedLocationInfo['formated_address']
    			),
    			'areacode' => $detailedLocationInfo['areacode'],
    			'imageurl' => ZLGROUPS_BOL_Service::getInstance()->getGroupImageWithDefaultUrl($group),
    			'member_userid'=> $memberIds
    			);
    	
    	
    	return $doc;    	
    }
    
    public function getIndexableEventDocument($eventId)
    {
    	// event
    	$eventService = ZLEVENT_BOL_EventService::getInstance();
    	$event = $eventService->findEvent($eventId);
    	
    	// group
    	$group = $eventService->findGroupByEventId($eventId);
    	 
    	// location
    	$detailedLocationInfo = $eventService->findLocationDetailedInfoByEventId($eventId);
    	$locationpoint = '' . $detailedLocationInfo['latitude'] . ',' . $detailedLocationInfo['longitude'];
    	
    	// tags array
    	$tags = ZLTAGS_BOL_TagService::getInstance()->findAllTags('zlevent_tag',$eventId);
    	
    	// originurl 
    	$originurl = ZLBASE_BOL_Service::getInstance()->findProperty('zlevent', $eventId, 'originurl');
    	$url = '';
    	if ($originurl)
    		$url = $originurl->value;
    	
    	// imageurl
    	$imageurl = ZLEVENT_BOL_EventService::getInstance()->getEventImageWithDefaultUrl($event);
    	
    	$doc = array (
    			'url' => $url,
    			'id' => $event->id,
    			'userid' => $event->userId,
    			'groupid' => $group->id,
    			'title' => $event->title,
    			'description' => $event->description,
    			'imageurl' => $imageurl,
    			'type' => '1',
    			'category' => $tags,
    			'happentime' => $this->convert2UTC($event->startTimeStamp),      // all the localtime must be converted to UTC time first
    			'eventdate' => $this->get_happen_time_string($event->startTimeStamp, 'Y.m.d'),
    			'eventtime' => $this->get_happen_time_string($event->startTimeStamp, 'H:i'),
    			'location' => $locationpoint,
    			'location_description' => array (
    					$detailedLocationInfo['location'],
    					$detailedLocationInfo['formated_address']
       			),
    			'areacode' => $detailedLocationInfo['areacode'],
    			'RSVP_userid' => ZLEVENT_BOL_EventService::getInstance()->findAllEventUserIds($eventId, ZLEVENT_BOL_EventUserDao::VALUE_STATUS_YES)
    			//'price' => 10.0
    			);
    	
  		return $doc;
    }
    
    public function addToGroupIndex($entityId)
    {
    	try {
    		$group_engine = new ZLSEARCHENGINE_CLASS_SearchEngineSolrGroup();
    		// prepare document
    		$doc = $this->getIndexableGroupDocument($entityId);
    		// update index
    		$result = $group_engine->updateDocument($doc);
    			
    		return true;
    	}
    	catch (Exception $ex) {
    		return false;
    	}
    }
    
    public function addToEventIndex($entityId)
    {
    	try {
    		$event_engine = new ZLSEARCHENGINE_CLASS_SearchEngineSolrEvent();
    		// prepare document
    		$doc = $this->getIndexableEventDocument($entityId);
    		// update index
    		$result = $event_engine->updateDocument($doc);
    		 
    		return true;
    	}
    	catch (Exception $ex) {
    		return false;
    	}
    }
    
    public function deleteGroupIndex($entityId)
    {
    	try {
    		$searchengine = new ZLSEARCHENGINE_CLASS_SearchEngineSolrGroup();
    		$searchengine->deleteDocument($entityId);
    		return true;
    	}
    	catch (Exception $ex) {
			return false;
     	}
    }
    
    public function deleteEventIndex($entityId)
    {
    	try {
    		$searchengine = new ZLSEARCHENGINE_CLASS_SearchEngineSolrEvent();
    		$searchengine->deleteDocument($entityId);
    		return true;
    	}
    	catch (Exception $ex) {
    		return false;
    	}
    }
    
    
    
}