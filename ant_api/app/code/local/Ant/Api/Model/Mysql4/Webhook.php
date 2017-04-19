<?php
class Ant_Api_Model_Mysql4_Webhook extends Mage_Core_Model_Mysql4_Abstract{

    public function _construct()
    {
        // TODO: Implement _construct() method.
        $this->_init("ant_api/webhook","ant_api_webhook_id");
    }
}