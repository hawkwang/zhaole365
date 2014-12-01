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
 * @package gheader.classes
 */
class GHEADER_CLASS_PrivacyBridge
{

    const PRIVACY_ACTION = 'view-cover';

    /**
     * Class instance
     *
     * @var GHEADER_CLASS_PrivacyBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return GHEADER_CLASS_PrivacyBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $isPluginActive = false;

    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    public function __construct()
    {
        $this->isPluginActive = OW::getPluginManager()->isPluginActive('privacy');
        $this->plugin = OW::getPluginManager()->getPlugin('gheader');
    }

    public function onCollectList( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => self::PRIVACY_ACTION,
            'pluginKey' => $this->plugin->getKey(),
            'label' => $language->text($this->plugin->getKey(), 'privacy_action_view_cover'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }

    public function checkPrivacy( $userId )
    {
        $eventParams = array(
            'action' => self::PRIVACY_ACTION,
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return false;
        }

        return true;
    }

    public function init()
    {
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'onCollectList'));
    }
}