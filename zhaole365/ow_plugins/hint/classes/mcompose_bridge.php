<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.classes
 */
class HINT_CLASS_McomposeBridge
{
    const PLUGIN_URL = "http://www.oxwall.org/store/item/580";
    const PLUGIN_TITLE = "Compose Message";
    
    /**
     * Class instance
     *
     * @var HINT_CLASS_McomposeBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_McomposeBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {

    }

    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive('mcompose');
    }

    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $userId = $params["entityId"];

        if ( !OW::getUser()->isAuthenticated() || $userId == OW::getUser()->getId() )
        {
            return;
        }

        $uniqId = uniqid("hint-mc-");

        $js = UTIL_JsGenerator::newInstance();

        if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId) )
        {
            $js->jQueryEvent("#" . $uniqId, "click",
                'OW.error(e.data.msg);',
            array('e'), array(
                "msg" => OW::getLanguage()->text('base', 'user_block_message')
            ));
        }
        else
        {
            $recipients = array(MCOMPOSE_CLASS_BaseBridge::ID_PREFIX . '_' . $userId);
            $recipientsData = MCOMPOSE_BOL_Service::getInstance()->getDataForIds($recipients);
            
            $js->jQueryEvent("#" . $uniqId, "click", 'HINT.getShown().hide(); OW.trigger("mailbox.open_new_message_form", e.data.data); return false;', array("e"), array(
                "data" => array(
                    "opponentId" => $recipients,
                    "mcompose" => array(
                        "context" => MCOMPOSE_BOL_Service::CONTEXT_USER,
                        "data" => $recipientsData
                    )
                )
            ));
        }

        OW::getDocument()->addOnloadScript($js);

        $button = array(
            "key" => "mcompose",
            //"label" => OW::getLanguage()->text('mailbox', 'create_conversation_button'),
            "label" => OW::getLanguage()->text('hint', 'button_send_message_label'),
            "attrs" => array("id" => $uniqId)
        );

        $event->add($button);
    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text('hint', 'button_send_message_label');

        $button = array(
            "key" => "mcompose",
            "label" => $label,
            "attrs" => array("href" => "javascript://")
        );

        $event->add($button);
    }

    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text('mailbox', 'create_conversation_button');
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "mcompose");

        $mcomposeInstalled = $this->isActive();
        
        $button = array(
            "key" => "mcompose",
            "active" => $active === null ? $mcomposeInstalled : $active,
            "label" => $label
        );
        
        if ( !$mcomposeInstalled )
        {
            $button["requirements"]["short"] = OW::getLanguage()->text("hint", "mcompose_required_short", array(
                "plugin" => '<a href="' . self::PLUGIN_URL . '" target="_blank">' . self::PLUGIN_TITLE . '</a>'
            ));
            
            $button["requirements"]["long"] = OW::getLanguage()->text("hint", "mcompose_required_long", array(
                "plugin" => '<a href="' . self::PLUGIN_URL . '" target="_blank">' . self::PLUGIN_TITLE . '</a>',
                "feature" => $label
            ));
        }

        $event->add($button);
    }

    public function onHintRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
    }

    public function onQuery( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !in_array($params["command"], array()) )
        {
            return;
        }

        $userId = $params["params"]['userId'];

        $info = null;
        $error = null;

        $event->setData(array(
            "info" => $info,
            "error" => $error
        ));
    }

    public function init()
    {
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));
        
        if ( !$this->isActive() )
        {
            return;
        }

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_HINT_RENDER, array($this, 'onHintRender'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_QUERY, array($this, 'onQuery'));
    }
}