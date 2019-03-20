<?php

/***
 * @author Neocreative
 * Class Ant_Api_Helper_Product_Inventory_Data
 */
class Ant_Api_Helper_Product_Inventory_Data extends Mage_Core_Helper_Data
{
    /***
     * @param $qty
     * @param bool $manageStock
     * @param int $stockStatus
     * @return array
     */
    public function prepareDefaultStockArray($qty, $manageStock = true, $stockStatus = Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK ){

        //TODO: use manage stock in preparation
        return array(

            'use_config_manage_stock' => 1,
            //'manage_stock' => $manageStock,
            //'is_in_stock' => $stockStatus,
            'qty' => $qty
        );
    }
}