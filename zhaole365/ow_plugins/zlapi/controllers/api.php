<?php

class ZLAPI_CTRL_Api extends OW_ActionController
{
	public function index() {

		$result = 'no';
		$ispost = OW::getRequest()->isPost();
		
		
		$data = "";
		if($ispost)
		{
			$json = file_get_contents('php://input');
			$values = json_decode($json, true);
			
			//print_r($values);
			$data = $values;
			
			// TBD - do somethong with data
			
		}
		else 
		{
			// source - groupId
			$source = 3;
			// title
			$title = "梦工厂《驯龙高手》超视景LIVE秀+互动体验园";
			// description
			$description = "梦工厂《驯龙高手》超视景LIVE秀+互动体验园 2014.06.26 19:30 国家体育场鸟巢热身场";
			// category
			$category = "亲子";
			// address
			$address = "国家体育场鸟巢热身场";
			// address_description
			$address_description = "北京市朝阳区慧忠路隧道";
			// province
			$province = "北京市";
			// city
			$city = "北京市";
			// area
			$area = "朝阳区";
			// areacode
			$areacode = "110105";
			// longitude
			$longitude = "116.40283570399";
			// latitude
			$latitude = "39.999380542577";
			// date
			$date = "2014.11.26";
			// time
			$time = "19:30";
			// price
			$price = "80";
			// fixnum
			$fixnum = "1000";
			// imageurl
			$imageurl = "http://img.piaochong.com/admin/2014/05/05/de18e1baece35b98_180_253.jpg";
			
			// get start and end time
			$strDateTime = $date . ' ' . $time;
			// $format = 'm-d-Y H:i:s';
			$format = 'Y.m.d H:i'; // the format is from the crawler
			$datetime = DateTime::createFromFormat($format, $strDateTime);
			$startStamp = $datetime->getTimestamp();
			$endStamp = strtotime("+1 day", $startStamp);
			$endStamp = mktime(0, 0, 0, date('n',$endStamp), date('j',$endStamp), date('Y',$endStamp));

			// create pure event
			$event = new ZLEVENT_BOL_Event();
			$event->setStartTimeStamp($startStamp);
			$event->setEndTimeStamp($endStamp);
			$event->setCreateTimeStamp(time());
			$event->setTitle(htmlspecialchars($title));
			$event->setLocation(UTIL_HtmlTag::autoLink(strip_tags($address)));
			$event->setWhoCanView((int) ZLEVENT_BOL_EventDao::VALUE_WHO_CAN_VIEW_ANYBODY);
			$event->setWhoCanInvite((int) ZLEVENT_BOL_EventDao::VALUE_WHO_CAN_INVITE_CREATOR);
			$event->setDescription($description);
			$event->setUserId(2); // hawkwang - wanghao_buaa@yahoo.com
			$event->setEndDateFlag( false );
			$event->setStartTimeDisable( false );
			$event->setEndTimeDisable( true );
			
			// deal with imageurl
			$imagePosted = false;
			if(isset($imageurl) && strlen($imageurl))
				$imagePosted = true;
			
			if($imagePosted)
				$event->setImage(uniqid());
			
			ZLEVENT_BOL_EventService::getInstance()->saveEvent($event);
			
			// 创建关联地址
			$location = $address;
			$address_details = ZLAREAS_CLASS_Utility::getInstance()->getAnotherAddressInfo($address_description, $province, $city, $area, $longitude, $latitude);
			ZLEVENT_BOL_EventService::getInstance()->saveLocation(
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
			ZLEVENT_BOL_EventService::getInstance()->saveEventGroup($event->id, $source);
			
			// 创建关联LOGO图片
			if ($imagePosted)
				ZLEVENT_BOL_EventService::getInstance()->saveEventImageFromUrl($imageurl, $event->getImage());
			
			// 将群乐创建者参与状态设为“参与”（yes）
			$eventUser = new ZLEVENT_BOL_EventUser();
			$eventUser->setEventId($event->getId());
			$eventUser->setUserId(2);
			$eventUser->setTimeStamp(time());
			$eventUser->setStatus(ZLEVENT_BOL_EventService::USER_STATUS_YES);
			ZLEVENT_BOL_EventService::getInstance()->saveEventUser($eventUser);
			
			// 发送自动生成标签的请求
			$serviceEvent = new OW_Event('zltagautogenerator_create_tags', array('title' => $title, 'description' => $description));
			OW::getEventManager()->trigger($serviceEvent);
			$data = $serviceEvent->getData();
			$tags = array();
			$tags[] = $category;
			if(isset($data['tags']))
				$tags = array_merge($tags, $data['tags']);
			// create tag for event
			foreach($tags as $tag_label)
				ZLTAGS_BOL_TagService::getInstance()->addTag('zlevent_tag', $event->getId(), 'zlevent', $event->getUserId(), $tag_label);
			
			// fire event
			$serviceEvent = new OW_Event('zlevent_after_create_event', array('eventId' => $event->id, 'eventDto' => $event));
			OW::getEventManager()->trigger($serviceEvent);
				
			// 
			$result = 'yes';
		}
		
		$apiResponse = array (
				"result" => $result,  // 'yes' or 'no'
				"ispost" => $ispost,
				"data" => $data 
		);
		
		// prepare result and format
		header ( 'Content-Type: application/json' );
		
		echo json_encode ( $apiResponse );
		
		exit ();
	}
    
}