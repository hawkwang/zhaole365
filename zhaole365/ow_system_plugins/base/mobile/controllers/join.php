<?php

class BASE_MCTRL_Join extends BASE_CTRL_Join
{
    public function __construct()
    {        
        parent::__construct();

        $this->responderUrl = OW::getRouter()->urlFor("BASE_MCTRL_Join", "ajaxResponder");
    }

    public function index( $params )
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->urlForRoute('base_index'));
        }
        
        parent::index($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'join_index.html');

        $urlParams = $_GET;
        
        if ( is_array($params) && !empty($params) )
        {
                $urlParams = array_merge($_GET, $params);
        }

        /* @var $form JoinForm */
        $form = $this->joinForm;
        
        if( !empty($form) )
        {
            $this->joinForm->setAction(OW::getRouter()->urlFor('BASE_MCTRL_Join', 'joinFormSubmit', $urlParams));
            
            BASE_MCLASS_JoinFormUtlis::setLabels($form, $form->getSortedQuestionsList());
            BASE_MCLASS_JoinFormUtlis::setInvitations($form, $form->getSortedQuestionsList());
            BASE_MCLASS_JoinFormUtlis::setColumnCount($form);

            $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');

            $this->assign('requiredPhotoUpload', ($displayPhotoUpload == BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD));
            $this->assign('presentationToClass', $this->presentationToCssClass());

            $element = $this->joinForm->getElement('userPhoto');

            $this->assign('photoUploadId', 'userPhoto');

            if ( $element )
            {
                $this->assign('photoUploadId', $element->getId());
            }

            BASE_MCLASS_JoinFormUtlis::addOnloadJs($form->getName());
        }
    }

    protected function presentationToCssClass()
    {
        return BASE_MCLASS_JoinFormUtlis::presentationToCssClass();
    }

    public function ajaxResponder()
    {
        parent::ajaxResponder();
    }

    public function joinFormSubmit( $params )
    {
        parent::joinFormSubmit($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'join_index.html');
    }
}