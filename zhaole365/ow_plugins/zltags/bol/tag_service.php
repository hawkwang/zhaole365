<?php

class ZLTAGS_BOL_TagService
{
    const CONFIG_TAGS_ON_PAGE = 'tags_on_page';
    const CONFIG_ALLOWED_TAGS = 'allowed_tags';
    const CONFIG_ALLOWED_ATTRS = 'allowed_attrs';
    const CONFIG_MB_TAGS_ON_PAGE = 'mb_tags_on_page';
    const CONFIG_MB_TAGS_COUNT_TO_LOAD = 'mb_tags_count_to_load';
    
    // 事件
    // zltags_before_add_tag
    // zltags_after_add_tag
    // zltags_before_delete_tag
    // zltags_after_delete_tag

    private $tagDao;
    private $entityTagDao;
    private $tagEntityDao;

    private $configs;

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->tagDao = ZLTAGS_BOL_TagDao::getInstance();
    	$this->entityTagDao = ZLTAGS_BOL_EntityTagDao::getInstance();
        $this->tagEntityDao = ZLTAGS_BOL_TagEntityDao::getInstance();

        $this->configs[self::CONFIG_TAGS_ON_PAGE] = 10;
        $this->configs[self::CONFIG_MB_TAGS_ON_PAGE] = 3;
        $this->configs[self::CONFIG_MB_TAGS_COUNT_TO_LOAD] = 10;
    }

    public function getConfigValue( $name )
    {
        if ( array_key_exists($name, $this->configs) )
        {
            return $this->configs[$name];
        }
        return null;
    }

    /**
     * Returns tags list for entity item.
     *
     * @param string $entityType
     * @param integer $entityId
     * @param integer $page
     * @return array
     */
    public function findTagList( $entityType, $entityId, $page = null, $count = null )
    {
        $page = ( $page === null ) ? 1 : (int) $page;
        $count = ( (int) $count === 0 ) ? $this->configs[self::CONFIG_TAGS_ON_PAGE] : (int) $count;
        $first = ( $page - 1 ) * $count;

        return $this->entityTagDao->findTagList($entityType, $entityId, $first, $count);
    }

    /**
     * Returns full tags list for entity item.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return array
     */
    public function findFullTagList( $entityType, $entityId )
    {
        return $this->entityTagDao->findFullTagList($entityType, $entityId);
    }

    /**
     * Returns tags count for entity item.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findTagCount( $entityType, $entityId )
    {
        return (int) $this->entityTagDao->findTagCount($entityType, $entityId);
    }

    /**
     * Returns entity item tag pages count.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return integer
     */
    public function findTagPageCount( $entityType, $entityId, $count = null )
    {
        $count = ( (int) $count === 0 ) ? $this->configs[self::CONFIG_TAGS_ON_PAGE] : (int) $count;
        $tagCount = $this->findTagCount($entityType, $entityId);

        if ( $tagCount === 0 )
        {
            return 1;
        }

        return ( ( $tagCount - ( $tagCount % $count ) ) / $count ) + ( ( $tagCount % $count > 0 ) ? 1 : 0 );
    }

    /**
     * Returns enityTag item.
     *
     * @param integer $id
     * @return ZLTAGS_BOL_EntityTag
     */
    public function findEntityTag( $id )
    {
        return $this->entityTagDao->findById($id);
    }

    public function findTagEntityById( $id )
    {
        return $this->tagEntityDao->findById($id);
    }

    /**
     * @return ZLTAGS_BOL_EntityTag
     */
    public function addTag( $entityType, $entityId, $pluginKey, $userId, $tag_label )
    {
        $tagEntity = $this->tagEntityDao->findByEntityTypeAndEntityId($entityType, $entityId);

        if ( $tagEntity === null )
        {
            $tagEntity = new ZLTAGS_BOL_TagEntity();
            $tagEntity->setEntityType(trim($entityType));
            $tagEntity->setEntityId((int) $entityId);
            $tagEntity->setPluginKey($pluginKey);

            $this->tagEntityDao->save($tagEntity);
        }

        //$tag_label = UTIL_HtmlTag::stripTags($tag_label, $this->configs[self::CONFIG_ALLOWED_TAGS], $this->configs[self::CONFIG_ALLOWED_ATTRS]);
        //$tag_label = UTIL_HtmlTag::stripJs($tag_label);
        //$tag_label = UTIL_HtmlTag::stripTags($tag_label, array('frame', 'style'), array(), true);
        if ( !isset($tag_label) || strlen($tag_label) == 0 )
        {
            return;
        }
        else
        {
            $tag_label = UTIL_HtmlTag::autoLink(nl2br(htmlspecialchars($tag_label)));
        }

        // create tag if not exist
        $tag = $this->tagDao->findByTag($tag_label);
        if($tag==null)
        {
        	$tag = new ZLTAGS_BOL_Tag();
        	$tag->setUserId($userId);
        	$tag->setTag(trim($tag_label));
        	$tag->setCreateStamp(time());
        	
        	$this->tagDao->save($tag);
        }
        	
        // create entity tag
        $entityTag = new ZLTAGS_BOL_EntityTag();
        $entityTag->setTagEntityId($tagEntity->getId());
        $entityTag->setTagId($tag->getId());
        $entityTag->setUserId($userId);
        $entityTag->setCreateStamp(time());

        $this->entityTagDao->save($entityTag);

        return $tag;
    }

    public function updateTag( ZLTAGS_BOL_EntityTag $tag )
    {
        $this->entityTagDao->save($tag);
    }

    /**
     * Deletes tag item.
     *
     * @param integer $id
     */
    public function deleteEntityTag( $id )
    {
        $this->entityTagDao->deleteById($id);
    }

    public function deleteTagEntity( $id )
    {
        $this->tagEntityDao->deleteById($id);
    }

    /**
     * Deletes entity tags.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityTags( $entityType, $entityId )
    {
        $tagEntity = $this->tagEntityDao->findByEntityTypeAndEntityId($entityType, $entityId);

        if ( $tagEntity === null )
        {
            return;
        }

        $this->entityTagDao->deleteByTagEntityId($tagEntity->getId());
        $this->tagEntityDao->delete($tagEntity);
    }

    /**
     * @param string $entityType
     * @param integer $entityId
     * @param boolean $status
     */
    public function setEntityStatus( $entityType, $entityId, $status = true )
    {
        $tagEntity = $this->tagEntityDao->findByEntityTypeAndEntityId($entityType, $entityId);

        if ( $tagEntity === null )
        {
            return;
        }

        $tagEntity->setActive(($status ? 1 : 0));
        $this->tagEntityDao->save($tagEntity);
    }

    /**
     * @param integer $entityType
     * @param array $idList
     * @return array
     */
    public function findTagCountForEntityList( $entityType, array $idList )
    {
        $tagCountArray = $this->entityTagDao->findTagCountForEntityList($entityType, $idList);

        $tagCountAssocArray = array();

        $resultArray = array();

        foreach ( $tagCountArray as $value )
        {
            $tagCountAssocArray[$value['id']] = $value['tagCount'];
        }

        foreach ( $idList as $value )
        {
            $resultArray[$value] = ( array_key_exists($value, $tagCountAssocArray) ) ? $tagCountAssocArray [$value] : 0;
        }

        return $resultArray;
    }

    /**
     * Finds most taged entities.
     *
     * @param string $entityType
     * @param integer $first
     * @param integer $count
     * @return array<ZLTAGS_BOL_TagEntity>
     */
    public function findMostTagedEntityList( $entityType, $first, $count )
    {
        $resultArray = $this->entityTagDao->findMostTagedEntityList($entityType, $first, $count);

        $resultList = array();

        foreach ( $resultArray as $item )
        {
            $resultList[$item['id']] = $item;
        }

        return $resultList;
    }

    /**
     * Finds tags count for entity type.
     *
     * @param string $entityType
     * @return integer
     */
    public function findTagedEntityCount( $entityType )
    {
        return $this->tagEntityDao->findTagedEntityCount($entityType);
    }

    /**
     * Deletes all user tags.
     *
     * @param integer $userId
     */
    public function deleteUserTags( $userId )
    {
        $this->entityTagDao->deleteByUserId($userId);
    }

    /**
     * Deletes tags for provided entity type.
     *
     * @param string $entityType
     */
    public function deleteEntityTypeTags( $entityType )
    {
        $entityType = trim($entityType);
        $this->entityTagDao->deleteEntityTypeTags($entityType);
        $this->tagEntityDao->deleteByEntityType($entityType);
    }

    /**
     * Deletes all plugin entities tags.
     *
     * @param string $pluginKey
     */
    public function deletePluginTags( $pluginKey )
    {
        $pluginKey = trim($pluginKey);
        $this->entityTagDao->deleteByPluginKey($pluginKey);
        $this->tagEntityDao->deleteByPluginKey($pluginKey);
    }

    /**
     * Finds tag entity object for provided entity type and id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return ZLTAGS_BOL_TagEntity
     */
    public function findTagEntity( $entityType, $entityId )
    {
        return $this->tagEntityDao->findByEntityTypeAndEntityId($entityType, $entityId);
    }

    public function findBatchTagsData( array $items )
    {
        if ( empty($items) )
        {
            return array();
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            $currentUserInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));
        }

        $resultArray = array('_static' => array());
        $creditsParams = array();

        foreach ( $items as $item )
        {
            if ( !isset($resultArray[$item['entityType']]) )
            {
                $resultArray[$item['entityType']] = array();
            }

            $resultArray[$item['entityType']][$item['entityId']] = array('tagsCount' => 0, 'countOnPage' => $item['countOnPage'], 'tagsList' => array());
            $creditsParams[$item['pluginKey']] = array('add_tag');
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            $userInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));
        }

        // get tags count
        $result = $this->entityTagDao->findBatchTagsCount($items);
        $entitiesForList = array();

        foreach ( $result as $item )
        {
            $resultArray[$item['entityType']][$item['entityId']]['tagsCount'] = (int) $item['count'];
            $entitiesForList[] = array('entityType' => $item['entityType'], 'entityId' => $item['entityId'], 'countOnPage' => $resultArray[$item['entityType']][$item['entityId']]['countOnPage']);
        }

        // get tags list
        $result = $this->entityTagDao->findBatchTagsList($entitiesForList);

        $batchUserIdList = array();
        foreach ( $result as $item )
        {
            $resultArray[$item->entityType][$item->entityId]['tagsList'][] = $item;
            $batchUserIdList[] = $item->getUserId();
        }

        $resultArray['_static']['avatars'] = BOL_AvatarService::getInstance()->getDataForUserAvatars(array_unique($batchUserIdList));

        if ( OW::getUser()->isAuthenticated() )
        {
            $resultArray['_static']['currentUserInfo'] = $currentUserInfo[OW::getUser()->getId()];
        }

        $eventParams = array('actionList' => $creditsParams);
        $resultArray['_static']['credits'] = OW::getEventManager()->call('usercredits.batch_check_balance_for_action_list', $eventParams);

        return $resultArray;
    }
    
    // added by hawk to deal with tag
    public function deleteEnityTagByTag( $tagEntityId, $tag )
    {
    	$tag = $this->tagDao->findByTag($tag);
    	if($tag != null)
    	{
    		$tagId = $tag->getTagId();
    		$this->entityTagDao->deleteByTagEntityIdAndTagId($tagEntityId, $tagId);
    	}
    }
    
    public function findTagById($id)
    {
    	return $this->tagDao->findById($id);
    }
    
    public function findByTag($tag)
    {
    	return $this->tagDao->findByTag($tag);
    }
    
    public function findEntityTagByTagEntityIdAndTagId($tagEntityId, $tagId)
    {
    	return $this->entityTagDao->findEntityTagByTagEntityIdAndTagId($tagEntityId, $tagId);
    }
    
    public function findAllTags($entityType, $entityId)
    {
    	$entityTags = $this->findFullTagList($entityType, $entityId );
    	$tags = array();
    	foreach($entityTags as $entityTag)
    	{
    		$tag = $this->tagDao->findById($entityTag->tagId);
    		$tags[] = $tag->tag;
    	}	
    	return $tags;
    }
    
    public function findTagsWithCount()
    {
    	return $this->entityTagDao->findTagsWithCount();
    }
    
}
