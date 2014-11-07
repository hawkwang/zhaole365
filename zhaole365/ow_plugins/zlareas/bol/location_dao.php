<?php


class ZLAREAS_BOL_LocationDao extends OW_BaseDao
{
    const ID = 'id';
    const ADDRESS_STRING = 'address';
    const LONGITUDE_STRING = 'longitude';
    const LATITUDE_STRING = 'latitude';
    
    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
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
        return 'ZLAREAS_BOL_Location';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlareas_location';
    }
    
    public function getAllLocations()
    {
        $query = "SELECT l.* FROM " . $this->getTableName() . " l ";
                
        return $this->dbo->queryForList($query, array());
    }
    
    public function findLocationById( $id )
    {
    	if ( empty($id) )
    	{
    		return null;
    	}
    
    	$example = new OW_Example();
    	$example->andFieldEqual(self::ID, $id);
    	return $this->findObjectByExample($example);
    }
    
    public function findLocationByAddress( $address )
    {
        if ( empty($address) )
        {
            return null;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual(self::ADDRESS_STRING, $address);
        return $this->findObjectByExample($example);
    }
    
    // FIXME - 这个方法有问题
    public function findLocationByLongitudeAndLatitude($longitude,$latitude)
    {
    	if ( empty($longitude) || empty($latitude) )
    	{
    		return null;
    	}
    	

    	
    	$query = "SELECT l.* FROM " . $this->getTableName() . " l
                WHERE CAST(l.longitude AS DECIMAL) = CAST(:longitude AS DECIMAL) and CAST(l.latitude AS DECIMAL) = CAST(:latitude AS DECIMAL) ";
    	
    	return $this->dbo->queryForObject($query, $this->getDtoClassName(), array('longitude' => floatval($longitude), 'latitude' => floatval($latitude)));
    	
//     	$example = new OW_Example();
//     	$example->andFieldEqual(self::LATITUDE_STRING, (floatval($latitude)));
//     	$example->andFieldEqual(self::LONGITUDE_STRING, (floatval($longitude)));
//     	return $this->findObjectByExample($example);
    }
    
//     public function deleteById( $id )
//     {
//     	if ( empty($id) )
//     	{
//     		return null;
//     	}
    
//     	$example = new OW_Example();
//     	$example->andFieldEqual(self::ID, $id);
//         return $this->deleteByExample($example);
//     }
}
