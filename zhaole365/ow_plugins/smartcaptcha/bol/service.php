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
class SMARTCAPTCHA_BOL_Service
{
    private static $classInstance;
    
    private $questionDao;
    private $answerDao;

    private function __construct()
    {
        $this->questionDao = SMARTCAPTCHA_BOL_QuestionsDao::getInstance();
        $this->answerDao = SMARTCAPTCHA_BOL_AnswerDao::getInstance();
    }
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function getRandomQuestion()
    {
        return $this->questionDao->findRandomQuestion();
    }
    
    public function findAnswersByQuestionId( $id )
    {
        return $this->answerDao->findAnswersByQuestionId( $id );
    }
    
    public function getQuestionAndAnswersCountList( $page, $limit )
    {
        return $this->questionDao->getQuestionAndAnswersCountList( $page, $limit );
    }
    
    public function findQuestionAndQnswersByQuestionId( $id )
    {
        if ( empty($id) )
        {
            return array();
        }
        
        $qustion = $this->questionDao->findById( $id );
        $qustion->answers = $this->answerDao->findAnswersByQuestionId( $id );
        
        return $qustion;
    }
    
    public function saveQuestion( OW_Entity $entity )
    {
        return $this->questionDao->save( $entity );
    }
    
    public function saveAnswer( OW_Entity $entity )
    {
        return $this->answerDao->save( $entity );
    }

    public function deleteAnswerByQuestionIdList( $id )
    {
        $this->answerDao->deleteAnswerByQuestionIdList( $id );
    }
    
    public function deleteQuestionByIdList( $list )
    {
        if ( $this->questionDao->deleteByIdList( $list ) && $this->answerDao->deleteAnswerByQuestionIdList( $list ) )
        {
            return true;
        }
    }
    
    public function questionCountAll()
    {
        return $this->questionDao->countAll();
    }
}
