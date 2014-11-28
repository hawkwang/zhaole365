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
//         $this->setPageTitle('找乐地盘配置');
//         $this->setPageHeading('找乐地盘配置');
        
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('zlareas')->getStaticCssUrl() . 'bootstrap.min.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlareas')->getStaticJsUrl() . 'jquery-1.10.2.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY+10);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlareas')->getStaticJsUrl() . 'bootstrap.min.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY);
        
        OW::getDocument()->addScript('http://api.map.baidu.com/api?v=2.0&ak=HL2OtpqEFglWT1j2RoS62eRD');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlareas')->getStaticJsUrl() . 'baidu_map.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY);
        
        $this->setPageTitle(OW::getLanguage()->text('zlareas', 'admin_area_title'));
        $this->setPageHeading(OW::getLanguage()->text('zlareas', 'admin_area_heading'));

        // information about areas
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
        //$fieldAreacode->setInvitation('区域编码');
        $fieldAreacode->setInvitation(OW::getLanguage()->text('zlareas', 'label_invitation_areacode'));
        $fieldAreacode->setHasInvitation(true);
        $form->addElement($fieldAreacode);
        
        $fieldProvince = new TextField('province');
        $fieldProvince->setRequired();
        //$fieldProvince->setInvitation('省份或直辖市');
        $fieldProvince->setInvitation(OW::getLanguage()->text('zlareas', 'label_invitation_province'));
        $fieldProvince->setHasInvitation(true);
        $form->addElement($fieldProvince);
        
        $fieldCity = new TextField('city');
        $fieldCity->setRequired();
        //$fieldCity->setInvitation('城市');
        $fieldCity->setInvitation(OW::getLanguage()->text('zlareas', 'label_invitation_city'));
        $fieldCity->setHasInvitation(true);
        $form->addElement($fieldCity);
        
        $fieldArea = new TextField('area');
        $fieldArea->setRequired();
        //$fieldArea->setInvitation('区域');
        $fieldArea->setInvitation(OW::getLanguage()->text('zlareas', 'label_invitation_area'));
        $fieldArea->setHasInvitation(true);
        $form->addElement($fieldArea);


        $submit = new Submit('add');
        $submit->setValue('添加');
        $form->addElement($submit);

        // information about locations
        $locationinfos = array();
        $deleteLocationUrls = array();
        $locations = ZLAREAS_BOL_LocationService::getInstance()->getLocationList();
        foreach ( $locations as $location )
        {
        	$locationinfos[$location->id]['areacode'] = $location->areacode;
        	$locationinfos[$location->id]['id'] = $location->id;
        	$locationinfos[$location->id]['address'] = $location->address;
        	$locationinfos[$location->id]['longitude'] = $location->longitude;
        	$locationinfos[$location->id]['latitude'] = $location->latitude;
        	$locationinfos[$location->id]['description'] = $location->description;
        	$deleteLocationUrls[$location->id] = OW::getRouter()->urlFor(__CLASS__, 'deletelocation', array('id' => $location->id));
        }
        $this->assign('locations', $locationinfos);
        $this->assign('deleteLocationUrls', $deleteLocationUrls);
        
        $location_form = new Form('add_location');
        $this->addForm($location_form);
        
        $fieldFrom = new Selectbox('from');
        $provinces = array();
        $provinces[] = '北京市';
        $provinces[] = '天津市';
        $provinces[] = '重庆市';
        $provinces[] = '上海市';
        $provinces[] = '河北省';
        $provinces[] = '山西省';
        $provinces[] = '辽宁省';
        $provinces[] = '吉林省';
        $provinces[] = '黑龙江省';
        $provinces[] = '江苏省';
        $provinces[] = '浙江省';
        $provinces[] = '安徽省';
        $provinces[] = '福建省';
        $provinces[] = '江西省';
        $provinces[] = '山东省';
        $provinces[] = '河南省';
        $provinces[] = '湖北省';
        $provinces[] = '湖南省';
        $provinces[] = '广东省';
        $provinces[] = '海南省';
        $provinces[] = '四川省';
        $provinces[] = '贵州省';
        $provinces[] = '云南省';
        $provinces[] = '陕西省';
        $provinces[] = '甘肃省';
        $provinces[] = '青海省';
        $provinces[] = '台湾省';
        $provinces[] = '内蒙古自治区';
        $provinces[] = '广西壮族自治区';
        $provinces[] = '宁夏回族自治区';
        $provinces[] = '新疆维吾尔自治区';
        $provinces[] = '西藏自治区';
        $provinces[] = '香港特别行政区';
        $provinces[] = '澳门特别行政区';
        
        
        foreach ( $provinces as $id => $value )
        {
        	$fieldFrom->addOption($value, $value);
        }
        $fieldFrom->setRequired();
        $fieldFrom->setHasInvitation(false);
        $fieldFrom->setLabel('选择省或直辖市');
        $location_form->addElement($fieldFrom);
        
        $fieldLocationDescription = new TextField('l_description');
        $fieldLocationDescription->setRequired();
        $fieldLocationDescription->setInvitation('原始地址');
        $fieldLocationDescription->setHasInvitation(true);
        $location_form->addElement($fieldLocationDescription);

        $fieldLocationAddress = new TextField('l_address');
        $fieldLocationAddress->setRequired();
        $fieldLocationAddress->setInvitation('精确地址');
        $fieldLocationAddress->setHasInvitation(true);
        $location_form->addElement($fieldLocationAddress);
        
        $fieldLocationProvince = new TextField('l_province');
        $fieldLocationProvince->setRequired();
        $fieldLocationProvince->setInvitation('省或直辖市');
        $fieldLocationProvince->setHasInvitation(true);
        $location_form->addElement($fieldLocationProvince);

        $fieldLocationCity = new TextField('l_city');
        $fieldLocationCity->setRequired();
        $fieldLocationCity->setInvitation('城市');
        $fieldLocationCity->setHasInvitation(true);
        $location_form->addElement($fieldLocationCity);

        $fieldLocationDistrict = new TextField('l_district');
        $fieldLocationDistrict->setRequired();
        $fieldLocationDistrict->setInvitation('区域');
        $fieldLocationDistrict->setHasInvitation(true);
        $location_form->addElement($fieldLocationDistrict);        
        
        $fieldLocationLongitude = new TextField('l_longitude');
        $fieldLocationLongitude->setRequired();
        $fieldLocationLongitude->setInvitation('经度');
        $fieldLocationLongitude->setHasInvitation(true);
        $location_form->addElement($fieldLocationLongitude);
        
        $fieldLocationLatitude = new TextField('l_latitude');
        $fieldLocationLatitude->setRequired();
        $fieldLocationLatitude->setInvitation('纬度');
        $fieldLocationLatitude->setHasInvitation(true);
        $location_form->addElement($fieldLocationLatitude);
        
        $fieldLocationOriginAddress = new TextField('l_origin_address');
        $fieldLocationOriginAddress->setRequired();
        $fieldLocationOriginAddress->setInvitation('请输入地址');
        $fieldLocationOriginAddress->setHasInvitation(true);
        $location_form->addElement($fieldLocationOriginAddress);
        
        $location_submit = new Submit('add_location');
        $location_submit->setValue('添加');
        $location_form->addElement($location_submit);
        
        
        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                ZLAREAS_BOL_Service::getInstance()->addArea($data['areacode'], $data['province'], $data['city'], $data['area']);
                $this->redirect();
            }

            if ( $location_form->isValid($_POST) )
            {
                $data = $location_form->getValues();
                $address = $data['l_address'];
                $description = $data['l_description'];
                $province = $data['l_province'];
                $city = $data['l_city'];
                $district = $data['l_district'];
                $longitude = $data['l_longitude'];
                $latitude = $data['l_latitude'];
                
                // check if we have this location already with longitude and latitude
                // if we have this, we will update the description field with more original address,
                $existing_location = ZLAREAS_BOL_LocationService::getInstance()->findLocationByAddress($address);
                if($existing_location != null)
                {
                	// check if the original address is already located in the description, if yes, do nothing; else update the description
                	if(ZLAREAS_CLASS_Utility::getInstance()->containOriginAddress($existing_location->description, $description)==false)
                	{
	                	$existing_location->description = $existing_location->description . '||' . $description;
	                	ZLAREAS_BOL_LocationService::getInstance()->save($existing_location);
                	}
                	$this->redirect();
                }
                
                // otherwise, we will add this new location here
                ZLAREAS_BOL_LocationService::getInstance()->addDetailedLocation($address, $province, $city, $district, $longitude, $latitude, $description);
                
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
	
	public function deletelocation( $params )
	{
		if ( isset($params['id']) )
		{
			ZLAREAS_BOL_LocationService::getInstance()->deleteById((int) $params['id']);
		}
		$this->redirect(OW::getRouter()->urlForRoute('zlareas.admin'));
	}
	
}