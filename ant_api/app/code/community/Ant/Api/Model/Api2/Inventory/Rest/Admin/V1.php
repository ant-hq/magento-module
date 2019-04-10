<?php
class Ant_Api_Model_Api2_Inventory_Rest_Admin_V1 extends Ant_Api_Model_Api2_Inventory{


    /***
     * @var array
     */
    private $requiredFields = array(
        'quantity' => array('type' => 'float')
    );

    /***
     * @param $data
     * @param $fieldData
     * @return bool
     * @throws Mage_Core_Exception
     */
    private function validateField($data, $fieldData){
        if (!isset($fieldData['type'])){
            Mage::throwException('Invalid field setup');
        }
        $isValid = false;
        switch($fieldData['type']){
            case 'float':
                $isValid = is_float(filter_var($data, FILTER_VALIDATE_FLOAT));
                break;
            case 'int':
                $isValid = is_int(filter_var($data, FILTER_VALIDATE_INT));
        }
        return $isValid;
    }

    /***
     * @param array $data
     * @throws Mage_Core_Exception
     */
    private function validateData(array $data){
        $errorMessages = array();
        foreach ($this->requiredFields as $field => $fieldData){
            if (!isset($data[$field])){
                $errorMessages[] = "$field is missing";
                continue;
            }
            if (!$this->validateField($data[$field], $fieldData)){
                $errorMessages[] = "$field has data in the wrong format: " . $data[$field] . " is not a " . $fieldData['type'];
            }
        }
        if (!count($errorMessages)){
            //valid data
            return;
        }
        Mage::throwException(implode(", ", $errorMessages));
    }

    protected function _update(array $data){
        try {
            Mage::register('is_new_product_api',"noevent");
            $id_product = $this->getRequest()->getParam("id_product");
            //should be a better way to do this, but not worth looking into just yet
            $this->validateData($data);
            $product = Mage::getModel("catalog/product")->load($id_product);

            $qty = (float) $data["quantity"];


            //NB: !important! Don't update manage_stock status during inventory updates
            //$manager_stock = $data["manage_stock"];

            $stockData = $product->getStockData();
            $minQty = (isset($stockData['min_qty']))? $stockData['min_qty'] : 0;

            $stockData['qty']         = $qty;
            $stockData['is_in_stock'] = ($qty > $minQty)? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK : Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;

            $product->setStockData($stockData);
            $product->save();

            Mage::getSingleton('cataloginventory/stock_status')->updateStatus($product->getId());
            unset($product);
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

    }
}