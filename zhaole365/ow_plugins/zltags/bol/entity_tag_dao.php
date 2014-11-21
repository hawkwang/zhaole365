<?php

class ZLTAGS_BOL_EntityTagDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const TAG_ENTITY_ID = 'tagEntityId';
    const TAG_ID = 'tagId';
    const CREATE_STAMP = 'createStamp';

    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        parent::__construct();
    }

    public function getDtoClassName()
    {
        return 'ZLTAGS_BOL_EntityTag';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zltags_entity_tag';
    }

    public function findTagList( $entityType, $entityId, $first, $count )
    {
        $query = "SELECT `et`.* FROM `" . $this->getTableName() . "` AS `et`
			LEFT JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `te` ON ( `et`.`" . self::TAG_ENTITY_ID . "` = `te`.`id` )
			WHERE `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_TYPE . "` = :entityType AND `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_ID . "` = :entityId
			ORDER BY `" . self::CREATE_STAMP . "` DESC
			LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('entityType' => $entityType, 'entityId' => $entityId, 'first' => $first, 'count' => $count));
    }

    public function findFullTagList( $entityType, $entityId )
    {
        $query = "SELECT `et`.* FROM `" . $this->getTableName() . "` AS `et`
			LEFT JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `te` ON ( `et`.`" . self::TAG_ENTITY_ID . "` = `te`.`id` )
			WHERE `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_TYPE . "` = :entityType AND `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_ID . "` = :entityId
 			ORDER BY `" . self::CREATE_STAMP . "`";
        
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('entityType' => $entityType, 'entityId' => $entityId));
    }

    /**
     * Returns tags count for provided entity type and entity id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return integer
     */
    public function findTagCount( $entityType, $entityId )
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` AS `et`
			LEFT JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `te` ON ( `et`.`" . self::TAG_ENTITY_ID . "` = `te`.`id` )
			WHERE `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_TYPE . "` = :entityType AND `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_ID . "` = :entityId
			";

        return (int) $this->dbo->queryForColumn($query, array('entityType' => $entityType, 'entityId' => $entityId));
    }

    public function findMostTagedEntityList( $entityType, $first, $count )
    {
        $query = "SELECT `te`.`entityId` AS `id`, COUNT(*) AS `tagCount` FROM `" . $this->getTableName() . "` AS `et`
			LEFT JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `te` ON ( `et`.`" . self::TAG_ENTITY_ID . "` = `te`.`id` )
			WHERE `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_TYPE . "` = :entityType AND `te`.`" . ZLTAGS_BOL_TagEntityDao::ACTIVE . "` = 1
			GROUP BY `" . ZLTAGS_BOL_TagEntityDao::ENTITY_ID . "`
			ORDER BY `tagCount` DESC
			LIMIT :first, :count";

        return $this->dbo->queryForList($query, array('entityType' => $entityType, 'first' => $first, 'count' => $count));
    }

    public function findTagCountForEntityList( $entityType, $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $query = "SELECT `te`.`entityId` AS `id`, COUNT(*) AS `tagCount` FROM `" . $this->getTableName() . "` AS `et`
			INNER JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `te`
				ON ( `et`.`" . self::TAG_ENTITY_ID . "` = `te`.`id` )
			WHERE `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_TYPE . "` = :entityType AND `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_ID . "` IN  ( " . $this->dbo->mergeInClause($idList) . " )
			GROUP BY `" . ZLTAGS_BOL_TagEntityDao::ENTITY_ID . "`";

        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    public function deleteByTagEntityId( $id )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::TAG_ENTITY_ID, $id);

        $this->deleteByExample($example);
    }
    
    public function deleteByTagEntityIdAndTagId( $tagEntityId, $tagId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual(self::TAG_ENTITY_ID, $tagEntityId);
    	$example->andFieldEqual(self::TAG_ID, $tagId);
    
    	$this->deleteByExample($example);
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }

    public function deleteEntityTypeTags( $entityType )
    {
        $query = "DELETE `et` FROM `" . $this->getTableName() . "` AS `et`
            LEFT JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `e` ON( `et`.`" . self::TAG_ENTITY_ID . "` = `e`.`id` )
            WHERE `e`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_TYPE . "` = :entityType";

        $this->dbo->query($query, array('entityType' => trim($entityType)));
    }

    public function deleteByPluginKey( $pluginKey )
    {
        $query = "DELETE `et` FROM `" . $this->getTableName() . "` AS `et`
            LEFT JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `e` ON( `et`.`" . self::TAG_ENTITY_ID . "` = `e`.`id` )
            WHERE `e`.`" . ZLTAGS_BOL_TagEntityDao::PLUGIN_KEY . "` = :pluginKey";

        $this->dbo->query($query, array('pluginKey' => trim($pluginKey)));
    }

    public function findBatchTagsCount( array $entities )
    {
        $queryStr = '';
        $params = array();
        foreach ( $entities as $entity )
        {
            $queryStr .= " (`te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_TYPE . "` = ? AND `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_ID . "` = ? ) OR";
            $params[] = $entity['entityType'];
            $params[] = $entity['entityId'];
        }
        $queryStr = substr($queryStr, 0, -2);

        $query = "SELECT `te`.`entityType`, `te`.`entityId`, COUNT(*) AS `count` FROM `" . $this->getTableName() . "` AS `et`
			LEFT JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `te` ON ( `et`.`" . self::TAG_ENTITY_ID . "` = `te`.`id` )
			WHERE " . $queryStr . " GROUP BY `te`.`id`";

        return $this->dbo->queryForList($query, $params);
    }

    public function findBatchTagsList( $entities )
    {
        if ( empty($entities) )
        {
            return array();
        }

        $queryParts = array();
        $queryParams = array();
        $genId = 1;
        foreach ( $entities as $entity )
        {
            $queryParts[] = " SELECT * FROM ( SELECT `et`.*, `te`.`entityType`, `te`.`entityId` FROM `" . $this->getTableName() . "` AS `et`
			LEFT JOIN `" . ZLTAGS_BOL_TagEntityDao::getInstance()->getTableName() . "` AS `te` ON ( `et`.`" . self::TAG_ENTITY_ID . "` = `te`.`id` )
			WHERE `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_TYPE . "` = ? AND `te`.`" . ZLTAGS_BOL_TagEntityDao::ENTITY_ID . "` = ?
			ORDER BY `" . self::CREATE_STAMP . "` DESC
			LIMIT 0, ? ) AS `al" . $genId++ . "` ".PHP_EOL;
            $queryParams[] = $entity['entityType'];
            $queryParams[] = $entity['entityId'];
            $queryParams[] = (int)$entity['countOnPage'];
        }

        $query = implode(" UNION ALL ", $queryParts);

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $queryParams);
    }
    
    public function findEntityTagByTagEntityIdAndTagId($tagEntityId, $tagId)
    {
    	if(!isset($tagEntityId) || (int)($tagEntityId) < 1)
    		return null;
    	if(!isset($tagId) || (int)($tagId) < 1)
    		return null;
    	
    	$example = new OW_Example();
    	$example->andFieldEqual('tagEntityId', $tagEntityId);
    	$example->andFieldEqual('tagId', $tagId);
    	return $this->findObjectByExample($example);
    	
    }
        
}
