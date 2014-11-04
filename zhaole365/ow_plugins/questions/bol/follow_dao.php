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
 * @package questions.bol
 */
class QUESTIONS_BOL_FollowDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_BOL_FollowDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_BOL_FollowDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'QUESTIONS_BOL_Follow';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'questions_follow';
    }

    public function addFollow( $userId, $questionId )
    {
        $dto = $this->findFollow($userId, $questionId);

        if ( $dto === null )
        {
            $dto = new QUESTIONS_BOL_Follow();
            $dto->userId = $userId;
            $dto->timeStamp = time();
            $dto->questionId = $questionId;
            $this->save($dto);
        }

        return $dto;
    }

    public function findFollow( $userId, $questionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('questionId', $questionId);

        return $this->findObjectByExample($example);
    }

    public function findByQuestionId( $questionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $questionId);

        return $this->findListByExample($example);
    }

    public function findFollowCount( $questionId, $userContext = array(), $ignoreUsers = array() )
    {
        $notIn = '1';

        if ( !empty($ignoreUsers) )
        {
            $notIn = 'userId NOT IN ("' . implode('","', $ignoreUsers) . '")';
        }

        $query = 'SELECT COUNT(*) FROM ' . $this->getTableName() . ' WHERE questionId=:q AND ' . $notIn;

        $out = $this->dbo->queryForColumn($query, array(
            'q' => $questionId
        ));

        return $out;
    }

    public function findFollowList( $questionId, $userContext = array(), $ignoreUsers = array() )
    {
        $notIn = '1';
        $order = 'timeStamp DESC';

        if ( !empty($ignoreUsers) )
        {
            $notIn = 'userId NOT IN ("' . implode('","', $ignoreUsers) . '")';
        }

        if ( !empty($userContext) )
        {
            $order = 'IF( userId IN ("' . implode('","', $userContext) . '"), 1, 0) DESC';
        }

        $query = 'SELECT * FROM ' . $this->getTableName() . ' WHERE questionId=:q AND ' . $notIn . ' ORDER BY ' . $order;

        $out = $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'q' => $questionId
        ));

        return $out;
    }

    public function removeFollow( $userId, $questionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('questionId', $questionId);

        return $this->deleteByExample($example);
    }
    
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual("userId", $userId);
        
        return $this->findListByExample($example);
    }
}