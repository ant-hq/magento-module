<?php
class Ant_Api_Model_Api2_Inventory_Rest_Admin_V1 extends Ant_Api_Model_Api2_Inventory{

    protected function _update(array $data){
        try {
            Mage::register('is_new_product_api',"noevent");
            $id_product = $this->getRequest()->getParam("id_product");
            $product = Mage::getModel("catalog/product")->load($id_product);
            $qty = $data["quantity"];
            $manager_stock = $data["manage_stock"];
            $product->setStockData(array(
                    'use_config_manage_stock' => 0, //'Use config settings' checkbox
                    'manage_stock' => $manager_stock, //manage stock
                    'is_in_stock' => 1, //Stock Availability
                    'qty' => $qty //qty
                )
            );
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