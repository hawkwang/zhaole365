<?php

class QUESTIONS_Plugin
{
    const PLUGIN_KEY = 'questions';
    const PLUGIN_VERSION = 490;

    const PRIVACY_ACTION_VIEW_MY_QUESTIONS = 'view_my_questions';

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return QUESTIONS_Plugin
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    private $staticAdded = false;

    public function addStatic( $ajax = false )
    {
        if ( $this->staticAdded )
        {
            return;
        }

        $staticUrl = OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getStaticUrl();
        $scriptUrl = $staticUrl . 'script.min.js' . '?' . self::PLUGIN_VERSION;
        $styleUrl = $staticUrl . 'style.min.css' . '?' . self::PLUGIN_VERSION;

        $imagesUrl = OW::getThemeManager()->getThemeImagesUrl();
        $css = 'html body div .q_ic_preloader { background-image: url(' . $imagesUrl . 'ajax_preloader_button.gif) };';

        OW::getDocument()->addStyleDeclaration($css);

        if ( !$ajax )
        {
            OW::getDocument()->addScript($scriptUrl);
            OW::getDocument()->addStyleSheet($styleUrl);
        }
        else
        {
            OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString('
                if ( !window.QUESTIONS_Loaded )
                {

                    OW.addScriptFiles([{$scriptUrl}], function(){
                        if ( window.EQAjaxLoadCallbacksRun )
                        {
                            window.EQAjaxLoadCallbacksRun();
                        }
                    });
                    OW.addCssFile({$styleUrl});

                 }
            ', array(
                'styleUrl' => $styleUrl,
                'scriptUrl' => $scriptUrl
            )));
        }

        $friendMode = (bool) OW::getEventManager()->call('plugin.friends');

        $js = UTIL_JsGenerator::newInstance();

        $js->setVariable(array('QUESTIONS', 'friendMode'), $friendMode);

        if ( !$ajax )
        {
            OW::getDocument()->addOnloadScript($js);
        }
        else
        {
            OW::getDocument()->addOnloadScript('window.EQAjaxLoadCallbackQueue = [];');

            OW::getDocument()->addOnloadScript('(function() {
                var loaded = function() {
                    ' . $js->generateJs() . '
                };

                if ( window.QUESTIONS_Loaded )
                    loaded.call();
                else
                    window.EQAjaxLoadCallbackQueue.push(loaded);
            })();');
        }

        OW::getLanguage()->addKeyForJs('questions', 'followers_fb_title');

        OW::getLanguage()->addKeyForJs('questions', 'toolbar_unfollow_btn');
        OW::getLanguage()->addKeyForJs('questions', 'toolbar_follow_btn');

        $this->staticAdded = true;
    }

    public function isReady()
    {
        $installed = OW::getConfig()->getValue('questions', 'plugin_installed');

        return $installed || !OW::getPluginManager()->isPluginActive('equestions');
    }

    public function init()
    {
        if ( $this->isReady() )
        {
            $this->fullInit();
        }
        else
        {
            $this->shortInit();
        }
    }

    private function shortInit()
    {

    }

    private function fullInit()
    {

        OW::getRouter()->addRoute(new OW_Route('questions-index', 'questions', 'QUESTIONS_CTRL_List', 'all'));
        OW::getRouter()->addRoute(new OW_Route('questions-all', 'questions', 'QUESTIONS_CTRL_List', 'all'));
        OW::getRouter()->addRoute(new OW_Route('questions-my', 'questions/my', 'QUESTIONS_CTRL_List', 'my'));
        OW::getRouter()->addRoute(new OW_Route('questions-friends', 'questions/friends', 'QUESTIONS_CTRL_List', 'friends'));
        OW::getRouter()->addRoute(new OW_Route('questions-admin-main', 'admin/plugins/questions', 'QUESTIONS_CTRL_Admin', 'main'));

        OW::getRouter()->addRoute(new OW_Route('questions-question', 'questions/:qid', 'QUESTIONS_CTRL_Questions', 'question'));
        OW::getRouter()->addRoute(new OW_Route('questions-upgrade', 'admin/plugins/questions/extended-version', 'QUESTIONS_CTRL_Upgrade', 'index'));


        OW::getEventManager()->bind('admin.add_admin_notification', array($this, 'onSetupAdminNotification'));

        $newsfeedBridge = QUESTIONS_CLASS_NewsfeedBridge::getInstance();

        OW::getEventManager()->bind('feed.get_status_update_cmp', array($newsfeedBridge, 'onStatusCmp'));
        OW::getEventManager()->bind('feed.on_item_render', array($newsfeedBridge, 'onItemRender'));
        OW::getEventManager()->bind('feed.on_entity_add', array($newsfeedBridge, 'onEntityAdd'));
        OW::getEventManager()->bind('feed.on_activity', array($newsfeedBridge, 'onActivity'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_ANSWER_ADDED, array($newsfeedBridge, 'onAnswerAdd'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_ANSWER_REMOVED, array($newsfeedBridge, 'onAnswerRemove'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_FOLLOW_ADDED, array($newsfeedBridge, 'onFollowAdd'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_FOLLOW_REMOVED, array($newsfeedBridge, 'onFollowRemove'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_QUESTION_REMOVED, array($newsfeedBridge, 'onQuestionRemove'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_POST_ADDED, array($newsfeedBridge, 'onPostAdd'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_POST_REMOVED, array($newsfeedBridge, 'onPostRemove'));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($newsfeedBridge, 'configurableActivity'));

        $activityBridge = QUESTIONS_CLASS_ActivityBridge::getInstance();

        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_QUESTION_ADDED, array($activityBridge, 'onQuestionAdd'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_QUESTION_REMOVED, array($activityBridge, 'onQuestionRemove'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_ANSWER_ADDED, array($activityBridge, 'onAnswerAdd'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_ANSWER_REMOVED, array($activityBridge, 'onAnswerRemove'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_FOLLOW_ADDED, array($activityBridge, 'onFollowAdd'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_FOLLOW_REMOVED, array($activityBridge, 'onFollowRemove'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_POST_ADDED, array($activityBridge, 'onPostAdd'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_POST_REMOVED, array($activityBridge, 'onPostRemove'));

        $commentBridge = QUESTIONS_CLASS_CommentsBridge::getInstance();

        OW::getEventManager()->bind('base_add_comment', array($commentBridge, 'onCommentAdd'));
        OW::getEventManager()->bind('base_delete_comment', array($commentBridge, 'onCommentRemove'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_QUESTION_REMOVED, array($commentBridge, 'onQuestionRemove'));

        $groupsBridge = QUESTIONS_CLASS_GroupsBridge::getInstance();
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_BEFORE_QUESTION_ADDED, array($groupsBridge, 'onBeforeQuestionAdd'));
        OW::getEventManager()->bind(QUESTIONS_BOL_Service::EVENT_ON_INTERACT_PERMISSION_CHECK, array($groupsBridge, 'onCheckInteractPermission'));

        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'onAuthLabelsCollect'));

        QUESTIONS_CLASS_EnotificationBridge::getInstance()->init();

        //Privacy
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'collectPrivacyActions'));
        OW::getEventManager()->bind('feed.collect_privacy', array($newsfeedBridge, 'collectPrivacy'));
        OW::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($this, 'onPrivacyChange'));
        
        QUESTIONS_CLASS_BaseBridge::getInstance()->init();
    }

    public function activate()
    {
        if ( $this->isReady() )
        {
            $this->fullActivate();
        }
        else
        {
            $this->shortActivate();
        }
    }

    private function fullActivate()
    {
        $navigation = OW::getNavigation();

        $navigation->addMenuItem(
            OW_Navigation::MAIN,
            'questions-index',
            'questions',
            'main_menu_list',
            OW_Navigation::VISIBLE_FOR_ALL);

        $widgetService = BOL_ComponentAdminService::getInstance();
        $widget = $widgetService->addWidget('QUESTIONS_CMP_IndexWidget', false);
        $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
    }

    private function shortActivate()
    {

    }

    public function deactivate()
    {
        OW::getNavigation()->deleteMenuItem('questions', 'main_menu_list');

        $widgetService = BOL_ComponentAdminService::getInstance();
        $widgetService->deleteWidget('QUESTIONS_CMP_IndexWidget');
    }


    public function install()
    {
        OW::getConfig()->addConfig('questions', 'plugin_installed', '0');

        if ( $this->isReady() )
        {
            $this->startInstall();
            $this->completeInstall();
        }
        else
        {
            $this->startInstall();
        }
    }

    public function startInstall()
    {
        $plugin = OW::getPluginManager()->getPlugin(self::PLUGIN_KEY);

        $sql = array();

        $sql[] = 'CREATE TABLE `' . OW_DB_PREFIX . 'questions_question` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(11) NOT NULL,
            `text` text NOT NULL,
            `settings` text NOT NULL,
            `timeStamp` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `userId` (`userId`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;';

        $sql[] = 'CREATE TABLE `' . OW_DB_PREFIX . 'questions_option` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(11) NOT NULL,
            `questionId` int(11) NOT NULL,
            `text` text CHARACTER SET utf8 NOT NULL,
            `timeStamp` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `questionId` (`questionId`,`timeStamp`)
        ) ENGINE = MYISAM CHARSET=utf8 ;';

        $sql[] = 'CREATE TABLE `' . OW_DB_PREFIX . 'questions_answer` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `userId` INT NOT NULL ,
            `optionId` INT NOT NULL ,
            `timeStamp` INT NOT NULL ,
            INDEX ( `optionId` , `timeStamp` )
        ) ENGINE = MYISAM CHARSET=utf8 ;';

        $sql[] = 'CREATE TABLE `' . OW_DB_PREFIX . 'questions_follow` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(11) NOT NULL,
            `questionId` int(11) NOT NULL,
            `timeStamp` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `userId` (`userId`,`questionId`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'questions_activity` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `questionId` int(11) NOT NULL,
            `activityType` varchar(100) CHARACTER SET utf8 NOT NULL,
            `activityId` int(11) NOT NULL,
            `userId` int(11) NOT NULL,
            `timeStamp` int(11) NOT NULL,
            `privacy` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT "everybody",
            `data` text CHARACTER SET utf8,
            PRIMARY KEY (`id`),
            UNIQUE KEY `activityUniq` (`questionId`,`activityType`,`activityId`),
            KEY `userId` (`userId`),
            KEY `timeStamp` (`timeStamp`),
            KEY `questionId` (`questionId`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;';


        foreach ( $sql as $q )
        {
            OW::getDbo()->query($q);
        }

        OW::getConfig()->addConfig('questions', 'allow_comments', '1');
        OW::getConfig()->addConfig('questions', 'enable_follow', '1');
        OW::getConfig()->addConfig('questions', 'list_order', 'latest');
        OW::getConfig()->addConfig('questions', 'allow_popups', '1');

        OW::getConfig()->addConfig('questions', 'ev_page_visited', 0);

        BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', 'questions');
    }

    public function completeInstall()
    {
        $authorization = OW::getAuthorization();
        $groupName = self::PLUGIN_KEY;
        $authorization->addGroup($groupName);

        $authorization->addAction($groupName, 'add_comment');
        $authorization->addAction($groupName, 'ask');
        $authorization->addAction($groupName, 'answer');
        $authorization->addAction($groupName, 'add_answer');

        OW::getPluginManager()->addPluginSettingsRouteName('questions', 'questions-admin-main');

        OW::getConfig()->saveConfig('questions', 'plugin_installed', '1');
    }

    //Callbacks

    public function onSetupAdminNotification( BASE_CLASS_EventCollector $e )
    {
        if ( !OW::getConfig()->configExists('questions', 'ev_page_visited') )
        {
            return;
        }

        if ( OW::getConfig()->getValue('questions', 'ev_page_visited') || OW::getPluginManager()->isPluginActive('equestions') )
        {
            return;
        }

        $language = OW::getLanguage();

        $e->add($language->text(self::PLUGIN_KEY, 'admin_setup_extended_version_notification', array(
            'href' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('questions-upgrade'), array('skip' => 1))
        )));
    }

    public function onAuthLabelsCollect( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'questions' => array(
                    'label' => $language->text('questions', 'auth_group_label'),
                    'actions' => array(
                        'add_comment' => $language->text('questions', 'auth_add_comment'),
                        'ask' => $language->text('questions', 'auth_ask'),
                        'answer' => $language->text('questions', 'auth_answer'),
                        'add_answer' => $language->text('questions', 'auth_add_answer'),
                        'delete_comment_by_content_owner' => $language->text('questions', 'auth_answer_delete_comment')
                    )
                )
            )
        );
    }

    public function collectPrivacyActions( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => self::PRIVACY_ACTION_VIEW_MY_QUESTIONS,
            'pluginKey' => self::PLUGIN_KEY,
            'label' => $language->text(self::PLUGIN_KEY, 'privacy_action_view_my_questions'),
            'description' => '',
            'defaultValue' => QUESTIONS_BOL_FeedService::PRIVACY_EVERYBODY
        );

        $event->add($action);
    }

    public function onPrivacyChange( OW_Event $e )
    {
        $params = $e->getParams();

        $userId = (int) $params['userId'];
        $actionList = $params['actionList'];
        $actionList = is_array($actionList) ? $actionList : array();

        if ( empty($actionList[self::PRIVACY_ACTION_VIEW_MY_QUESTIONS]) )
        {
            return;
        }

        QUESTIONS_BOL_FeedService::getInstance()->setPrivacy($userId, $actionList[self::PRIVACY_ACTION_VIEW_MY_QUESTIONS]);
    }
}