<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package gheader.bol
 */
class GHEADER_BOL_Service
{

    const EVENT_ADD = 'gheader.after_cover_add';
    const EVENT_UPDATE = 'gheader.after_cover_upddate';
    const EVENT_CHANGE = 'gheader.after_cover_change';
    const EVENT_REMOVE = 'gheader.after_cover_remove';
    
    const ENTITY_TYPE = "group";

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return GHEADER_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var GHEADER_BOL_CoverDao
     */
    private $coverDao;

    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    private function __construct()
    {
        $this->coverDao = GHEADER_BOL_CoverDao::getInstance();
        $this->plugin = OW::getPluginManager()->getPlugin('gheader');
    }

    /**
     *
     * @param int $groupId
     * @param type $status
     * @return GHEADER_BOL_Cover
     */
    public function findCoverByGroupId( $groupId, $status = GHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        return $this->coverDao->findByGroupId($groupId, $status);
    }

    /**
     *
     * @param int $id
     * @return GHEADER_BOL_Cover
     */
    public function findCoverById( $id )
    {
        return $this->coverDao->findById($id);
    }

    public function saveCover( GHEADER_BOL_Cover $cover )
    {
        $this->coverDao->save($cover);
    }

    public function deleteCover( GHEADER_BOL_Cover $cover )
    {
        $coverPath = $this->getCoverPath($cover);

        $event = new OW_Event(GHEADER_BOL_Service::EVENT_REMOVE, array(
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

    public function deleteCoverByGroupId( $groupId, $status = GHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $cover = $this->findCoverByGroupId($groupId, $status);

        if ( $cover === null )
        {
            return;
        }

        $this->deleteCover($cover);
    }

    public function getCoverPath( GHEADER_BOL_Cover $cover )
    {
        return $this->plugin->getUserFilesDir() . $cover->file;
    }

    public function getCoverUrl( GHEADER_BOL_Cover $cover )
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


        $_userSettings = GHEADER_CMP_HeaderWidget::getSettingList();
        $userSettings = array();
        foreach ( $_userSettings as $name => $options )
        {
            $userSettings[$name] = $options['value'];
        }

        $settings = GHEADER_CMP_HeaderWidget::getStandardSettingValueList();

        $defaultSettings = BOL_ComponentAdminService::getInstance()->findSettingList('group-GHEADER_CMP_HeaderWidget');
        $customSettings = BOL_ComponentEntityService::getInstance()->findSettingList('group-GHEADER_CMP_HeaderWidget', $groupId);

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
        $groupUser = GROUPS_BOL_Service::getInstance()->findUser($groupId, $userId);

        return $groupUser !== null;
    }
}