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
class HINT_CLASS_ParseManager
{
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_ParseManager
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
     * @var OW_Plugin
     */
    private $plugin;
    private $parsers = array();

    private function __construct()
    {
        $this->plugin = OW::getPluginManager()->getPlugin('hint');
    }

    public function addParser( HINT_CLASS_Parser $parser )
    {
        $this->parsers[] = $parser;
    }

    /**
     *
     * @param string $url
     * @return HINT_CLASS_Parser
     */
    public function getParser( $url )
    {
        foreach ( $this->parsers as $p )
        {
            if ( $p->test($url) )
            {
                return $p;
            }
        }

        return null;
    }

    private function getMaskList()
    {
        $maskList = array();

        foreach ( $this->parsers as $p )
        {
            $maskList[] = $p->getMask();
        }

        return $maskList;
    }
    
    private function getIgnoreSelectorsList()
    {
        $selectorsList = array(".hint-container a");

        foreach ( $this->parsers as $p )
        {
            $selectorsList = array_merge($selectorsList, $p->getIgnoreSelectors());
        }

        return $selectorsList;
    }

    public function initStatic()
    {
        $staticUrl = $this->plugin->getStaticUrl();
        OW::getDocument()->addStyleSheet($staticUrl . 'style.min.css');
        OW::getDocument()->addScript($staticUrl . 'script.min.js');

        $utils = array();
        $utils['queryRsp'] = OW::getRouter()->urlFor('HINT_CTRL_Hint', 'query');
        $js = UTIL_JsGenerator::newInstance()->callFunction(array('HINT.UTILS', 'init'), array($utils));

        $hint = array();
        $hint['rsp'] = OW::getRouter()->urlFor('HINT_CTRL_Hint', 'rsp');
        $js->callFunction(array('HINT.Launcher', 'init'), array($hint, $this->getMaskList(), $this->getIgnoreSelectorsList()));

        OW::getDocument()->addOnloadScript($js);
    }

    public function beforeRender()
    {
        $hint = new HINT_CMP_Hint();
        OW::getDocument()->prependBody($hint->render());
    }

    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, array($this, 'initStatic'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'beforeRender'));
    }
}