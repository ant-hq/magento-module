<?php
class Ant_Api_Model_Mysql4_Webhook_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{

    function _construct()
    {
        parent::_construct(); // TODO: Change the autogenerated stub
        $this->_init("ant_api/webhook");
    }
}