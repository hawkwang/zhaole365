<?php

class ZLSEARCHENGINE_CLASS_SearchEngineSolrGroup extends ZLSEARCHENGINE_CLASS_SearchEngineSolr {
	public function __construct() {
		$solr_groups_url = ZLSEARCHENGINE_BOL_Service::getInstance()->getServiceUrl() . 'groups/'; 
		parent::__construct ($solr_groups_url);
	}
	
	// 使用solr进行检索
	public function query($options)
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
		$keyword = $options['keyword'];
		if($keyword=="")
			$keyword = "*:*";
		$keyword = urlencode($keyword);
		$url .= $keyword;
		// 限定类型
		$category = $options['category'];
		if($category!='0')
		{
			$category = urlencode($category);
			$url .= "&fq=";
			$url .= "category:" . $category;
		}
		// 限定type, 公开还是私有
		if (strlen ( $options ['type'] ) > 0) {
			$type = $options['type'];
			$url .= "&fq=";
			$url .= "type:" . $type;
		}
		// 限定时间范围
		$url .= "&fq=";
		$downlimit = "";
		if($options['timerange']=='0')
			$downlimit = "*";
		else
			$downlimit = "NOW-" . $options['timerange'] . "DAY";
		$url .= urlencode("ts_created:[" . $downlimit . " TO NOW]");
		// 限定区域
		$areacode = $options ['areacode'];
		if( $areacode != '0' )
		{
			$url .= "&fq=";
			$url .= "areacode:" . $areacode;
		}
		// 限定距离
		if($options['distancerange']=='0')
			$options['distancerange'] = '100000';
			
		$url .= "&fq={!geofilt}";
		$url .= "&sfield=location";
		$url .= "&pt=";
		$url .= $options['location'];
		$url .= "&d=";
		$url .= $options['distancerange'];
	
		// 指定结果要求（时间优先）
		if($options['sort']=='0')
		{
			// 按照时间先后排序
			$url .= "&sort=ts_created+asc";
			// 按照距离排序
			$url .= "&sort=geodist()+asc";
		}
		else
		{
			// 按照距离排序
			$url .= "&sort=geodist()+asc";
			// 按照时间先后排序
			$url .= "&sort=ts_created+asc";
		}
		// 限定返回字段
		//$url .= "&fl=_dist_:geodist()";//返回距离
		$url .= "&fl=id";
		//$url .= "&fl=text";
		// 限定起始行
		$url .= "&start=";
		$url .= $options['start'];
		//限定返回行数
		$url .= "&rows=";
		$url .= $options['rows'];
		// 限定输出格式
		$url .= "&wt=json&indent=true";
	
		// for debugging
		//echo $url;
	
		//Executes the URL and saves the content (json) in the variable.
		try {
			$content = file_get_contents($url);
			// for debugging
			//echo $content;
	
			$result_array = json_decode($content,true);
	
			// get numFound
			$response = $result_array['response'];
			$numFound = (int) $response['numFound'];
	
			$start = (int) $response['start'];
	
			if(($start+$rows)<$numFound)
				$hasmore = 1;
			else
				$hasmore = 0;
	
			$docs = $response['docs'];
			foreach ($docs as $doc)
			{
				$ids[] = (int) $doc['id'];
			}
	
		}
		catch (Exception $ex)
		{
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
	
// 	public function SearchMe($options)
// 	{
// 	    $this->logger->info('preparing group searching and search ...');
	    
// 		// initialize the options
// 		$defaults = array (
// 				'sort' => 0, // 0-时间优先，if false则距离优先
// 				'rows' => 10
// 		);
	
// 		foreach ( $defaults as $k => $v ) {
// 			$options [$k] = array_key_exists ( $k, $options ) ? $options [$k] : $v;
// 		}
	
	
// 		$result = $this->query($options);
		
//         $this->logger->info('finish group searching ...');
	
// 		$rows = count($result['ids']);
// 		$groups = null;
	
// 		if( $rows!=0 )
// 		{
// 			$opts = array (
// 					'group_id' => $result['ids']
// 			);
				
// 			// for debugging
// 			//print_r($opts);
				
// 			$groups = WX_DatabaseObject_Group::get__groups ( $opts );
				
// 			$rows = count($groups);
// 		}
	
// 		$hasmore = $result['hasmore'];
	
// 		$finalresult = array(
// 				'queryurlstatement'=>$result['queryurlstatement'],
// 				'items' => $groups,
// 				'numFound' => $result['numFound'],
// 				'rows' => $rows,
// 				'hasmore' => $hasmore
// 		);
		
// 	    $this->logger->info('returning basic searching result ...');
		
		
// 		return $finalresult;
	
// 	}
	
	

}

?>