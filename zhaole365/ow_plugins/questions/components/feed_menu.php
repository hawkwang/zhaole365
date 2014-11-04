<?php

class QUESTIONS_CMP_FeedMenu extends OW_Component
{
    private $order;

    public function __construct()
    {
        parent::__construct();

        $this->addComponent('menu', $this->getMenu());

        $this->order = QUESTIONS_BOL_FeedService::getInstance()->getDefaultOrder();
    }

    public function setOrder( $order )
    {
        $this->order = $order;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $contextActionMenu = new BASE_CMP_ContextAction();

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('question_list_order');
        $contextParentAction->setLabel('<span class="ql-sort-btn">' . OW::getLanguage()->text('questions', 'feed_order_' . $this->order) . '</span>');
        $contextActionMenu->addAction($contextParentAction);

        $contextAction = new BASE_ContextAction();
        $contextAction->setParentKey($contextParentAction->getKey());
        $contextAction->setLabel('<span class="ql-sort-order-label">' . OW::getLanguage()->text('questions', 'feed_order_' . QUESTIONS_CMP_Feed::ORDER_LATEST) . '</span>');
        $contextAction->setUrl('javascript://');
        $contextAction->setKey(QUESTIONS_CMP_Feed::ORDER_LATEST);
        $contextAction->setOrder(1);
        $contextAction->addAttribute('qorder', QUESTIONS_CMP_Feed::ORDER_LATEST);

        $class = array('ql-sort-item');
        if ( $this->order == QUESTIONS_CMP_Feed::ORDER_LATEST )
        {
            $class[] = 'ql-sort-item-checked';
        }

        $contextAction->setClass(implode(' ', $class));

        $contextActionMenu->addAction($contextAction);


        $contextAction = new BASE_ContextAction();
        $contextAction->setParentKey($contextParentAction->getKey());
        $contextAction->setLabel('<span class="ql-sort-order-label">' . OW::getLanguage()->text('questions', 'feed_order_' . QUESTIONS_CMP_Feed::ORDER_POPULAR) . '</span>');
        $contextAction->setUrl('javascript://');
        $contextAction->setKey(QUESTIONS_CMP_Feed::ORDER_POPULAR);
        $contextAction->setOrder(2);
        $contextAction->addAttribute('qorder', QUESTIONS_CMP_Feed::ORDER_POPULAR);

        $class = array('ql-sort-item');
        if ( $this->order == QUESTIONS_CMP_Feed::ORDER_POPULAR )
        {
            $class[] = 'ql-sort-item-checked';
        }

        $contextAction->setClass(implode(' ', $class));

        $contextActionMenu->addAction($contextAction);

        $this->addComponent('sortControl', $contextActionMenu);
    }

    public function getMenu()
    {
        $language = OW::getLanguage();

        $menu = new BASE_CMP_ContentMenu();

        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('all');
        $menuItem->setPrefix('questions');
        $menuItem->setLabel( $language->text('questions', 'list_all_tab') );
        $menuItem->setOrder(1);
        $menuItem->setUrl(OW::getRouter()->urlForRoute('questions-all'));
        $menuItem->setIconClass('ow_ic_lens');

        $menu->addElement($menuItem);

        if ( OW::getUser()->isAuthenticated() )
        {
            if ( OW::getPluginManager()->isPluginActive('friends') )
            {
                $menuItem = new BASE_MenuItem();
                $menuItem->setKey('friends');
                $menuItem->setPrefix('questions');
                $menuItem->setLabel( $language->text('questions', 'list_friends_tab') );
                $menuItem->setOrder(2);
                $menuItem->setUrl(OW::getRouter()->urlForRoute('questions-friends'));
                $menuItem->setIconClass('ow_ic_user');

                $menu->addElement($menuItem);
            }

            $menuItem = new BASE_MenuItem();
            $menuItem->setKey('my');
            $menuItem->setPrefix('questions');
            $menuItem->setLabel( $language->text('questions', 'list_my_tab') );
            $menuItem->setOrder(3);
            $menuItem->setUrl(OW::getRouter()->urlForRoute('questions-my'));
            $menuItem->setIconClass('ow_ic_user');

            $menu->addElement($menuItem);
        }

        return $menu;
    }
}