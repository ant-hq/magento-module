<?php
class Ant_Api_Model_Api2_Order_Rest_Admin_V1 extends Ant_Api_Model_Api2_Order{

    protected function _update(array $data){
        $id_order=$this->getRequest()->getParam("id_order");
        $order=Mage::getModel("sales/order")->load($id_order);
        $status=$data["status"];
        switch ($status){
            case "voided":
                $status=Mage_Sales_Model_Order::STATE_CANCELED;
                break;

        }
        $order->setState($status, true);
        $order->setStatus($status);
        $order->save();
    }
}