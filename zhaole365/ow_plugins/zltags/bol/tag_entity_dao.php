<?php

class ZLTAGS_BOL_TagEntityDao extends OW_BaseDao
{
    const ENTITY_TYPE = 'entityType';
    const ENTITY_ID = 'entityId';
    const PLUGIN_KEY = 'pluginKey';
    const ACTIVE = 'active';

    /**
     * Singleton instance.
     *
     * @var BOL_CommentEntityDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_CommentEntityDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'ZLTAGS_BOL_TagEntity';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zltags_tag_entity';
    }


    public function findByEntityTypeAndEntityId( $entityType, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::ENTITY_ID, $entityId);

        return $this->findObjectByExample($example);
    }

    public function findTaggedEntityCount( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        return (int) $this->countByExample($example);
    }

    public function deleteByEntityType( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($example);
    }

    public function deleteByPluginKey( $pluginKey )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::PLUGIN_KEY, trim($pluginKey));

        $this->deleteByExample($example);
    }

    
}
