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
class SMARTCAPTCHA_BOL_AnswerDao extends OW_BaseDao
{
    CONST ID_QUESTION = 'idQuestion';
    
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
        return 'SMARTCAPTCHA_BOL_AnswerDto';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'smartcaptcha_answers';
    }

    public function findAnswersByQuestionId( $questionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual( self::ID_QUESTION, $questionId );
        return $this->findListByExample( $example );
    }
    
    public function deleteAnswerByQuestionIdList( $id )
    {
        $example = new OW_Example();
        $example->andFieldInArray( self::ID_QUESTION, $id );
        return $this->deleteByExample( $example );
    }
}
