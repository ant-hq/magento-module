<?php

/***
 * Class Ant_Api_Model_System_Config_Sync_Source_Framework
 */
class Ant_Api_Model_System_Config_Sync_Source_Framework
{
    const OPTION_VALUE_MAGENTO = 1;
    const OPTION_VALUE_ANTHQ = 2;

    const OPTION_LABEL_MAGENTO = 'Magento';
    const OPTION_LABEL_ANTHQ = "AntHQ";

    /**
     * Options for Config section
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
             'label' => Mage::helper('ant_api')->__(self::OPTION_LABEL_MAGENTO),
             'value' => self::OPTION_VALUE_MAGENTO
        );
        $options[] = array(
            'label' => Mage::helper('ant_api')->__(self::OPTION_VALUE_ANTHQ),
            'value' => self::OPTION_LABEL_ANTHQ
        );
        return $options;
    }
}