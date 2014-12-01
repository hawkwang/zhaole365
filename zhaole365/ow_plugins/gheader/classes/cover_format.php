<?php

class GHEADER_CLASS_CoverFormat extends NEWSFEED_CLASS_Format
{
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $groupId = $this->vars["groupId"];
        
        $cmp = new GHEADER_CMP_CoverItem($groupId);
        $this->addComponent("cover", $cmp);
    }
}