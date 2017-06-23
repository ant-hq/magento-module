<?php
class Ant_Api_Model_Api2_WebHook_Rest_Admin_V1 extends Ant_Api_Model_Api2_WebHook
{
    protected function _create(array $data){
        //After create
        try {
            $url = $data["url"];
            $action = $data["action"];
            $webhook = Mage::getModel("ant_api/webhook");
            $webhook->setData("ant_api_webhook_url", $url);
            $webhook->setData("ant_api_webhook_action", $action);
            $webhook->save();
            return $webhook->getId();
        }catch (Exception $e){
            $this->_critical($e->getMessage(),400);
        }
    }
}