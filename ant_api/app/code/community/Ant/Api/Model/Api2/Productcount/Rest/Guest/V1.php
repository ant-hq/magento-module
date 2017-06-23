<?php
class Ant_Api_Model_Api2_ProductCount_Rest_Guest_V1 extends Ant_Api_Model_Api2_ProductCount
{

    protected function _retrieveCollection(){
        $arrayJsonReturn=array();
        $countProduct= Mage::helper("ant_api")->getCountProductInStore();
        $pageSize=25;
        $pageCount=intval($countProduct / $pageSize)+1;
        $arrayJsonReturn["count"]=$countProduct;
        $arrayJsonReturn["page_count"]=$pageCount;
        $arrayJsonReturn["page_size"]=$pageSize;
        return $arrayJsonReturn;
    }
}