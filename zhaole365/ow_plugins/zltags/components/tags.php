<?php


class ZLTAGS_CMP_Tags extends OW_Component
{
	
	protected $params;
	protected $id;
	protected $cmpContextId;
	protected $isAuthorized;
	
	
    /**
     * @return Constructor.
     */
    public function __construct(ZLTAGS_CLASS_Params $params)
    {
        parent::__construct();
        $this->params = $params;
        
        //
        $entityType = $params->getEntityType();
        $entityId = $params->getEntityId();
        $pluginKey = $params->getPluginKey();
        
        srand(time());
        $this->id =  $entityType . $entityId . rand(1, 10000);
        $this->cmpContextId = "tags-$this->id";
        
        $this->isAuthorized = OW::getUser()->isAuthorized($pluginKey, 'add_tag') && $params->getAddTag();
        
        //OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zltags')->getStaticJsUrl() . 'jquery-1.11.1.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zltags')->getStaticJsUrl() . 'jquery-ui.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zltags')->getStaticJsUrl() . 'tag-it.js');
        
        OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('zltags')->getStaticCssUrl() . 'jquery-ui.css' );
        OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('zltags')->getStaticCssUrl() . 'jquery.tagit.css' );
        OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('zltags')->getStaticCssUrl() . 'tag-it.css' );
        
        //
        $addTagUrl = OW::getRouter()->urlFor('ZLTAGS_CTRL_Tags', 'add');
        $deleteTagUrl = OW::getRouter()->urlFor('ZLTAGS_CTRL_Tags', 'delete');
        
        // 根据用户是否可以编辑乐群显示标签widget
        $userId = OW::getUser()->getId();
        
        $isReadOnly = !$this->isAuthorized;
        
        $section = $isReadOnly ? 'readOnly : true,' : 'placeholderText : "添加新标签",';
        
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript(
        		'    $(document).ready(function() {
				        $("#myTags").tagit({
				        	singleField : true,' . $section .
        		'afterTagAdded: function(event, ui) {
        			if($("#isInitializing").val()=="false")
	        			addTag({$pluginKey}, {$entityType}, {$entityId}, ui.tagLabel);
        		},
        		afterTagRemoved: function(event, ui) {
	        		deleteTag({$pluginKey}, {$entityType}, {$entityId}, ui.tagLabel);
        		}, '
        		.
        		'
						    onTagClicked: function(event, ui) {
						        // do something special
						        // TBD - 显示根据tag得到的
						        //addTag({$pluginKey}, {$entityType}, {$entityId}, ui.tagLabel);
						    }
				        });
				    });

        		',
                		array(
                				'pluginKey' => $pluginKey,
                				'entityType' => $entityType,
                				'entityId' => $entityId
                		));
        
        $js->addScript(
        	'
        	    function addTag(pluginKey, entityType, entityId, tagLabel)
        		{
		            $.ajax({
		                type: "POST",
		                url: {$addTagUrl},
		                //data: "pluginKey="+pluginKey+"&entityType="+entityType+"&entityId="+entityId+"&tagLabel="+tagLabel,
		                data: {"pluginKey": pluginKey, "entityType": entityType, "entityId": entityId, "tagLabel": tagLabel },
		                dataType: "json",
		                success : function(data)
		                {
		                    if( data.messageType == "error" )
		                    {
		                        OW.error(data.message);
		                    }
		                    else
		                    {
		                        OW.info(data.message);
		                    }
		                },
        		        error : function( XMLHttpRequest, textStatus, errorThrown ){
                                OW.error(textStatus);
                        }
		            });        		
        		}
        	',
                		array(
                				'addTagUrl' => $addTagUrl
                		)
        );
        
        $js->addScript(
        		'
        	    function deleteTag(pluginKey, entityType, entityId, tagLabel)
        		{
		            $.ajax({
		                type: "POST",
		                url: {$deleteTagUrl},
		                data: {"pluginKey": pluginKey, "entityType": entityType, "entityId": entityId, "tagLabel": tagLabel },
		                dataType: "json",
		                success : function(data)
		                {
		                    if( data.messageType == "error" )
		                    {
		                        OW.error(data.message);
		                    }
		                    else
		                    {
		                        OW.info(data.message);
		                    }
		                },
        		        error : function( XMLHttpRequest, textStatus, errorThrown ){
                                OW.error(textStatus);
                        }
		            });
        		}
        	',
        		array(
        				'deleteTagUrl' => $deleteTagUrl
        		)
        );        

        // 初始化tags
        $entityTags = ZLTAGS_BOL_TagService::getInstance()->findFullTagList($entityType, $entityId);
        foreach ($entityTags as $entityTag)
        {
        	$tag = ZLTAGS_BOL_TagService::getInstance()->findTagById($entityTag->tagId);
        	$tagLabel = $tag->getTag();
        	$js->addScript(
        	'$(document).ready(function() {
        			$("#isInitializing").val("true");
        			$("#myTags").tagit("createTag", {$tagLabel});
           			$("#isInitializing").val("false");
        			
        	});',
        			array(
        					'tagLabel' => $tagLabel
        			)
        	);
        	 
        }
        
        OW::getDocument()->addOnloadScript($js);
        

    }
    

}

