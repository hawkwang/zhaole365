<?php

class ZLAREAS_BOL_AreaDao extends OW_BaseDao
{
	const ID = 'id';
	const AREACODE = 'areacode';
	const PROVINCE_STRING = 'province';
	const CITY_STRING = 'city';		
	const DISTRICT_STRING = 'area';
/**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var ZLAREAS_BOL_AreaDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ZLAREAS_BOL_AreaDao
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
        return 'ZLAREAS_BOL_Area';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlareas_area';
    }
    
    public function getAreaByDetailedinfo($province,$city, $district)
    {
    		if ( empty($province) || empty($city)|| empty($district))
    		{
    			return null;
    		}
    	
    		$example = new OW_Example();
    		$example->andFieldEqual(self::PROVINCE_STRING, $province);
    		$example->andFieldEqual(self::CITY_STRING, $city);
    		$example->andFieldEqual(self::DISTRICT_STRING, $district);
    		return $this->findObjectByExample($example);
    }
    
    public function findAreaById( $id )
    {
    	if ( empty($id) )
    	{
    		return null;
    	}
    
    	$example = new OW_Example();
    	$example->andFieldEqual(self::ID, $id);
    	return $this->findObjectByExample($example);
    }
    
    public function findByAreacode( $areacode )
    {
    	if ( empty($areacode) )
    	{
    		return null;
    	}
    
    	$example = new OW_Example();
    	$example->andFieldEqual(self::AREACODE, $areacode);
    	return $this->findObjectByExample($example);
    }
}