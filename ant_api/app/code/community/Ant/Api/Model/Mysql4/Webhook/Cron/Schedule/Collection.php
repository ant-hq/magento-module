<?php
class Ant_Api_Model_Mysql4_Webhook_Cron_Schedule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected function _construct()
    {
        parent::_construct(); // TODO: Change the autogenerated stub
        $this->_init("ant_api/webhook_cron_schedule");
    }
    public function addWebhookUrl() {
        $this->getSelect()->joinLeft(
            array('ant_webhook' => $this->getTable('ant_api/webhook')),
            'main_table.webhook_id = ant_webhook.ant_api_webhook_id',
            array('webhook_url' => 'ant_api_webhook_url')
        );
        return $this;
    }
}