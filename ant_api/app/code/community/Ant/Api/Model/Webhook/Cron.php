<?php

class Ant_Api_Model_Webhook_Cron extends Mage_Core_Model_Abstract {

    const CRON_SCHEDULE_LIFETIME = 2592000;
    const MAX_ROWS_TO_KEEP = 10000;

    /**
     * Process the webhooks that are in the queued status
     */
    const SUCCESS = 'success';

    const FAILED = 'failed';

    const TOTAL = 'total';

    public function processWebhooks() {
        $statistics = array(
            self::SUCCESS => 0,
            self::FAILED  => 0,
            self::TOTAL   => 0,
        );
        /** @var Ant_Api_Model_Mysql4_Webhook_Cron_Schedule_Collection $webhookCollection */
        $webhookSchedule = Mage::getModel('ant_api/webhook_cron_schedule');
        $processStatuses = array(
            Ant_Api_Model_Webhook_Cron_Schedule::STATUS_QUEUED,
            Ant_Api_Model_Webhook_Cron_Schedule::STATUS_PROGRESS
        );
        $webhookCollection = $webhookSchedule->getCollection();
        $webhookCollection->addFieldToFilter('status', array('in' => $processStatuses));
        $webhookCollection->addWebhookUrl();
        if (!$webhookCollection->getSize()) {
            echo "No webhooks to process.";
            return;
        }
        foreach ($webhookCollection as $webhook) {
            try {
                $statistics[self::TOTAL]++;
                $webhook->setStatus(Ant_Api_Model_Webhook_Cron_Schedule::STATUS_PROGRESS);
                $webhook->save();
                /** @var \Ant_Api_Helper_Data $helper */
                $helper = Mage::helper('ant_api');
                if (!$webhook->getWebhookUrl()) {
                    Mage::throwException('Webhook URL required to process webhook');
                }
                if (!is_array($webhook->getRequestData())) {
                    Mage::throwException('Request Data required to process webhook');
                }
                $response = $helper->forceWebhook($webhook->getWebhookUrl(), $webhook->getRequestData());
                if (!isset($response['message'])) {
                    $response['message'] = $helper->__('Unable to parse webhook response');
                }
                $webhook->setResponse($response['message']);
                $webhook->setStatus(Ant_Api_Model_Webhook_Cron_Schedule::STATUS_SUCCESS);
                $webhook->save();
                $statistics[self::SUCCESS]++;
            } catch (\Exception $exception) {
                $webhook->setResponse($exception->getMessage());
                $webhook->setStatus(Ant_Api_Model_Webhook_Cron_Schedule::STATUS_FAILED);
                $webhook->save();
                $statistics[self::FAILED]++;
            }
        }
        echo sprintf("Processed all %d webhooks. </br> " . PHP_EOL . "Succeeded: %d </br> " . PHP_EOL . "Failed: %d </br>" . PHP_EOL, $statistics[self::TOTAL], $statistics[self::SUCCESS], $statistics[self::FAILED]);
        // output the statistics if wanted.
    }

    /**
     * Remove all webhook cron schedule items that have failed or completed within a certain number of days, to keep
     * the table size manageable
     * Limit by 30 days or 10000 rows.
     */
    public function cleanOldWebhooks() {
        $webhookSchedule = Mage::getModel('ant_api/webhook_cron_schedule');
        /** @var \Ant_Api_Model_Mysql4_Webhook_Cron_Schedule_Collection $webhookCollection */
        $webhookCollection = $webhookSchedule->getCollection();
        $webhookCollection->getSelect()->order('created_at' );
        $removalStatuses = array(
            Ant_Api_Model_Webhook_Cron_Schedule::STATUS_SUCCESS,
            Ant_Api_Model_Webhook_Cron_Schedule::STATUS_FAILED
        );
        $webhookCollection->addFieldToFilter('status', array('in' => $removalStatuses))->load();
        $now = time();
        /** @var \Ant_Api_Model_Webhook_Cron_Schedule $webhook */
        foreach ($webhookCollection as $webhook) {
            $createdTime = strtotime($webhook->getCreatedAt());
            $expiredTime = $now - self::CRON_SCHEDULE_LIFETIME;
            if ($createdTime < $expiredTime) {
                $webhook->delete();
            }
        }
        // TODO: Add in way to remove all webhooks when there are more than 10000
        // TODO: This should be done at the database layer for performance reasons
//        if ($webhookCollection->getSize() > self::MAX_ROWS_TO_KEEP) {
//            $webhookCollection->getSelect()->limitPage(1, self::MAX_ROWS_TO_KEEP);
//            $webhookCollection->getSelect()->reset(Zend_Db_Select::WHERE);
//            foreach ($webhookCollection as $oldWebhook) {
//                $oldWebhook->delete();
//            }
//        }
    }
}