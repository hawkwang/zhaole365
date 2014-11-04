<?php

/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * 
 *
 * @author Kairat Bakytow <kainisoft@gmail.com>
 * @package ow_plugins.smartcaptcha.bol
 * @since 1.0
 */
class SMARTCAPTCHA_BOL_QuestionsDao extends OW_BaseDao
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'SMARTCAPTCHA_BOL_QuestionsDto';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'smartcaptcha_questions';
    }

    public function findRandomQuestion()
    {
        $count = $this->countAll();
        $offet = mt_rand( 0, $count - 1 );
        
        $sql = '
SELECT *
FROM `' . $this->getTableName() .'`
LIMIT ' . $offet .', 1';
        
        return OW::getDbo()->queryForObject( $sql, $this->getDtoClassName() );
    }
    
    public function getQuestionAndAnswersCountList( $page, $limit )
    {
        $first = ( $page - 1 ) * $limit;
        
        $sql = '
SELECT `q`.*, COUNT(`a`.`id`) AS `count`
FROM `' . $this->getTableName() . '` AS `q` 
    LEFT JOIN `' . SMARTCAPTCHA_BOL_AnswerDao::getInstance()->getTableName() .'` AS `a` ON(`q`.`id` = `a`.`idQuestion`)
GROUP BY 1
LIMIT ' . $first . ', ' . $limit;
        
        return OW::getDbo()->queryForList( $sql );
    }
}
