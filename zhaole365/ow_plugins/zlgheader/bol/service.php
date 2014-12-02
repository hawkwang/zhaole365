<?php

class ZLGHEADER_BOL_Service
{

    const EVENT_ADD = 'zlgheader.after_cover_add';
    const EVENT_UPDATE = 'zlgheader.after_cover_upddate';
    const EVENT_CHANGE = 'zlgheader.after_cover_change';
    const EVENT_REMOVE = 'zlgheader.after_cover_remove';
    
    const ENTITY_TYPE = "zlgroup";

    private static $classInstance;

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $coverDao;

    private $plugin;

    private function __construct()
    {
        $this->coverDao = ZLGHEADER_BOL_CoverDao::getInstance();
        $this->plugin = OW::getPluginManager()->getPlugin('zlgheader');
    }

    public function findCoverByGroupId( $groupId, $status = ZLGHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        return $this->coverDao->findByGroupId($groupId, $status);
    }

    public function findCoverById( $id )
    {
        return $this->coverDao->findById($id);
    }

    public function saveCover( ZLGHEADER_BOL_Cover $cover )
    {
        $this->coverDao->save($cover);
    }

    public function deleteCover( ZLGHEADER_BOL_Cover $cover )
    {
        $coverPath = $this->getCoverPath($cover);

        $event = new OW_Event(ZLGHEADER_BOL_Service::EVENT_REMOVE, array(
            'groupId' => $cover->groupId,
            'id' => $cover->id,
            'file' => $cover->file,
            'status' => $cover->status,
            'path' => $coverPath,
            'src' => $this->getCoverUrl($cover),
            'data' => $cover->getSettings()
        ));

        OW::getEventManager()->trigger($event);

        $this->coverDao->deleteById($cover->id);
        unlink($coverPath);
    }

    public function deleteCoverByGroupId( $groupId, $status = ZLGHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $cover = $this->findCoverByGroupId($groupId, $status);

        if ( $cover === null )
        {
            return;
        }

        $this->deleteCover($cover);
    }

    public function getCoverPath( ZLGHEADER_BOL_Cover $cover )
    {
        return $this->plugin->getUserFilesDir() . $cover->file;
    }

    public function getCoverUrl( ZLGHEADER_BOL_Cover $cover )
    {
        return OW::getStorage()->getFileUrl($this->getCoverPath($cover));
    }

    public function getConfigList( $groupId )
    {
        static $configList = array();

        if ( !empty($configList[$groupId]) )
        {
            return $configList[$groupId];
        }


        $_userSettings = ZLGHEADER_CMP_HeaderWidget::getSettingList();
        $userSettings = array();
        foreach ( $_userSettings as $name => $options )
        {
            $userSettings[$name] = $options['value'];
        }

        $settings = ZLGHEADER_CMP_HeaderWidget::getStandardSettingValueList();

        $defaultSettings = BOL_ComponentAdminService::getInstance()->findSettingList('zlgroup-ZLGHEADER_CMP_HeaderWidget');
        $customSettings = BOL_ComponentEntityService::getInstance()->findSettingList('zlgroup-ZLGHEADER_CMP_HeaderWidget', $groupId);

        $configList = array_merge($settings, $userSettings, $defaultSettings, $customSettings);

        return $configList;
    }

    public function getConfig( $groupId, $name )
    {
        $configs = $this->getConfigList($groupId);

        return isset($configs[$name]) ? $configs[$name] : null;
    }

    public function isUserCanInteract( $userId, $groupId )
    {
        $groupUser = ZLGROUPS_BOL_Service::getInstance()->findUser($groupId, $userId);

        return $groupUser !== null;
    }
}