<?php
class AntHQ_Ant_Observer_OrderObserver {

    public function save($observer) {
        Mage::log('AntHQ_Ant: Save Called On Order');
    }
}