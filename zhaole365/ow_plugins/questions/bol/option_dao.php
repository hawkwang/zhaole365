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
class QUESTIONS_BOL_OptionDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_BOL_OptionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_BOL_OptionDao
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
        return 'QUESTIONS_BOL_Option';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'questions_option';
    }

    public function findListWithAnswerCountList( $id, $startStamp, $priorUsers = array(), $limit = null )
    {
        $answerDao = QUESTIONS_BOL_AnswerDao::getInstance();
        $limitSql = empty($limit) ? '' : 'LIMIT ' . $limit[0] . ', ' . $limit[1];

        if ( empty($priorUsers) )
        {
            $query ='SELECT o.*, count(DISTINCT a.id) AS answerCount FROM ' . $this->getTableName() . ' o ' .
                'LEFT JOIN ' . $answerDao->getTableName() . ' a ON o.id = a.optionId ' .
                'WHERE o.questionId=:q AND o.timeStamp <= :ss
                    GROUP BY o.id
                    ORDER BY answerCount DESC, o.timeStamp, o.id ' . $limitSql;
        }
        else
        {
            $query ='SELECT o.*, count(DISTINCT a.id) AS answerCount FROM ' . $this->getTableName() . ' o ' .
                'LEFT JOIN ' . $answerDao->getTableName() . ' a ON o.id = a.optionId ' .
                'LEFT JOIN ' . $answerDao->getTableName() . ' a2 ON o.id = a2.optionId AND a2.userId IN (' . implode(', ', $priorUsers) . ') ' .
                'WHERE o.questionId=:q AND o.timeStamp <= :ss
                    GROUP BY o.id
                    ORDER BY count(DISTINCT a2.userId) DESC, answerCount DESC, o.timeStamp, o.id ' . $limitSql;
        }

        $list = $this->dbo->queryForList($query, array(
            'q' => $id,
            'ss' => $startStamp
        ));

        $countList = array();
        $optionList = array();

        foreach ( $list as $row )
        {
            $countList[$row['id']] = $row['answerCount'];
            unset($row['answerCount']);

            $option = new QUESTIONS_BOL_Option;
            foreach ( $row as $k => $v )
            {
                $option->$k = $v;
            }

            $optionList[$row['id']] = $option;
        }

        return array(
            'countList' => $countList,
            'optionList' => $optionList
        );
    }

    public function findCountByQuestionId( $id )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $id);

        return $this->countByExample($example);
    }

    public function findByText( $questionId, $text )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $questionId);
        $example->andFieldEqual('text', $text);

        return $this->findObjectByExample($example);
    }

    public function findByQuestionId( $questionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $questionId);

        return $this->findListByExample($example);
    }
}