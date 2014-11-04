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
class QUESTIONS_BOL_AnswerDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_BOL_AnswerDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_BOL_AnswerDao
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
        return 'QUESTIONS_BOL_Answer';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'questions_answer';
    }

    /**
     *
     * @return QUESTIONS_BOL_Answer
     */
    public function findAnswer( $userId, $optionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('optionId', $optionId);
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    public function findByQuestionIdAndUserId( $questionId, $userId )
    {
        $optionDao = QUESTIONS_BOL_OptionDao::getInstance();

        $query ='SELECT a.* FROM ' . $this->getTableName() . ' a ' .
                'INNER JOIN ' . $optionDao->getTableName() . ' o ON a.optionId=o.id ' .
                'WHERE o.questionId=:q AND a.userId=:u';

        return $this->dbo->queryForColumn($query, array(
            'q' => $questionId,
            'u' => $userId
        ));
    }

    public function findListWithUserIdList( $optionId, $userIds, $limit = null )
    {
        if (empty($userIds))
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual('optionId', $optionId);
        $example->andFieldInArray('userId', $userIds);

        if ( !empty($limit) )
        {
            $example->setLimitClause(0, $limit);
        }

        $example->setOrder('timeStamp DESC');

        return $this->findListByExample($example);
    }

    public function findList( $optionId, $limit = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('optionId', $optionId);

        if ( !empty($limit) )
        {
            $example->setLimitClause(0, $limit);
        }

        $example->setOrder('timeStamp DESC');

        return $this->findListByExample($example);
    }

    public function findByOptionId( $optionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('optionId', $optionId);

        return $this->findListByExample($example);
    }

    public function findTotalCountByQuestionId( $questionId )
    {
        $optionDao = QUESTIONS_BOL_OptionDao::getInstance();

        $query ='SELECT COUNT(a.id) FROM ' . $this->getTableName() . ' a ' .
                'INNER JOIN ' . $optionDao->getTableName() . ' o ON a.optionId=o.id ' .
                'WHERE o.questionId=:q';

        return $this->dbo->queryForColumn($query, array(
            'q' => $questionId
        ));
    }

    public function findMaxCountByQuestionId( $questionId )
    {
        $optionDao = QUESTIONS_BOL_OptionDao::getInstance();

        $query ='SELECT count(a.id) FROM ' . $this->getTableName() . ' a ' .
                'INNER JOIN ' . $optionDao->getTableName() . ' o ON a.optionId=o.id ' .
                'WHERE o.questionId=:q GROUP BY o.id ORDER BY count(a.id) DESC limit 1';

        return (int) $this->dbo->queryForColumn($query, array(
            'q' => $questionId
        ));
    }

    public function findCountList( $optionIds )
    {
        if ( empty($optionIds) )
        {
            return array();
        }

        $query ='SELECT optionId, count(id) count FROM ' . $this->getTableName() .
                ' WHERE optionId IN (' . implode(', ', $optionIds) . ') GROUP BY optionId';

        $list = $this->dbo->queryForList($query);
        $out = array();
        foreach ( $list as $row )
        {
            $out[$row['optionId']] = $row['count'];
        }

        foreach ($optionIds as $oid)
        {
            $out[$oid] = empty($out[$oid]) ? 0 : $out[$oid];
        }

        return $out;
    }

    public function findCount( $optionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('optionId', $optionId);

        return $this->countByExample($example);
    }

    public function findUserAnswerList( $userId, $optionIds )
    {
        $example = new OW_Example();
        $example->andFieldInArray('optionId', $optionIds);
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }
    
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual("userId", $userId);
        
        return $this->findListByExample($example);
    }
}