<?php

class ZLTAGS_CTRL_Tags extends OW_ActionController
{

    private $tagService;


    public function __construct()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $this->tagService = ZLTAGS_BOL_TagService::getInstance();
    }

    public function add()
    {
        $errorMessage = false;
        $params = $this->getParamsObject();

        if ( empty($_POST['tagLabel']) )
        {
            $errorMessage = OW::getLanguage()->text('lztags', 'tag_required_validator_message');
        }
        else if ( !OW::getUser()->isAuthorized($params->getPluginKey(), 'add_tag') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus($params->getPluginKey(), 'add_tag');
            $errorMessage = $status['msg'];
        }
        else if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $params->getOwnerId()) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'user_block_message');
        }

        if ( $errorMessage )
        {
            exit(json_encode(array(
            		'messageType' => 'error',
            		'message' => $errorMessage
            		)
            	)
            );
        }
        
        $tagLabel = empty($_POST['tagLabel']) ? '' : trim($_POST['tagLabel']);
        
        // TBD－检查标签是否含有非法词，如果有，返回提醒
        // 发送zltags_before_add_tag事件，根据获得响应处理后的数据判断是否继续添加输入的标签
        $event = new OW_Event('zltags_before_add_tag', array(
        		'entityType' => $params->getEntityType(),
        		'entityId' => $params->getEntityId(),
        		'userId' => OW::getUser()->getId(),
        		'tagLabel' => $tagLabel,
        		'pluginKey' => $params->getPluginKey(),
        		'createdTime' => time()
        ));
        OW::getEventManager()->trigger($event);
        //判断 FIXME
        $data = $event->getData();
        if( isset($data['invalidMessage']) )
        {
        	exit(json_encode(array(
        			'messageType' => 'error',
        			'message' => $data['invalidMessage']
		        	)
        		)
        	);
        }

        // 添加标签
        $entityTag = $this->tagService->addTag($params->getEntityType(), $params->getEntityId(), $params->getPluginKey(), OW::getUser()->getId(), $tagLabel);

        // trigger event tag add
        $event = new OW_Event('zltags_after_add_tag', array(
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'userId' => OW::getUser()->getId(),
            'entityTagId' => $entityTag->getId(),
        	'tagLabel' => $tagLabel,
            'pluginKey' => $params->getPluginKey()
        ));

        OW::getEventManager()->trigger($event);
        
        BOL_AuthorizationService::getInstance()->trackAction($params->getPluginKey(), 'add_tag');
        
        exit(json_encode(array(
            'messageType' => 'ok',
            'message' => '已成功添加标签－' . $tagLabel
//         	'entityType' => $params->getEntityType(),
//             'entityId' => $params->getEntityId(),
//             'onloadScript' => OW::getDocument()->getOnloadScript()
                )
            )
        );
    }

    public function delete()
    {
    	$params = $this->getParamsObject();
    	
        $tagArray = $this->getTagInfoForDelete();
        $entityTag = $tagArray['entityTag'];
        $tagEntity = $tagArray['tagEntity'];
        
        $tag = $this->tagService->findTagById($entityTag->getTagId());
        $tagLabel = $tag->getTag();
        $userId = $entityTag->getUserId();
        $entityType = $tagEntity->getEntityType();
        $entityId = $tagEntity->getEntityId();
        
        $event = new OW_Event('zltags_before_delete_tag', array(
        		'entityType' => $entityType,
        		'entityId' => $entityId,
        		'userId' => $userId,
        		'tagLabel' => $tagLabel
        ));
        OW::getEventManager()->trigger($event);
        
        $this->tagService->deleteEntityTag($entityTag->getId());
        $tagCount = $this->tagService->findTagCount($tagEntity->getEntityType(), $tagEntity->getEntityId());

        // 如果没有entityTag项，则删除tagEntity项
        if ( $tagCount === 0 )
        {
            $this->tagService->deleteTagEntity($tagEntity->getId());
        }

        $event = new OW_Event('zltags_after_delete_tag', array(
            'entityType' => $entityType,
            'entityId' => $entityId,
            'userId' => $userId,
            'tagLabel' => $tagLabel
        ));
        OW::getEventManager()->trigger($event);

        exit(json_encode(array(
        	'messageType' => 'ok',
        	'message' => '已成功删除标签－' . $tagLabel,
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'onloadScript' => OW::getDocument()->getOnloadScript()
                )
            )
        );
    }



    private function getTagInfoForDelete()
    {
        $params = $this->getParamsObject();
    	
        // 如果entityTagId没有合理的提供
//     	if ( !isset($_POST['entityTagId']) || (int) $_POST['entityTagId'] < 1 )
//         {
        	// 如果tagLabel没有合理的提供
        	if(!isset($_POST['tagLabel']) || strlen($_POST['tagLabel']) < 1)
        	{
	            echo json_encode(array('messageType' => 'error','message' => OW::getLanguage()->text('zltags', 'tag_ajax_error')));
	            exit();
        	}
//         }

        $tag = $this->tagService->findByTag($_POST['tagLabel']);
        $tagId = $tag->getId();
        	
        $tagEntity = $this->tagService->findTagEntity($params->getEntityType(), $params->getEntityId());
        if ( $tagEntity === null )
        {
        	echo json_encode(array('messageType' => 'error','message' => OW::getLanguage()->text('zltags', 'tag_ajax_error')));
        	exit();
        }  
        $tagEntityId =  $tagEntity->getId();  

        $entityTag = $this->tagService->findEntityTagByTagEntityIdAndTagId($tagEntityId, $tagId);
        
        if ( $entityTag === null )
        {
            echo json_encode(array('messageType' => 'error','message' => OW::getLanguage()->text('zltags', 'tag_ajax_error')));
            exit();
        }

        $isModerator = OW::getUser()->isAuthorized($params->getPluginKey());
        $isOwnerAuthorized = (OW::getUser()->isAuthenticated() && $params->getOwnerId() !== null && (int) $params->getOwnerId() === (int) OW::getUser()->getId());
        $tagOwner = ( (int) OW::getUser()->getId() === (int) $entityTag->getUserId() );

        if ( !$isModerator && !$isOwnerAuthorized && !$tagOwner )
        {
            echo json_encode(array('messageType' => 'error','message' => OW::getLanguage()->text('zltags', 'auth_ajax_error')));
            exit();
        }

        return array('entityTag' => $entityTag, 'tagEntity' => $tagEntity);
    }

    private function getParamsObject()
    {
        $errorMessage = false;

        $entityType = !isset($_POST['entityType']) ? null : trim($_POST['entityType']);
        $entityId = !isset($_POST['entityId']) ? null : (int) $_POST['entityId'];
        $pluginKey = !isset($_POST['pluginKey']) ? null : trim($_POST['pluginKey']);

        if ( !$entityType || !$entityId || !$pluginKey )
        {
            $errorMessage = OW::getLanguage()->text('zltags', 'tag_ajax_error');
        }

        $params = new ZLTAGS_CLASS_Params($pluginKey, $entityType);
        $params->setEntityId($entityId);

        if ( isset($_POST['ownerId']) )
        {
            $params->setOwnerId((int) $_POST['ownerId']);
        }

        if ( isset($_POST['displayType']) )
        {
            $params->setDisplayType($_POST['displayType']);
        }

        if ( $errorMessage )
        {
            echo json_encode(array(
            	'messageType' => 'error',
                'message' => $errorMessage
            ));

            exit();
        }

        return $params;
    }
}
