<?php

class Ant_Api_Model_Webhook_Cron extends Mage_Core_Model_Abstract {

    const CRON_SCHEDULE_LIFETIME = 60;

    /**
     * Process the webhooks that are in the queued status
     */
    public function processWebhooks() {
        $statistics = array(
            'success' => 0,
            'failed' => 0,
            'total' => 0,
        );
        /** @var Ant_Api_Model_Mysql4_Webhook_Cron_Schedule_Collection $webhookCollection */
        $webhookSchedule = Mage::getModel('ant_api/webhook_cron_schedule');
        $webhookCollection = $webhookSchedule->getCollection();
        $webhookCollection->addFieldToFilter('status', array('in', Ant_Api_Model_Mysql4_Webhook_Cron_Schedule::STATUS_QUEUED));
        foreach ($webhookCollection as $webhook) {
            try {
                $statistics['total']++;
                $webhook->setStatus(Ant_Api_Model_Mysql4_Webhook_Cron_Schedule::STATUS_PROGRESS);
                $webhook->save();
                /** @var \Ant_Api_Helper_Data $helper */
                $helper = Mage::helper('ant_api');
                $response = $helper->triggerWebhook($webhook->getWebhookUrl(), $webhook->getRequestData());
                $webhook->setResponse($response);
                $webhook->setStatus(Ant_Api_Model_Mysql4_Webhook_Cron_Schedule::STATUS_SUCCESS);
                $webhook->save();
                $statistics['success']++;
            } catch (\Exception $exception) {
                $webhook->setResponse($exception->getMessage());
                $webhook->setStatus(Ant_Api_Model_Mysql4_Webhook_Cron_Schedule::STATUS_FAILED);
                $webhook->save();
                $statistics['failed']++;
            }
        }
        // output the statistics if wanted.
    }

    /**
     * Remove all webhook cron schedule items that have failed or completed within a certain number of days, to keep
     * the table size manageable
     */
    public function cleanOldWebhooks() {
        $webhookSchedule = Mage::getModel('ant_api/webhook_cron_schedule');
        $webhookCollection = $webhookSchedule->getCollection();
        $removalStatuses = array(
            Ant_Api_Model_Mysql4_Webhook_Cron_Schedule::STATUS_SUCCESS,
            Ant_Api_Model_Mysql4_Webhook_Cron_Schedule::STATUS_FAILED
        );
        $webhookCollection->addFieldToFilter('status', array('in', $removalStatuses))->load();
        $now = time();
        /** @var \Ant_Api_Model_Webhook_Cron_Schedule $webhook */
        foreach ($webhookCollection as $webhook) {
            if (strtotime($webhook->getCreatedAt()) < ($now - self::CRON_SCHEDULE_LIFETIME)) {
                $webhook->delete();
            }
        }
    }


}