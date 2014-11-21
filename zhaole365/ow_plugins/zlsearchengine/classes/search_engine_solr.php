<?php


class ZLSEARCHENGINE_CLASS_SearchEngineSolr extends ZLSEARCHENGINE_CLASS_SearchBase {
	
	// the solr core url
	protected $_core_url = null;
	
	public function __construct($core_url) {
		parent::__construct ();
		
		$this->_core_url = $core_url;
	}
	
	public function updateDocument($doc)
	{
		$url = $this->_core_url;
		
		$url .= "update/?commit=true";
		
		$header = array("Content-type:text/json; charset=utf-8");
			
		$mydoc = array (
				'add' => array (
						'doc' => $doc 
				) 
		);
		
		$post_string = json_encode ( $mydoc );
		
		$ch = curl_init ();
		
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_string );
		curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		curl_setopt ( $ch, CURLINFO_HEADER_OUT, 1 );
		
		$data = curl_exec ( $ch );
		
		if (curl_errno ( $ch )) {
			print "curl_error:" . curl_error ( $ch );
			return false;
		} else {
			curl_close ( $ch );
			return true;
			//return $url . ' ' . $post_string;
		}
	}
	
	public function deleteDocument($docid) {
		$url = $this->_core_url;
		
		$url .= "update/?commit=true";
		
		$header = array (
				"Content-type:text/json; charset=utf-8" 
		);
		
		// In general, Solr expects your dates to be in the ISO-8601 DateTime format (yyyy-MM-ddTHH:mm:ssZ)
		
		$mydoc = array (
				'delete' => array (
						'id' => $docid 
				) 
		);
		
		$post_string = json_encode ( $mydoc );
		
		$ch = curl_init ();
		
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_string );
		curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		curl_setopt ( $ch, CURLINFO_HEADER_OUT, 1 );
		
		$data = curl_exec ( $ch );
		
		if (curl_errno ( $ch )) {
			print "curl_error:" . curl_error ( $ch );
			return false;
		} else {
			curl_close ( $ch );
			return true;
		}
	}
	
// 		$options = array(
// 			'keyword' => '',
// 			'category' => '足球',
// 			'timehigh' => true, 			// 时间优先，if false则距离优先 
// 			'timerange' => '1WEEK',
// 			'location' => '45.15,-93.85',
// 			'distancerange' => '5',
// 			'start' => 0,
// 			'rows' => 10
// 		);
		
	public function query($options)
	{
		return null;
	}

}