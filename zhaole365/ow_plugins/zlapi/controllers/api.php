<?php

class ZLAPI_CTRL_Api extends OW_ActionController
{
	public function index() {

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
		
		
		$apiResponse = array (
				"type" => 'yes',  // or 'no'
				"ispost" => $ispost,
				"data" => $data 
		);
		
		// prepare result and format
		header ( 'Content-Type: application/json' );
		
		echo json_encode ( $apiResponse );
		
		exit ();
	}
    
}