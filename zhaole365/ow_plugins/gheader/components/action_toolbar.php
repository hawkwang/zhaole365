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
 * @package gheader.components
 */
class GHEADER_CMP_ActionToolbar extends OW_Component
{
    /**
     * Singleton instance.
     *
     * @var GHEADER_CMP_ActionToolbar
     */
    private static $classInstanceList = array();

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GHEADER_CMP_ActionToolbar
     */
    public static function getInstanceForUserId( $userId )
    {
        if ( empty(self::$classInstanceList[$userId]) )
        {
            self::$classInstanceList[$userId] = new self( $userId );
        }

        return self::$classInstanceList[$userId];
    }


    const EVENT_NAME = 'base.add_profile_action_toolbar';
    const DATA_KEY_LABEL = 'label';
    const DATA_KEY_LINK_ID = 'id';
    const DATA_KEY_LINK_CLASS = 'linkClass';
    const DATA_KEY_CMP_CLASS = 'cmpClass';
    const DATA_KEY_LINK_HREF = 'href';

    private $toolbarItems = array();

    private $userId;

    private $toolbarCollected = false;
    private $toolbarPrepared = false;

    /**
     * Constructor.
     */
    public function __construct( $userId )
    {
        parent::__construct();

        $this->userId = (int) $userId;
    }

    public function collectToolbar()
    {
        if ( $this->toolbarCollected )
        {
            return;
        }

        $userId = $this->userId;

        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME, array('userId' => $userId));

        OW::getEventManager()->trigger($event);

        $this->toolbarItems = $event->getData();

        $userService = BOL_UserService::getInstance();
        $language = OW::getLanguage();
        $isAdmin = OW::getUser()->isAuthorized('base');
        $isModerator = BOL_AuthorizationService::getInstance()->isModerator();
        $isApproved = $userService->isApproved($userId);
        $myself = OW::getUser()->getId() == $userId;
        $isSuspended = $userService->isSuspended($userId);
        $isFeatured = $userService->isUserFeatured($userId);
        $isBlocked = $userService->isBlocked($userId);
        $backUrl = OW::getRouter()->getBaseUrl() . OW::getRequest()->getRequestUri();

        if ( $isAdmin )
        {
            if ( !$myself  )
            {
                if ( $isSuspended )
                {
                    $url = OW::getRouter()->urlFor('BASE_CTRL_SuspendedUser', 'unsuspend', array(
                        'id' => $userId
                    ));

                    $url .= '?backUrl=' . $backUrl;

                    $this->toolbarItems[] = array(
                        self::DATA_KEY_LINK_HREF => $url,
                        self::DATA_KEY_LINK_CLASS => 'ow_mild_green',
                        self::DATA_KEY_LABEL => $language->text('base', 'user_unsuspend_btn_lbl')
                    );
                }
                else
                {
                    $url = OW::getRouter()->urlFor('BASE_CTRL_SuspendedUser', 'suspend', array(
                        'id' => $userId
                    ));

                    $url .= '?backUrl=' . $backUrl;

                    $this->toolbarItems[] = array(
                        self::DATA_KEY_LINK_HREF => $url,
                        self::DATA_KEY_LINK_CLASS => 'ow_mild_red',
                        self::DATA_KEY_LABEL => $language->text('base', 'user_suspend_btn_lbl')
                    );
                }
            }

            $this->toolbarItems[] = array(
                self::DATA_KEY_LINK_CLASS => 'ow_mild_green',
                self::DATA_KEY_LABEL => $language->text('base', 'authorization_give_user_role'),
                'extra' => 'data-userid="' . $userId . '" data-fbtitle="' . $language->text('base', 'authorization_user_roles')
                    . '" onclick="window.baseChangeUserRoleFB = OW.ajaxFloatBox(\'BASE_CMP_GiveUserRole\', [$(this).data().userid], { width:556, title: $(this).data().fbtitle });"'
            );

            if ( !$isApproved )
            {
                $url = OW::getRouter()->urlFor('BASE_CTRL_User', 'approve', array(
                    'userId' => $userId
                ));

                $url .= '?backUrl=' . $backUrl;

                $this->toolbarItems[] = array(
                    self::DATA_KEY_LINK_HREF => $url,
                    self::DATA_KEY_LINK_CLASS => 'ow_mild_green',
                    self::DATA_KEY_LABEL => $language->text('base', 'profile_toolbar_user_approve_label')
                );
            }

            if ( $isFeatured )
            {
                $url = OW::getRouter()->urlFor('BASE_CTRL_User', 'controlFeatured', array(
                    'id' => $userId,
                    'command' => 'unmark'
                ));

                $url .= '?backUrl=' . $backUrl;

                $this->toolbarItems[] = array(
                    self::DATA_KEY_LINK_HREF => $url,
                    self::DATA_KEY_LABEL => $language->text('base', 'user_action_unmark_as_featured')
                );
            }
            else
            {
                $url = OW::getRouter()->urlFor('BASE_CTRL_User', 'controlFeatured', array(
                    'id' => $userId,
                    'command' => 'mark'
                ));

                $url .= '?backUrl=' . $backUrl;

                $this->toolbarItems[] = array(
                    self::DATA_KEY_LINK_HREF => $url,
                    self::DATA_KEY_LABEL => $language->text('base', 'user_action_mark_as_featured')
                );
            }

        }

        $this->toolbarCollected = true;
    }

    public function prepareToolbar()
    {
        if ( $this->toolbarPrepared )
        {
            return;
        }

        $this->collectToolbar();

        $visibleCount = 4;

        $cmpsMarkup = '';

        foreach ( $this->toolbarItems as & $item )
        {
            $item['href'] = isset($item['href']) ? $item['href'] : 'javascript://';

            if ( isset($item[self::DATA_KEY_CMP_CLASS]) )
            {
                $reflectionClass = new ReflectionClass(trim($item[self::DATA_KEY_CMP_CLASS]));
                $cmp = call_user_func_array(array($reflectionClass, 'newInstance'), array(array('userId' => $this->userId)));
                $cmpsMarkup .= $cmp->render();
            }
        }

        $visibleItems = array_slice($this->toolbarItems, 0, $visibleCount);
        $tooltipItems = array_slice($this->toolbarItems, $visibleCount);

        $this->assign('visibleItems', $visibleItems);
        $this->assign('tooltipItems', $tooltipItems);

        $this->assign('cmpsMarkup', $cmpsMarkup);

        $this->toolbarPrepared = true;
    }

    public function onBeforeRender()
    {
        $this->prepareToolbar();
    }
}