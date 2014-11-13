<?php

class ZLAREAS_BOL_Service
{
    /**
     * Singleton instance.
     *
     * @var ZLAREAS_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ZLAREAS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    public function addArea( $areacode, $province, $city, $area )
    {
        $areainfo = new ZLAREAS_BOL_Area();
        $areainfo->areacode = $areacode;
        $areainfo->province = $province;
        $areainfo->city = $city;
        $areainfo->area = $area;
        ZLAREAS_BOL_AreaDao::getInstance()->save($areainfo);
    }

    public function deleteArea( $id )
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            ZLAREAS_BOL_AreaDao::getInstance()->deleteById($id);
        }
    }
    
    public function getAreaByDetailedinfo($province,$city, $district)
    {
    	return ZLAREAS_BOL_AreaDao::getInstance()->getAreaByDetailedinfo($province,$city, $district);
    }


    public function getAreaList()
    {
        return ZLAREAS_BOL_AreaDao::getInstance()->findAll();
    }
    
    public function findAreaById( $id )
    {
    	return ZLAREAS_BOL_AreaDao::getInstance()->findAreaById($id);
    }
    
    public function findByAreacode( $areacode )
    {
    	return ZLAREAS_BOL_AreaDao::getInstance()->findByAreacode($areacode);
    }

}