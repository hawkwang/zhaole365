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
 * @package questions.classes
 */
class QUESTIONS_CLASS_CommentsBridge
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_CLASS_CommentsBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_CLASS_CommentsBridge
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
     *
     * @var BOL_CommentService
     */
    private $service;

    private function __construct()
    {
        $this->service = BOL_CommentService::getInstance();
    }

    public function onCommentAdd( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != QUESTIONS_BOL_Service::ENTITY_TYPE )
        {
            return;
        }

        $questionId = (int) $params['entityId'];
        $comment = $this->service->findComment($params['commentId']);

        $event = new OW_Event(QUESTIONS_BOL_Service::EVENT_POST_ADDED, array(
            'questionId' => $questionId,
            'id' => (int) $params['commentId'],
            'userId' => (int) $params['userId'],
            'text' => $comment->message
        ));

        OW::getEventManager()->trigger($event);
    }

    public function onCommentRemove( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != QUESTIONS_BOL_Service::ENTITY_TYPE )
        {
            return;
        }

        $questionId = (int) $params['entityId'];

        $event = new OW_Event(QUESTIONS_BOL_Service::EVENT_POST_REMOVED, array(
            'questionId' => $questionId,
            'id' => (int) $params['commentId'],
            'userId' => (int) $params['userId']
        ));

        OW::getEventManager()->trigger($event);
    }

    public function onQuestionRemove( OW_Event $e )
    {
        $params = $e->getParams();
        $questionId = (int) $params['id'];

        $posts = $this->service->findFullCommentList(QUESTIONS_BOL_Service::ENTITY_TYPE, $questionId);

        foreach ( $posts as $post )
        {
            $event = new OW_Event(QUESTIONS_BOL_Service::EVENT_POST_REMOVED, array(
                'questionId' => $questionId,
                'id' => $post->id,
                'userId' => $post->userId
            ));

            OW::getEventManager()->trigger($event);
        }

        $this->service->deleteEntityComments(QUESTIONS_BOL_Service::ENTITY_TYPE, $questionId);
    }
}