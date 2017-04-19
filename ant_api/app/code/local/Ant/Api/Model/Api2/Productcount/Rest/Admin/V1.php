<?php
class Ant_Api_Model_Api2_ProductCount_Rest_Admin_V1 extends Ant_Api_Model_Api2_ProductCount
{

    protected function _retrieveCollection(){
        $modelProduct = Mage::getModel('catalog/product');
        $collectionProduct = $modelProduct->getCollection();
        $arrayJsonReturn=array();
        $countProduct=$collectionProduct->count();
        $pageSize=25;
        $pageCount=intval($countProduct / $pageSize)+1;
        $arrayJsonReturn["count"]=$countProduct;
        $arrayJsonReturn["page_count"]=$pageCount;
        $arrayJsonReturn["page_size"]=$pageSize;
        return $arrayJsonReturn;
    }
}