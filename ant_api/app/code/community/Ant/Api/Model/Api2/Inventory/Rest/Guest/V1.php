<?php
class Ant_Api_Model_Api2_Inventory_Rest_Guest_V1 extends Ant_Api_Model_Api2_Inventory{

    protected function _update(array $data){
        try {
            Mage::register('is_new_product_api',"noevent");
            $id_product = $this->getRequest()->getParam("id_product");
            $product = Mage::getModel("catalog/product")->load($id_product);
            $qty = $data["quantity"];

            //NB: !important! Don't update manage_stock status during inventory updates
            //$manager_stock = $data["manage_stock"];

            $stockData = $product->getStockData();
            $minQty = (isset($stockData['min_qty']))? $stockData['min_qty'] : 0;

            $stockData['qty']         = $qty;
            $stockData['is_in_stock'] = ($qty > $minQty)? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK : Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;

            $product->setStockData($stockData);

            $product->save();
            unset($product);
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
}