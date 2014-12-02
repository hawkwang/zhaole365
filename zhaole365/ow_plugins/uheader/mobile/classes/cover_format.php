<?php

class UHEADER_MCLASS_CoverFormat extends NEWSFEED_CLASS_MobileFormat
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $coverId = $this->vars["coverId"];

        $cover = UHEADER_BOL_Service::getInstance()->findCoverById($coverId);
        
        if ( $cover === null )
        {
            $this->setVisible(false);
            
            return;
        }
        
        UHEADER_CLASS_Plugin::getInstance()->includeStaticFile("uheader.css");
        
        $src = UHEADER_BOL_Service::getInstance()->getCoverUrl($cover);
        $this->assign('src', $src);

        $this->assign('imageCss', $cover->getCssString());
        $this->assign("scale", $cover->getRatio());
        
        $this->assign("url", BOL_UserService::getInstance()->getUserUrl($cover->userId));
    }
}