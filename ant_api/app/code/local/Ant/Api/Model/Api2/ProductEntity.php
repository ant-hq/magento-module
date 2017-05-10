<?php
class Ant_Api_Model_Api2_ProductEntity extends Mage_Api2_Model_Resource
{
    public function dispatch()
    {
        //Mage::log($this->getActionType() . $this->getOperation(),null,"debug.txt");
        switch ($this->getActionType() . $this->getOperation()) {
            /* Create */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_CREATE:
                // Creation of objects is possible only when working with collection
                $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_CREATE:
                // If no of the methods(multi or single) is implemented, request body is not checked
                if (!$this->_checkMethodExist('_create') && !$this->_checkMethodExist('_multiCreate')) {
                    $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                }
                // If one of the methods(multi or single) is implemented, request body must not be empty
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                // The create action has the dynamic type which depends on data in the request body
                if ($this->getRequest()->isAssocArrayInRequestBody()) {
                    $this->_errorIfMethodNotExist('_create');
                    $filteredData = $this->getFilter()->in($requestData);
                    if (empty($filteredData)) {
                        $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                    }
                    $newItemLocation = $this->_create($filteredData);
                    $arrayNewItem=explode("|",$newItemLocation);
                    $idProduct=$arrayNewItem[0];
                    $typeProduct=$arrayNewItem[1];
                    $arrayParent=array();

                    if($typeProduct=="simple") {
                        $arrayParent["product"] = Mage::helper("ant_api")->setTheHashProductSimple($idProduct);
                    }else{
                        $arrayParent["product"] = Mage::helper("ant_api")->setTheHashConfigruableProduct($idProduct);
                    }
                    //$this->getResponse()->setHeader('Location', $newItemLocation);
                    //$this->getResponse()->setHttpResponseCode(200);

                    $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
                    $this->getResponse()->setBody(json_encode($arrayParent));
                } else {
                    $this->_errorIfMethodNotExist('_multiCreate');
                    $filteredData = $this->getFilter()->collectionIn($requestData);
                    $this->_multiCreate($filteredData);
                    $this->_render($this->getResponse()->getMessages());
                    $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
                }
                break;
            /* Retrieve */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieve');
                $retrievedData = $this->_retrieve();
                $filteredData  = $this->getFilter()->out($retrievedData);
                $this->_render($filteredData);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieveCollection');
                $retrievedData = $this->_retrieveCollection();
                $filteredData  = $retrievedData;
                $this->_render($filteredData);
                break;
            /* Update */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_update');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $filteredData = $this->getFilter()->in($requestData);
                if (empty($filteredData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $this->_update($filteredData);
                $id=$this->getRequest()->getParam("id_product");
                $product = Mage::getModel("catalog/product")->load($id);
                $productType=$product->getTypeId();
                $newItemLocation=$id."|".$productType;
                $arrayNewItem=explode("|",$newItemLocation);
                $idProduct=$arrayNewItem[0];
                $typeProduct=$arrayNewItem[1];
                $arrayParent=array();

                if($typeProduct=="simple") {
                    $arrayParent["product"] = Mage::helper("ant_api")->setTheHashProductSimple($idProduct);
                }else{
                    $arrayParent["product"] = Mage::helper("ant_api")->setTheHashConfigruableProduct($idProduct);
                }
                //$this->getResponse()->setHeader('Location', $newItemLocation);
                //$this->getResponse()->setHttpResponseCode(200);
                $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
                $this->getResponse()->setBody(json_encode($arrayParent));
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_multiUpdate');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $filteredData = $this->getFilter()->collectionIn($requestData);
                $this->_multiUpdate($filteredData);
                $this->_render($this->getResponse()->getMessages());
                $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
                break;
            /* Delete */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_delete');
                $this->_delete();
                $id_product=$this->getRequest()->getParam("id_product");
                $arrayParent["message"]="Deleted product [$id_product] successfully";
                $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
                $this->getResponse()->setBody(json_encode($arrayParent));
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_multiDelete');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $this->_multiDelete($requestData);
                $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
                break;
            default:
                $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                break;
        }
    }
}