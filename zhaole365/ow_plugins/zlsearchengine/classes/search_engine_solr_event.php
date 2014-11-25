<?php


class ZLSEARCHENGINE_CLASS_SearchEngineSolrEvent extends ZLSEARCHENGINE_CLASS_SearchEngineSolr {
	public function __construct() {
		$solr_events_url = ZLSEARCHENGINE_BOL_Service::getInstance()->getServiceUrl() . 'events/'; 
		//Zend_Registry::get("solr_events_url");
		parent::__construct ($solr_events_url);
	}
        
    // 使用solr进行检索
    public function query ($options)
    {
        // prepare vars
        $result = 1;
        $numFound = 0;
        $start = 0;
        $hasmore = 1;
        $ids = array();
        $rows = $options['rows'];
        
        // 构建检索url头
        $url = $this->_core_url;
        $url .= "select?q=";
        
        // get result
        // 指定搜索条件
        // 限定关键字
        if ((isset($options['keyword']) == false) || ($options['keyword'] == ""))
            $keyword = "*:*";
        else
            $keyword = $options['keyword'];
        $keyword = urlencode($keyword);
        $url .= $keyword;
        // 限定隶属乐群groupid
        // $groupid = $options['groupid'];
        if ((isset($options['groupid']) == true) &&
                 (strlen($options['groupid']) > 0) &&
                 (intval($options['groupid']) >= 0)) {
            $groupid = urlencode($options['groupid']);
            $url .= "&fq=";
            $url .= "groupid:" . $groupid;
        }
        // 限定类型
        // $category = $options['category'];
        if ((isset($options['category']) == true) && ($options['category'] != '0')) {
            $category = urlencode($options['category']);
            $url .= "&fq=";
            $url .= "category:" . $category;
        }
        // 限定type, private event or public event
//         if ((isset($options['type']) == true) &&
//                  (strlen($options['type']) > 0)) {
//             $type = $options['type'];
//             $url .= "&fq=";
//             $url .= "type:" . $type;
//         }
        // 限定时间范围
        $url .= "&fq=";
        $uplimit = "";
        if ((isset($options['timerange']) == false) ||
                 ($options['timerange'] == '0')) {
            $lowlimit = "NOW";
            $uplimit = "*";
        } else {
            if ($options['timerange'] == '-1')             // get old events
            {
                $lowlimit = "*";
                $uplimit = "NOW";
            } else             // get new events
            {
                $lowlimit = "NOW";
                $uplimit = "NOW+" . $options['timerange'] . "DAY";
            }
        }
        $url .= urlencode("happentime:[" . $lowlimit . " TO " . $uplimit . "]");
        // 限定区域
        // $areacode = $options ['areacode'];
        if ((isset($options['areacode']) == true) &&
                 ($options['areacode'] != '0')) {
            $url .= "&fq=";
            $url .= "areacode:" . $options['areacode'];
        }
        // 限定距离
        $hasLocation = true;
        if ((isset($options['location']) == false) || ($options['location'] ==
                 '0'))
            $hasLocation = false;
        
        if ($hasLocation == true) {
            if ((isset($options['distancerange']) == false) ||
                     ($options['distancerange'] == '0'))
                $options['distancerange'] = '100000';
            
            $url .= "&fq={!geofilt}";
            $url .= "&sfield=location";
            $url .= "&pt=";
            $url .= $options['location'];
            $url .= "&d=";
            $url .= $options['distancerange'];
        }
        // 指定结果要求（时间优先）
        // if this query want to get old events
        if ((isset($options['timerange']) == true) &&
                 ($options['timerange'] == '-1')) {
            // 按照时间先后排序
            $url .= "&sort=happentime+desc";
        } else         // if this query want to get old events
        {
            if ($options['sort'] == '0') {
                // 按照时间先后排序
                $url .= "&sort=happentime+asc";
                // 按照距离排序
                if ($hasLocation == true)
                    $url .= "&sort=geodist()+asc";
            } else {
                // 按照距离排序
                if ($hasLocation == true)
                    $url .= "&sort=geodist()+asc";
                    // 按照时间先后排序
                $url .= "&sort=happentime+asc";
            }
        }
        // 限定返回字段
        // $url .= "&fl=_dist_:geodist()";//返回距离
        $url .= "&fl=id";
        // $url .= "&fl=text";
        // 限定起始行
        $url .= "&start=";
        if (isset($options['start']) == false) {
            $options['start'] = '0';
        }
        $url .= $options['start'];
        // 限定返回行数
        $url .= "&rows=";
        if (isset($options['rows']) == false)
            $options['rows'] = '10';
        $url .= $options['rows'];
        // 限定输出格式
        $url .= "&wt=json&indent=true";
        
        // for debugging
        // echo $url;
        
        // Executes the URL and saves the content (json) in the variable.
        try {
            //$content = file_get_contents($url);
            $content = $this->CallAPI($url);
            // for debugging
            // echo $content;
            
            $result_array = json_decode($content, true);
            
            // get numFound
            $response = $result_array['response'];
            $numFound = (int) $response['numFound'];
            
            $start = (int) $response['start'];
            
            if (($start + $rows) < $numFound)
                $hasmore = 1;
            else
                $hasmore = 0;
            
            $docs = $response['docs'];
            foreach ($docs as $doc) {
                $ids[] = (int) $doc['id'];
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 0;
        }
        
        // determine if we has more query elements
        
        // generate result for client
        $search_result = array(
                'queryurlstatement' => $url,
                'result' => $result,
                'numFound' => $numFound,
                'start' => $start,
                'hasmore' => $hasmore,
                'ids' => $ids
        );
        // return search result
        return $search_result;
    }
    
    private function CallAPI ($url)
    {
    	$ch = curl_init();
    
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_HEADER, false);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	$result_data = curl_exec($ch);
    	if (curl_errno($ch)) {
    		print "curl_error:" . curl_error($ch);
    		return "error";
    	} else {
    		curl_close($ch);
    		return $result_data;
    	}
    }
	
	
// 	$options = array (
// 			'order' => 'p.ts_happen desc',
// 			'offset' => $parameters['offset'],
// 			'limit' => $parameters['limit'],
// 			'category' => $parameters['category'],
// 			'key' => $parameters['key'],
// 			'area' => $parameters['area'],
// 			'longitude' => $parameters['longitude'],
// 			'latitude' => $parameters['latitude'],
// 			'radius' => $parameters['radius'],
// 			'timerange' => $parameters['timerange'],
// 			'sort' => $parameters['sort']
// 	);

	public function SearchMe($options)
	{
		// initialize the options
		$defaults = array (
				'sort' => 0, // 0-时间优先，if false则距离优先
				'rows' => 10
		);	
		
		foreach ( $defaults as $k => $v ) {
			$options [$k] = array_key_exists ( $k, $options ) ? $options [$k] : $v;
		}
		

		$result = $this->query($options);
		
		$rows = count($result['ids']);
		$events = null;
		
		if( $rows!=0 )
		{
// 			$opts = array (
// 					'event_id' => $result['ids']
// 			);
			
// 			$events = WX_DatabaseObject_Event::get__events ( $opts );
			
// 			$rows = count($events);

			$event_ids = $result['ids'];
			$events = ZLEVENT_BOL_EventService::getInstance()->findByIdList($event_ids);
		}
		
		$hasmore = $result['hasmore'];
		
		$finalresult = array(
				'queryurlstatement'=>$result['queryurlstatement'],
				'events' => $events,
				'numFound' => $result['numFound'],
				'rows' => $rows,
				'hasmore' => $hasmore
		);
		
		return $finalresult;
		
	}
}
