<?php
class Ant_Api_Model_Api2_ProductEntity_Rest_Admin_V1 extends Ant_Api_Model_Api2_ProductEntity
{

    protected function _retrieve(){
        $idProduct=$this->getRequest()->getParam("id_product");
        $product=Mage::getModel("catalog/product")->getCollection()->addAttributeToFilter("entity_id",array("eq",$idProduct))->getFirstItem();
        $arrayJsonProductSimple=array();
        switch ($product->getTypeId()){
            case "simple":
                $arrayJsonProductSimple=Mage::helper("ant_api")->setTheHashProductSimple($idProduct);
                break;
            case "configurable":
                $arrayJsonProductSimple=Mage::helper("ant_api")->setTheHashConfigruableProduct($idProduct);
                break;
        }

        return $arrayJsonProductSimple;
    }
    protected function _criticalCustom($message, $code = null)
    {
        throw new Exception($message,$code);
    }
    protected function _checkAttribute($attribute_name,$data){
        if(!isset($data[$attribute_name])){
            return false;
        }
        return true;
    }
    protected function _delete(){
        $idProduct=$this->getRequest()->getParam("id_product");
        $product = Mage::getModel("catalog/product")->load($idProduct);
        try {
            if($product->getTypeId()=="simple") {
                $product->delete();
            }else{
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);
                foreach($childProducts as $child){
                    //TODO: wtf? there are bulk delete actions we can use without triggering a full load of the product
                    $childProduct=Mage::getModel("catalog/product")->load($child->getId());
                    $childProduct->delete();
                }
                $product->delete();
            }
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }


    /**
     * validates string not empty
     * @param $string
     */
    private function validateArrayKeyNotNull($key, $array){
        return isset($array[$key]) && $array[$key] != null && trim($array[$key]) != "" && trim(strtolower($array[$key])) !== "null";
    }

    /** validate productsku is not unique
     * @return bool
     */
    private function isProductSkuUnique($sku){

        return true;
    }

    /***
     * returns true is product data is valid, and throws exception if not
     * @param $data
     * @return bool
     * @throws Exception
     */
    protected function isProductDataValid($data){
        $stringError="";
        foreach($data as $key=>$value){
            switch ($key){
                case 'sku':
                case 'name':
                case 'description':
                case 'full_price':
                    if (!$this->validateArrayKeyNotNull($key, $data)){
                        $stringError .= "Product $key can not be empty, ";
                    }
                    break;
                case 'inventories':
                    if(is_array($data["inventories"])) {
                        if (!$this->_checkAttribute("quantity", $data["inventories"]) || trim($data["inventories"]["quantity"]) == "") {
                            $stringError .= "Product quantity can not be empty, ";
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        if($stringError != "") {
            $this->_criticalCustom($stringError, "400");
        }
        return true;
    }

    /***
     *
     * @deprecated Please use self::isProductDataValid($data) instead
     * @param $data
     * @param $skuCheck
     * @return string
     * @throws Exception
     */
    protected function _validateDataBeforeUpdate($data, $skuCheck){
        $stringError = '';
        try{
            $this->isProductDataValid($data);
            $this->isProductSkuUnique($data['sku']);
        }
        catch(Mage_Core_Exception $e){
            $stringError = $e->getMessage();
        }
        //bad way to do this, but code is currently setup to throw default exceptions
        //deprecated so that we don't need to use this anymore
        catch(Exception $e){
           throw $e;
        }
        return $stringError;
    }


    /** @var Ant_Api_Helper_Data */
    private $_helper;

    /***
     * @return Ant_Api_Helper_Data
     */
    private function getAntHelper(){
        if (!$this->_helper){
            $this->_helper = Mage::helper("ant_api");
        }
        return $this->_helper;
    }

    private $_excludedFields = array(
        "id",
        "images",
        "inventories",
        "full_price",
        "tags",
        "tax",
        "meta",
        "manage_stock",
        "special_price",
        "product_options",
        "categories",
        "product_type",
        "handle"
    );


    protected function _update(array $data){


        try {
            $idProduct = (int) $this->getRequest()->getParam("id_product");
            if (!$idProduct){
                throw new Mage_Core_Exception('Id Not specified');
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel("catalog/product")->load($idProduct);
            if(!$product->getId()){
                $this->_criticalCustom("Product not found.", "404");
            }
            $this->isProductDataValid($data);
            $this->isProductSkuUnique($data['sku']);

            //TODO: should use a sync map here, and only have exceptions where it calls outside of the map (Map should be settable in config)
            //TODO: change below $product->setData to $product->setDataUsingMethod to ensure that any overriden functions are honoured
            $originalData = $data;

            $includedData = array_diff_key($data, array_flip($this->_excludedFields));
            $product->addData($includedData);

            if ($this->_checkAttribute("tax", $data)) {
                $product->setTaxClassId($data["tax"]);
            }

            if(!$product->getTaxClassId()){
                $taxDefault = $this->getAntHelper()->getConfigTaxAnt();
                $product->setTaxClassId($taxDefault);
            }

            if ($this->_checkAttribute("full_price", $data)) {
                $product->setPrice($data["full_price"]);
            }

            if ( $this->getAntHelper()->getShouldSyncSpecialPriceFromAntHQ() && $this->_checkAttribute("special_price", $data)) {
                $product->setSpecialPrice($data["special_price"]);
            }

            if ($this->_checkAttribute("tags", $data)) {
                if (is_array($data["tags"])) {
                    $this->getAntHelper()->setTagsProduct($data["tags"], $product);
                }
            }

            if ($this->_checkAttribute("meta", $data)) {
                $stringMeta = "";
                foreach ($data["meta"] as $_meta) {
                    $stringMeta .= $_meta . ",";
                }
                $product->setMetaKeyword($stringMeta);
                //$product->setMetaDescription($stringMeta);
            }

            if($this->_checkAttribute("handle", $data)) {
                $handle = $data["handle"];
                $urlRewrite = $this->getAntHelper()->rewriteUrl($data["name"], $handle);
                $product->setUrlKey($urlRewrite);
            }

            if ($this->_checkAttribute("categories", $data)) {
                $categories = $data["categories"];
                $arrayCategoryIds = array();
                foreach ($categories as $_cate) {
                    $arrayCategoryIds[] = $this->getAntHelper()->getCategory($_cate);
                }
                $product->setCategoryIds($arrayCategoryIds);
            }

            //QTY
            if ($this->_checkAttribute("inventories", $data)) {
                if ($this->_checkAttribute("quantity", $data["inventories"])) {
                    $qty = $data["inventories"]["quantity"];
                    $managerStock = 1;
                    if ($this->_checkAttribute("manage_stock", $data)) {
                        $managerStock = $data["manage_stock"];
                    }
                    //TODO: add in manage stock as expected
                    $stockData = $product->getStockData();

                    $minQty = (isset($stockData['min_qty']))? $stockData['min_qty'] : 0;

                    $stockData['qty']         = $qty;
                    $stockData['is_in_stock'] = ($qty > $minQty)? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK : Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;

                    $product->setStockData($stockData);
                    //$product->setStockData(array(
                    //    'use_config_manage_stock' => 0,
                    //    'manage_stock' => $managerStock,
                    //    'is_in_stock' => 1,
                    //    'qty' => $qty
                    //));
                }
            }
            //Image

            if ($this->_checkAttribute("images", $data)) {
                $count = 0;
                $dataImage = $data["images"];
                foreach ($dataImage as $_image) {
                    if ($this->_checkAttribute("id", $_image) && $this->_checkAttribute("url",
                            $_image) && $this->_checkAttribute("position", $_image)
                    ) {
                        $urlImageInput = $_image["url"];
                        $position = $_image["position"];
                        $arrayImageInfor = $this->getAntHelper()->setImageProduct($urlImageInput);
                        $nameImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
                        $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
                        $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
                        $mimeType = "";
                        if (file_exists("$absolutePathImage")) {
                            $pathInfo = pathinfo("$absolutePathImage");
                            switch ($pathInfo['extension']) {
                                case 'png':
                                    $mimeType = 'image/png';
                                    break;
                                case 'jpg':
                                    $mimeType = 'image/jpeg';
                                    break;
                                case 'gif':
                                    $mimeType = 'image/gif';
                                    break;
                            }
                        }
                        $items = $mediaApi->items($idProduct, Mage_Core_Model_App::ADMIN_STORE_ID);
                        $isEmptyImage = true;
                        foreach ($items as $item) {
                            if ($item["position"] == $position) {
                                $isEmptyImage = false;
                                $count++;
                                $dataImage = array(
                                    'file' => array(
                                        'name' => $nameImage,
                                        'content' => base64_encode(file_get_contents($absolutePathImage)),
                                        'mime' => $mimeType
                                    ),
                                    'label' => $item["label"],
                                    'position' => $position,
                                    'types' => $item["types"],
                                    'exclude' => 0
                                );
                                $mediaApi->update($idProduct, $item['file'], $dataImage,
                                    Mage_Core_Model_App::ADMIN_STORE_ID);
                            }
                        }
                        if ($isEmptyImage) {
                            if ($count == 0) {
                                $product->addImageToMediaGallery($absolutePathImage,
                                    array('image', 'thumbnail', 'small_image'), true, false);
                            } else {
                                $product->addImageToMediaGallery($absolutePathImage, null, true, false);
                            }
                            $count++;
                        }
                    }
                }
            }

            switch($product->getTypeId()){
                case 'configurable':
                    $attributeSetId = $product->getAttributeSetId();

                    if ($this->_checkAttribute("product_options", $data)) {
                        $product_options = $data["product_options"];
                        /** @var Ant_Api_Helper_Formatting $formattingHelper */
                        $formattingHelper = Mage::helper('ant_api/format');

                        foreach ($product_options as $p_opt) {
                            $nameAttribute = $p_opt["name"];
                            $attributeCode = $formattingHelper->createAttributeNameSlugFromLabel($nameAttribute);
                            $p_values = $p_opt["values"];
                            $valStringArray = array();
                            foreach ($p_values as $_val) {
                                $v = $_val["name"];
                                $valStringArray[] = $v;
                            }
                            if (!$this->getAntHelper()->checkExistAttribute($attributeCode)) {
                                $this->getAntHelper()->createAttribute($nameAttribute, $attributeCode, -1, -1, -1,
                                    $valStringArray);
                            } else {
                                $this->getAntHelper()->updateAttributeValue($attributeCode, $valStringArray);
                            }
                        }
                    }
                    //assign simple product to configruable product
                    $dataVariants = $data["variants"];

                    $errorOnChildProduct = false;
                    $arrayProductIds = array();
                    if ($dataVariants && is_array($dataVariants)) {
                        foreach ($dataVariants as $_variant) {
                            $id_variant = $_variant["id"];
                            $arrayProductIds[] = $this->setSimpleProductToConfigruableProduct($id_variant, $_variant, $attributeSetId);
                        }
                        $childProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
                        $idsToDelete = array();
                        foreach($childProducts as $child){
                            if(!in_array($child->getId(),$arrayProductIds)) {
                                $idsToDelete[] = $child->getId();
                            }
                        }
                        foreach ($idsToDelete as $productId) {
                            $product = Mage::getSingleton('catalog/product')->load($productId);
                            $product->delete();
                        }

                        $arrayAttributeToset = array();
                        $firstData = $dataVariants[0]["options"];
                        foreach ($firstData as $first_options) {
                            $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $first_options["code"]);
                            $arrayAttributeToset[] = $attribute->getId();
                        }
                        //$product->getTypeInstance()->setUsedProductAttributeIds($arrayAttributeToset); //attribute ID of attribute 'color' in my store
                        $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray();
                        $product->setCanSaveConfigurableAttributes(true);
                        $product->setConfigurableAttributesData($configurableAttributesData);

                        $configurableProductsData = array();
                        foreach ($arrayProductIds as $key => $value) {
                            $configurableProductsData[$value] = array(
                                //['value'] = id of a simple product associated with this configurable
                            );
                        }

                        $product->setConfigurableProductsData($configurableProductsData);
                    }
                    break;
                case 'simple':
                default:
                    break;

            }
            $product->save();

        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
    public function setSimpleProductToConfigruableProduct($idProduct,$data,$attributeSetId){
        $product = Mage::getModel("catalog/product")->load($idProduct);

        /** @var Ant_Api_Helper_Data $helperAnt */
        $helperAnt = Mage::helper("ant_api");

        $isCreate=false;
        if(!$product->getId()){
            $isCreate=true;
            $product=Mage::getModel("catalog/product");
            $product->setTypeId("simple");
            $product->setWebsiteIds($helperAnt->getProductWebsiteIds()); //website ID the product is assigned to, as an array
            $product->setCreatedAt(strtotime('now'));
            $product->setStatus(1);
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG);
        }
        $arrayToExclude = array("id","product_name","images", "inventories", "tax", "full_price");
        foreach($data as $key=>$value){
            if(!in_array($key,$arrayToExclude)){
                $product->setData($key, $value);
            }
        }
        if($this->_checkAttribute("product_name",$data)) {
            $product->setName($data["product_name"]);
        }
        if($this->_checkAttribute("tax",$data)) {
            $product->setTaxClassId($data["tax"]);
        }
        if(!$product->getTaxClassId()){
            $taxDefault = Mage::helper("ant_api")->getConfigTaxAnt();
            $product->setTaxClassId($taxDefault);
        }
        if($this->_checkAttribute("full_price",$data)){
            $product->setPrice($data["full_price"]);
        }
        $product->setAttributeSetId($attributeSetId); //ID of a attribute set named 'default'
        if ($this->_checkAttribute("options", $data)) {
            $codeArray = $data["options"];
            foreach ($codeArray as $_item) {
                $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$_item["code"]);
                $attr = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                $value=$attr->setStoreId(0)->getSource()->getOptionId($_item["value"]);
                $product->setData($_item["code"],$value);
            }
        }
        if($this->_checkAttribute("inventories",$data)) {
            if($this->_checkAttribute("quantity",$data["inventories"])) {
                $qty = $data["inventories"]["quantity"];
                //TODO: add in manage stock as expected
                $stockData = $product->getStockData();
                $minQty = (isset($stockData['min_qty']))? $stockData['min_qty'] : 0;

                $stockData['qty']         = $qty;
                $stockData['is_in_stock'] = ($qty > $minQty)? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK : Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;

                $product->setStockData($stockData);
//                    $product->setStockData(array(
//                    'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                    'manage_stock' => 1, //manage stock
//                    'is_in_stock' => 1, //Stock Availability
//                    'qty' => $qty
//                ));
            }
        }
        $product->save();
        $id = $product->getId();
        return $id;
    }
}