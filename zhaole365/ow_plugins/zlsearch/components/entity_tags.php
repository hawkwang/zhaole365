<?php

class ZLSEARCH_CMP_EntityTags extends OW_Component
{
    public function __construct()
    {
        parent::__construct();
        $contexId = UTIL_HtmlTag::generateAutoId('cmp');
        $this->assign('contexId', $contexId);
        
        $tagsWithCount = ZLTAGS_BOL_TagService::getInstance()->findTagsWithCount();
        $this->assign('tagsWithCount', $tagsWithCount);
        
        $document = OW::getDocument();
        $document->addScriptDeclarationBeforeIncludes(
        		';window.tagsWithCount = ' . json_encode($tagsWithCount) . ';'
        );
        
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlsearch')->getStaticJsUrl() . 'entity_tags.js');
        OW::getDocument()->addOnloadScript("
            var cmp = new EntityTagSelect(" .  "'" . $contexId . "');
            cmp.init();  ");
        
        $this->assign('submitLabel', OW::getLanguage()->text('zlsearch', 'tags_select_submit_btn_label'));
        
    }

}