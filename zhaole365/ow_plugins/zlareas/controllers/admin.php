<?php



/**
 * Main page
 *
 * @author Hawk Wang <zhaole365@yahoo.com>
 * @package ow_plugins.zlareas.controllers
 * @since 1.0
 */
class ZLAREAS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $this->setPageTitle('找乐地盘');
        $this->setPageHeading('找乐地盘');
//         $this->setPageTitle(OW::getLanguage()->text('zlareas', 'admin_area_title'));
//         $this->setPageHeading(OW::getLanguage()->text('zlareas', 'admin_area_heading'));
        $areainfos = array();
        $deleteUrls = array();
        $areas = ZLAREAS_BOL_Service::getInstance()->getAreaList();
        foreach ( $areas as $area )
        {
        	$areainfos[$area->id]['areacode'] = $area->areacode;
        	$areainfos[$area->id]['id'] = $area->id;
        	$areainfos[$area->id]['province'] = $area->province;
        	$areainfos[$area->id]['city'] = $area->city;
        	$areainfos[$area->id]['area'] = $area->area;
            $deleteUrls[$area->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $area->id));
        }
        $this->assign('areas', $areainfos);
        $this->assign('deleteUrls', $deleteUrls);

        $form = new Form('add_area');
        $this->addForm($form);

        $fieldAreacode = new TextField('areacode');
        $fieldAreacode->setRequired();
        $fieldAreacode->setInvitation('区域编码');
//         $fieldAreacode->setInvitation(OW::getLanguage()->text('zlareas', 'label_invitation_areacode'));
        $fieldAreacode->setHasInvitation(true);
        $form->addElement($fieldAreacode);
        
        $fieldProvince = new TextField('province');
        $fieldProvince->setRequired();
        $fieldProvince->setInvitation('省份或直辖市');
//         $fieldProvince->setInvitation(OW::getLanguage()->text('zlareas', 'label_invitation_province'));
        $fieldProvince->setHasInvitation(true);
        $form->addElement($fieldProvince);
        
        $fieldCity = new TextField('city');
        $fieldCity->setRequired();
        $fieldCity->setInvitation('城市');
        $fieldCity->setHasInvitation(true);
        $form->addElement($fieldCity);
        
        $fieldArea = new TextField('area');
        $fieldArea->setRequired();
        $fieldArea->setInvitation('区域');
        $fieldArea->setHasInvitation(true);
        $form->addElement($fieldArea);


        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('zlareas', 'form_add_area_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                ZLAREAS_BOL_Service::getInstance()->addArea($data['areacode'], $data['province'], $data['city'], $data['area']);
                $this->redirect();
            }
        }
    }
	
	public function delete( $params )
	{
		if ( isset($params['id']) )
		{
			ZLAREAS_BOL_Service::getInstance()->deleteArea((int) $params['id']);
		}
		$this->redirect(OW::getRouter()->urlForRoute('zlareas.admin'));
	}
	
}