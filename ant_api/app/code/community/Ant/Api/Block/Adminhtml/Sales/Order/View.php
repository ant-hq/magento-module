<?php

class Ant_Api_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{

    public function __construct()
    {
        parent::__construct();

        $onclickJs = 'deleteConfirm(\'' . 'Sync to Ant?' . '\', \'' . $this->getAntOrderWebhook() . '\');';

        $this->_addButton('order_edit', array(
            'label'    => Mage::helper('sales')->__('Sync With Ant'),
            'onclick'  => $onclickJs,
        ));
    }

    public function getAntOrderWebhook() {
        /** @var \Ant_Api_Helper_Data $antHelper */
        $antHelper = Mage::helper('ant_api');
        return $antHelper->getDataWebHook(Ant_Api_Model_Webhook::ORDER_CREATE);
    }

}
