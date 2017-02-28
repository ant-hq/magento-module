<?php
/*
* AntHQ_Ant_Observer_CatalogProductObserver is the main interface point for observing
* to Magento Events related to Catalog Products.
*/

class AntHQ_Ant_Observer_CatalogProductObserver {

    public function save($observer) {
        Mage::log('AntHQ_Ant: Save Called On Catalog Product');
    }

    public function delete($observer) {
        Mage::log('AntHQ_Ant: Delete Called On Catalog Product');
    }
}