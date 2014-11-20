<?php

class ZLGROUPS_CMP_TagsWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

//         $groupId = $params->additionalParamList['entityId'];
        
//         OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlareas')->getStaticJsUrl() . 'jquery-1.11.1.min.js');
//         OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlareas')->getStaticJsUrl() . 'jquery-ui.min.js');
//         OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlareas')->getStaticJsUrl() . 'tag-it.js');
        
//         OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('zlareas')->getStaticCssUrl() . 'jquery-ui.css' );
//         OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('zlareas')->getStaticCssUrl() . 'jquery.tagit.css' );
//         OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('zlareas')->getStaticCssUrl() . 'tag-it.css' );
        
//         $this->assignList( $groupId );
        
//         // 根据用户是否可以编辑乐群显示标签widget
//         $userId = OW::getUser()->getId();
//         $groupDto = ZLGROUPS_BOL_Service::getInstance()->findGroupById($groupId);
//         $isReadOnly = !ZLGROUPS_BOL_Service::getInstance()->isUserCanEdit($userId, $groupDto);
        
//         $btnEnableScript = $isReadOnly ? ' $("#ZLGROUPS_SaveTags").hide();' : '';
//         $section = $isReadOnly ? 'readOnly : true,' : 'placeholderText : "添加新标签",';
        
//         $js = UTIL_JsGenerator::newInstance();
//         $js->addScript(
// 				'    $(document).ready(function() {
// 				        // 判断是否是可以编辑的
//         		        '.
//         				$btnEnableScript
//         		        .'        
        		
// 				        $("#myTags").tagit({
// 				        	singleField : true,' . $section .
// 				        	//placeholderText : "添加新标签",
// 				        	//readOnly : true,
// 				        	//afterTagAdded: function(event, ui) {
// 						        // do something special
// 						        //updateTags();
// 						    //},
// 						    //afterTagRemoved: function(event, ui) {
// 						        // do something special
// 						        //updateTags();
// 						    //},
// 						    '
// 						    onTagClicked: function(event, ui) {
// 						        // do something special
// 						        // TBD - 显示根据tag得到的
// 						        console.log(ui.tag);
// 						    }
// 				        });
// 				    });
// 				    function updateTags()
// 				    {
// 				    	var alltags = $("input[name=tags]").val();
// 				    	debug(alltags);
// 				    }
				
// 				    function debug(message)
// 				    {
// 				    	var debugging = true;
// 				    	if(debugging==true)
// 				    	{
// 				    		alert(message);
// 				    	}
// 				    }        		
//         		', 
//         		array(
//         		//'isReadOnly' => $isReadOnly
//         ));
//         OW::getDocument()->addOnloadScript($js);


        $params = $paramObj->customParamList;
        $tagParams = new ZLTAGS_CLASS_Params('zlgroups', ZLGROUPS_BOL_Service::ENTITY_TYPE_TAG);
        
        $groupId = (int) $paramObj->additionalParamList['entityId'];
        $tagParams->setEntityId($groupId);
        
        if ( isset($params['display_mode']) )
        {
        	$tagParams->setDisplayType($params['display_mode']);
        }
        
        $isMember = ZLGROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
        $tagParams->setAddTag($isMember);
        
        $this->addComponent('tags', new ZLTAGS_CMP_Tags($tagParams));

    }

    private function assignList( $groupId )
    {

    	 
        $this->assign("isEditable", true);

        
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('zlgroups', 'widget_tags_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => "ow_ic_tag" //self::ICON_INFO
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}