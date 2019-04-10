<?php

/***
 * Class Ant_Api_Helper_Formatting
 */
class Ant_Api_Helper_Format extends Mage_Core_Helper_Data
{
    /***
     * @param $label
     * @return string
     */
    public function createAttributeNameSlugFromLabel($label)
    {
        //TODO: roll this out for all of places that attribute codes are run
        $attributeCode = str_replace(' ', '_', (string) $label);
        $attributeCode = strtolower($attributeCode);
        $attributeCode = preg_replace("/[^A-Za-z0-9]/", '', $attributeCode);
        return $attributeCode;
    }
}