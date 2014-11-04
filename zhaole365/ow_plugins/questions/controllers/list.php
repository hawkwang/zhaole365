<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package questions.controllers
 */
class QUESTIONS_CTRL_List extends OW_ActionController
{
    const ITEMS_COUNT = 10;

    /**
     *
     * @var QUESTIONS_BOL_Service
     */
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = QUESTIONS_BOL_Service::getInstance();
    }

    private function getMenu()
    {
        $menu = new QUESTIONS_CMP_FeedMenu();

        return $menu;
    }

    public function all()
    {
        $language = OW::getLanguage();

        OW::getDocument()->setTitle($language->text('questions', 'list_all_page_title'));
        OW::getDocument()->setDescription($language->text('questions', 'list_all_page_description'));
        OW::getDocument()->setHeading($language->text('questions', 'list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_lens');

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'questions', 'main_menu_list');

        $userId = OW::getUser()->getId();

        $cmp = new QUESTIONS_CMP_MainFeed(time(), $userId, self::ITEMS_COUNT);
        $cmp->setFeedType(QUESTIONS_CMP_Feed::FEED_ALL);
        $order = QUESTIONS_BOL_FeedService::getInstance()->getOrder(QUESTIONS_CMP_Feed::FEED_ALL, OW::getUser()->getId());
        $cmp->setOrder($order);

        $menu = $this->getMenu();
        $menu->setOrder($order);
        $this->addComponent('list', $cmp);
        $this->addComponent('menu', $menu);

        if ( QUESTIONS_BOL_Service::getInstance()->isCurrentUserCanAsk() )
        {
            $add = new QUESTIONS_CMP_QuestionAdd();
            $this->addComponent('add', $add);
        }
    }

    public function my()
    {
        $language = OW::getLanguage();

        OW::getDocument()->setTitle($language->text('questions', 'list_my_page_title'));
        OW::getDocument()->setHeading($language->text('questions', 'list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_lens');

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'questions', 'main_menu_list');

        $userId = OW::getUser()->getId();

        $cmp = new QUESTIONS_CMP_MyFeed(time(), $userId, self::ITEMS_COUNT);
        $cmp->setFeedType(QUESTIONS_CMP_Feed::FEED_MY);
        $order = QUESTIONS_BOL_FeedService::getInstance()->getOrder(QUESTIONS_CMP_Feed::FEED_MY, OW::getUser()->getId());
        $cmp->setOrder($order);

        $menu = $this->getMenu();
        $menu->setOrder($order);

        $this->addComponent('list', $cmp);
        $this->addComponent('menu', $menu);

        $add = new QUESTIONS_CMP_QuestionAdd();
        $this->addComponent('add', $add);
    }

    public function friends()
    {
        if ( !OW::getPluginManager()->isPluginActive('friends') )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        OW::getDocument()->setTitle($language->text('questions', 'list_friends_page_title'));
        OW::getDocument()->setHeading($language->text('questions', 'list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_lens');

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'questions', 'main_menu_list');

        $userId = OW::getUser()->getId();

        $cmp = new QUESTIONS_CMP_FriendsFeed(time(), $userId, self::ITEMS_COUNT);
        $cmp->setFeedType(QUESTIONS_CMP_Feed::FEED_FRIENDS);
        $order = QUESTIONS_BOL_FeedService::getInstance()->getOrder(QUESTIONS_CMP_Feed::FEED_FRIENDS, OW::getUser()->getId());
        $cmp->setOrder($order);

        $menu = $this->getMenu();
        $menu->setOrder($order);

        $this->addComponent('list', $cmp);
        $this->addComponent('menu', $menu);
    }

    public function rsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $query = json_decode($_POST['query'], true);
        $data = json_decode($_POST['data'], true);

        $responce = array();
        $method = trim($query['command']);

        $responce = call_user_func(array($this, $method), $query, $data);

        echo json_encode($responce);
        exit;
    }

    private function more( $query, $data )
    {
        $count = empty($query['count']) ? self::ITEMS_COUNT : $query['count'];
        $className = $data['className'];

        $cmp = new $className($data['startStamp'], $data['userId'], $count);
        $cmp->setOrder($data['order']);
        $cmp->setRenderedQuestionIds($data['questionIds']);
        $questionsCount = $cmp->findFeedCount($data['startStamp']);

        $html = $cmp->renderList();
        $script = OW::getDocument()->getOnloadScript();

        $data['offset'] = $cmp->getRenderedCount();
        $data['questionIds'] = $cmp->getRenderedQuestionIds();
        $data['viewMore'] = $data['offset'] < $questionsCount;

        return array(
            'data' => $data,
            'markup' => array(
                'html' => $html,
                'script' => $script,
                'position' => 'append'
            )
        );
    }

    private function order( $query, $data )
    {
        if ( !empty($query['order']) )
        {
            $data['order'] = $query['order'];
        }

        QUESTIONS_BOL_FeedService::getInstance()->setOrder($data['feedType'], $query['order'], OW::getUser()->getId());

        $count = empty($query['count']) ? self::ITEMS_COUNT : $query['count'];
        $className = $data['className'];

        $cmp = new $className($data['startStamp'], $data['userId'], $count);
        $cmp->setOrder($data['order']);
        $questionsCount = $cmp->findFeedCount($data['startStamp']);

        $html = $cmp->renderList();
        $script = OW::getDocument()->getOnloadScript();

        $data['offset'] = $cmp->getRenderedCount();
        $data['questionIds'] = $cmp->getRenderedQuestionIds();
        $data['viewMore'] = $data['offset'] < $questionsCount;

        return array(
            'data' => $data,
            'markup' => array(
                'html' => $html,
                'script' => $script,
                'position' => 'replace'
            )
        );
    }

    public function addQuestion()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            echo json_encode(false);
            exit;
        }

        if ( empty($_POST['question']) )
        {
            echo json_encode(false);
            exit;
        }

        $question = empty($_POST['question']) ? '' : strip_tags($_POST['question']);
        $question = UTIL_HtmlTag::autoLink($question);
        $answers = empty($_POST['answers']) ? array() : array_filter($_POST['answers'], 'trim');
        $allowAddOprions = !empty($_POST['allowAddOprions']);

        $userId = OW::getUser()->getId();
        $questionDto = $this->service->addQuestion($userId, $question, array(
            'allowAddOprions' => $allowAddOprions
        ));

        foreach ($answers as $ans)
        {
            $this->service->addOption($questionDto->id, $userId, $ans);
        }

        $event = new OW_Event('feed.action', array(
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE,
            'entityId' => $questionDto->id,
            'pluginKey' => 'questions',
            'userId' => $userId,
            'visibility' => 15 // Visibility all ( 15 )
        ));

        OW::getEventManager()->trigger($event);

        $activityList = QUESTIONS_BOL_FeedService::getInstance()->findMainActivity(time(), array($questionDto->id), array(0, 6));

        $cmp = new QUESTIONS_CMP_FeedItem($questionDto, reset($activityList[$questionDto->id]), $activityList[$questionDto->id]);
        $html = $cmp->render();
        $script = OW::getDocument()->getOnloadScript();

        echo json_encode(array(
            'markup' => array(
                'html' => $html,
                'script' => $script,
                'position' => 'prepend'
            )
        ));
        exit;
    }
}