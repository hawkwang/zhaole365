<?php

class UHEADER_MCMP_ProfileHeader extends BASE_MCMP_ProfileHeader
{
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $service = UHEADER_BOL_Service::getInstance();
        
        $cover = $service->findCoverByUserId($this->user->id, UHEADER_BOL_Cover::STATUS_ACTIVE);
        
        if ( $cover === null )
        {
            $removedCover = $service->findCoverByUserId($this->user->id, UHEADER_BOL_Cover::STATUS_REMOVED);
            if ( $removedCover === null )
            {
                $template = $service->findDefaultTemplateForUser($this->user->id);
                
                if ( $template !== null )
                {
                    $cover = $template->createCover($this->user->id);
                }
            }
        }
        
        if ( $cover === null )
        {
            return;
        }
        
        UHEADER_CLASS_Plugin::getInstance()->includeStaticFile("uheader.css");
        
        $tplCover = array();
        $tplCover["src"] = UHEADER_BOL_Service::getInstance()->getCoverUrl($cover);

        $tplCover['imageCss'] = $cover->getCssString();
        $tplCover["scale"] = $cover->getRatio();
        
        $this->assign("cover", $tplCover);
    }
}