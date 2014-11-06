<?php


class ZLAREAS_BOL_LocationDao extends OW_BaseDao
{
    const ID = 'id';
    const ADDRESS_STRING = 'address';
    
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
        return OW_DB_PREFIX . 'zllocations';
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
        $example->andFieldEqual(self::ENTITY_ADDRESS, $address);
        return $this->findObjectByExample($example);
    }
    
    public function deleteById( $id )
    {
    	if ( empty($id) )
    	{
    		return null;
    	}
    
    	$example = new OW_Example();
    	$example->andFieldEqual(self::ID, $id);
        return $this->deleteByExample($example);
    }
}
