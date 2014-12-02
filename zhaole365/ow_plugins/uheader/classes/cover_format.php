<?php

class UHEADER_CLASS_CoverFormat extends NEWSFEED_CLASS_Format
{
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $coverId = $this->vars["coverId"];
        $cover = UHEADER_BOL_Service::getInstance()->findCoverById($coverId);

		if ( empty($cover) ) {
			$this->assign("cover", "");
			
			return;	
		}

        $cmp = new UHEADER_CMP_CoverItem($cover->userId);
        $this->addComponent("cover", $cmp);
    }
}