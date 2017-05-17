<?php

class Ant_Api_Model_System_Config_Source_Dropdown_Values
{
    public function toOptionArray()
    {
        $taxCollection=Mage::getModel('tax/class')->getCollection()->addFieldToFilter("class_type",array("eq"=>"PRODUCT"));
        $retArray=array();
        foreach($taxCollection as $_tax){
            $arrayValues = array();
            $arrayValues["value"]=$_tax->getData("class_id");
            $arrayValues["label"]=$_tax->getData("class_name");
            $retArray[] = $arrayValues;
        }
        return $retArray;
    }
}