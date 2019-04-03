<?php

/***
 * @author Neocreative
 * Class Ant_Api_Helper_Product_Inventory_Data
 */
class Ant_Api_Helper_Product_Inventory_Data extends Mage_Core_Helper_Data
{

    const USE_CONFIG_MANAGE_STOCK_ENABLE = 1;
    const USE_CONFIG_MANAGE_STOCK_DISABLE = 1;

    /***
     * @param $qty
     * @param bool $manageStock
     * @param int $stockStatus
     * @return array
     */
    public function prepareDefaultStockArray($qty, $manageStock = false){

        $stockData = array(
            'use_config_manage_stock' => self::USE_CONFIG_MANAGE_STOCK_ENABLE,
            'qty' => $qty
        );

        if ($manageStock){
            $stockData['use_config_manage_stock'] = self::USE_CONFIG_MANAGE_STOCK_DISABLE;
            $stockData['manage_stock'] = (int) $manageStock;
    }
//        $stockData['is_in_stock'] = ($qty)? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK : Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;
        return $stockData;


    }
}
