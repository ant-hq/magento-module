<?php
class Ant_Api_Model_Api2_Product_Rest_Guest_V1 extends Ant_Api_Model_Api2_Product
{
    protected $_arrayIdSimpleProduct=array();
    protected function _retrieveCollection(){

        $modelProduct = Mage::getModel('catalog/product');
        $collectionProduct = $modelProduct->getCollection();
        $paramsSearch=$this->getRequest()->getParams();
        $page=$this->getRequest()->getParam("page");
        $limit=$this->getRequest()->getParam("limit");
        $pageSize=20;
        if(count($paramsSearch) > 0){
            $arrayNotInclude=array("api_type","model","ant_api/api2_product","type","action_type","page","limit");
            foreach($paramsSearch as $_key => $value){
                if(!in_array($_key,$arrayNotInclude)) {
                    $collectionProduct=$collectionProduct->addAttributeToFilter($_key, array("like" => "%" . $value . "%"));
                }
            }
        }
        if($page != ""){
            if($limit == "") {
                $limit=20;
            }
            $pageSize = intval($limit);
            $collectionProduct = $modelProduct
                ->getCollection()
                ->setCurPage($page)
                ->setPageSize($pageSize);
            //todo: these database select changes shouldn't be done in a model, they need to be moved to a custom resource model
            $collectionProduct->getSelect()->joinLeft(array('link_table' => Mage::getConfig()->getTablePrefix().'catalog_product_super_link'),
                'link_table.product_id = e.entity_id',
                array('product_id')
            );
            $collectionProduct->getSelect()->where('link_table.product_id IS NULL and (e.type_id="simple" OR e.type_id="configurable")');
        }else{
            $page = 1;
        }
        $products = array();
        foreach($collectionProduct as $_product){
            $idProduct = $_product->getId();
            switch ($_product->getTypeId()) {
                case "simple":
                    if (!Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($idProduct)) {
                        try {
                            /* TODO: Create custom Ant Product model and populate the data with the response */
                            $newProductData = Mage::helper("ant_api")->setTheHashProductSimple($idProduct);
                        } catch (\Exception $exception) {
                            $newProductData = array(
                                'id' => $idProduct,
                                'error' => $exception->getMessage()
                            );
                        }
                        $products[] = $newProductData;
                    }
                    break;
                case "configurable":
                    try {
                        /* TODO: Create custom Ant Configurable Product model and populate the data with the response */
                        $newProductData = Mage::helper("ant_api")->setTheHashConfigruableProduct($idProduct);
                    } catch (\Exception $exception) {
                        $newProductData = array(
                            'id' => $idProduct,
                            'error' => $exception->getMessage()
                        );
                    }
                    $products[] = $newProductData;
                    break;
            }
        }
        $countProduct = Mage::helper("ant_api")->getCountProductInStore();
        $pageCount=intval($countProduct / $pageSize)+1;
        $arrayParent=array();
        $arrayParent["products"] = $products;
        $arrayParent["pagination"] = array();
        $arrayParent["pagination"]["total_products"] = $countProduct;
        $arrayParent["pagination"]["total_pages"] = $pageCount;
        $arrayParent["pagination"]["current_page"] = intval($page);
        $arrayParent["pagination"]["page_size"] = $pageSize;
        return $arrayParent;
    }
    protected function _criticalCustom($message, $code = null)
    {
        throw new Exception($message,$code);
    }
    protected function _checkAttribute($attribute_name,$data){
        if(is_array($data[$attribute_name])){
            if (!isset($data[$attribute_name])) {
                return false;
            }
        }else {
            if (!isset($data[$attribute_name]) || trim($data[$attribute_name]) == "") {
                return false;
            }
        }
        return true;
    }
    protected function _validateImages($attribute_name,$data){
        $stringError="";
        if(!isset($data[$attribute_name]) || $data[$attribute_name] == "" ){
            $stringError="Properties image is empty or null . Please check your data";
        }
        if($stringError != "") {
            $this->_criticalCustom($stringError, "400");
        }
        return $stringError;
    }
    protected function _validateDataBeforeUpdate($data){
        $stringError="";
        $product=Mage::getModel("catalog/product")->loadByAttribute("sku",$data["sku"]);
        if(!$this->_checkAttribute("product_type",$data)){
            $stringError.="Product Type can not be empty";
        }
        if(!$this->_checkAttribute("name",$data)){
            $stringError.="Product name can not be empty , ";
        }
        if(!$this->_checkAttribute("sku",$data)){
            $stringError.="Product sku can not be empty , ";
        }
        if(!$this->_checkAttribute("description",$data)){
            $stringError.="Product description can not be empty , ";
        }
        if(!$this->_checkAttribute("full_price",$data)){
            $stringError.="Product full_price can not be empty , ";
        }
        if(isset($data["inventories"])) {
            if (!$this->_checkAttribute("quantity",$data["inventories"])) {
                $stringError .= "Product quantity can not be empty , ";
            }
        }
        if($product){
            $stringError.="Product has sku ".$data['sku']." has existed.Please check your sku data on product listing";
        }
        if($stringError != "") {
            $this->_criticalCustom($stringError, "400");
        }
        return $stringError;
    }
    protected function _validateVariantBeforeUpdate($data){
        $stringError="";
        $product=Mage::getModel("catalog/product")->loadByAttribute("sku",$data["sku"]);
        if(!$this->_checkAttribute("product_name",$data)){
            $stringError="Product variant name can not be empty , ";
        }
        if(!$this->_checkAttribute("sku",$data)){
            $stringError.="Product variant sku can not be empty , ";
        }
        if(!$this->_checkAttribute("full_price",$data)){
            $stringError.="Product variant full_price can not be empty , ";
        }
        if(isset($data["inventories"])) {
            if (!$this->_checkAttribute("quantity", $data["inventories"])) {
                $stringError .= "Product variant quantity can not be empty , ";
            }
        }
        if($product){
            $stringError.="Product variant has sku ".$data['sku']." has existed.Please check your sku data on product listing";
        }
        if($stringError != "") {
            $this->_criticalCustom($stringError, "400");
        }
        return $stringError;
    }
    protected function _create(array $data){
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $helperAnt = Mage::helper("ant_api");
            $defaultAttributeSetId = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getDefaultAttributeSetId();
            $product = Mage::getModel('catalog/product');
            $arrayToExclude=array("id","images","inventories","full_price","tags","tax","meta","manage_stock","special_price","product_options","categories","product_type","handle");
            $this->_validateDataBeforeUpdate($data);
            $product_type=$data["product_type"];
            if($product_type=="simple") {
                if($this->_validateDataBeforeUpdate($data)=="") {
                    foreach($data as $key=>$value){
                        if(!in_array($key,$arrayToExclude)) {
                            $product->setData($key, $value);
                        }
                    }
                    if (isset($data["tax"])) {
                        $product->setTaxClassId($data["tax"]);
                    } else {
                        $tax_class = $helperAnt->getConfigTaxAnt();
                        $product->setTaxClassId($tax_class);
                    }
                    $product->setPrice($data["full_price"]);
                    $product->setData("special_price",$data["special_price"]);
                    $product->setMetaTitle($data["name"]);
                    $stringMeta="";
                    foreach($data["meta"] as $_meta){
                        $stringMeta.=$_meta.",";
                    }
                    $product->setMetaKeyword($stringMeta);
                    //$product->setMetaDescription($stringMeta);
                    /** @var Ant_Api_Helper_Data $helperAnt */
                    $helperAnt = Mage::helper("ant_api");

                    $product->setWebsiteIds($helperAnt->getProductWebsiteIds()); //website ID the product is assigned to, as an array
                    $product->setAttributeSetId($defaultAttributeSetId); //ID of a attribute set named 'default'
                    $product->setTypeId('simple'); //product type
                    $product->setCreatedAt(strtotime('now')); //product creation time
                    $product->setStatus(1); //product status (1 - enabled, 2 - disabled)
                    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                    if($this->_checkAttribute("handle", $data)) {
                        $handle = $data["handle"];
                        $urlRewrite = $helperAnt->rewriteUrl($data["name"], $handle);
                        $product->setUrlKey($urlRewrite);
                    }
                    $dataImage = $data["images"];
                    $count = 0;
                    $product->setMediaGallery(array('images' => array(), 'values' => array()));
                    $isErrorImage=false;
                    if(isset($data["images"])) {
                        if (count($dataImage) > 0) {
                            foreach ($dataImage as $_image) {
                                if ($this->_validateImages("url", $_image) == "" && $this->_validateImages("position",
                                        $_image) == ""
                                ) {
                                    $urlImageInput = $_image["url"];
                                    $position = $_image["position"];
                                    $arrayImageInfor = $helperAnt->setImageProduct($urlImageInput);
                                    $nameImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
                                    $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
                                    if ($count == 0) {
                                        $product->addImageToMediaGallery($absolutePathImage,
                                            array('image', 'thumbnail', 'small_image'), true, false);
                                    } else {
                                        $product->addImageToMediaGallery($absolutePathImage, null, true, false);
                                    }
                                    $count++;
                                } else {
                                    $isErrorImage = true;
                                    break;
                                }
                            }
                        }
                    }
                    if(isset($data["categories"])) {
                        $categories = $data["categories"];
                        $arrayCategoryIds = array();
                        foreach ($categories as $_cate) {
                            $arrayCategoryIds[] = $helperAnt->getCategory($_cate);
                        }
                        if (count($arrayCategoryIds) > 0) {
                            $product->setCategoryIds($arrayCategoryIds);
                        } else {
                            $product->setCategoryIds($helperAnt->getRootCategory());
                        }
                    }else{
                        $product->setCategoryIds($helperAnt->getRootCategory());
                    }


                    $qty = (isset($data["inventories"]))? $data["inventories"]["quantity"] : 0;
                    $manageStock = ($this->_checkAttribute("manage_stock", $data))? (int) filter_var($data["manage_stock"],FILTER_VALIDATE_BOOLEAN) : 1;

                    $helper = Mage::helper('ant_api/product_inventory_data');
                    /** @var Ant_Api_Helper_Product_Inventory_Data $helper */
                    $stockData = $helper->prepareDefaultStockArray($qty, $manageStock);

                    $product->setStockData($stockData);

                    if($isErrorImage==false) {
                        Mage::register('is_new_product_api',"noevent");
                        $product->save();
                        if(isset($data["tags"])) {
                            if (is_array($data["tags"])) {
                                $helperAnt->setTagsProduct($data["tags"], $product);
                            }
                        }
                        return $product->getId()."|simple";
                    }
                    unset($product);
                }
            }
            if($product_type=="configurable") {
                if ($this->_validateDataBeforeUpdate($data) == "") {
                    $attributeSetId = $defaultAttributeSetId;
                    foreach($data as $key=>$value){
                        if(!in_array($key,$arrayToExclude)) {
                            $product->setData($key, $value);
                        }
                    }
                    /** @var Ant_Api_Helper_Data $helperAnt */
                    $helperAnt = Mage::helper("ant_api");

                    $product->setWebsiteIds($helperAnt->getProductWebsiteIds()); //website ID the product is assigned to, as an array
                    $product->setAttributeSetId($attributeSetId); //ID of a attribute set named 'default'
                    $product->setTypeId("configurable"); //product type
                    $product->setCreatedAt(strtotime('now')); //product creation time
                    $product->setStatus(1); //product status (1 - enabled, 2 - disabled)
                    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                    $product->setPrice($data["full_price"]);
                    $product->setData("special_price",$data["special_price"]);
                    $product->setMetaTitle($data["name"]);
                    if($this->_checkAttribute("handle", $data)) {
                        $handle = $data["handle"];
                        $urlRewrite = $helperAnt->rewriteUrl($data["name"], $handle);
                        $product->setUrlKey($urlRewrite);
                    }
                    $stringMeta="";
                    foreach($data["meta"] as $_meta){
                        $stringMeta.=$_meta.",";
                    }
                    $product->setMetaKeyword($stringMeta);
                    //$product->setMetaDescription($stringMeta);
                    $product->setMediaGallery(array('images' => array(), 'values' => array()));
                    if (isset($data["tax"])) {
                        $product->setTaxClassId($data["tax"]);
                    } else {
                        $tax_class = $helperAnt->getConfigTaxAnt();
                        $product->setTaxClassId($tax_class);
                    }
                    $dataImage = $data["images"];
                    $count = 0;
                    $isErrorImage=false;
                    if(isset($data["images"])) {
                        if (count($dataImage) > 0) {
                            foreach ($dataImage as $_image) {
                                if ($this->_validateImages("url", $_image) == "" && $this->_validateImages("position",
                                        $_image) == ""
                                ) {
                                    $urlImageInput = $_image["url"];
                                    $position = $_image["position"];
                                    $arrayImageInfor = $helperAnt->setImageProduct($urlImageInput);
                                    $nameImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
                                    $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
                                    //$product->addImageToMediaGallery($absolutePathImage, array('image', 'thumbnail', 'small_image'), false, false);
                                    if ($count == 0) {
                                        $product->addImageToMediaGallery($absolutePathImage,
                                            array('image', 'thumbnail', 'small_image'), true, false);
                                    } else {
                                        $product->addImageToMediaGallery($absolutePathImage, null, true, false);
                                    }
                                    $count++;
                                } else {
                                    $isErrorImage = true;
                                    break;
                                }
                            }
                        }
                    }

                    if(isset($data["categories"])) {
                        $categories = $data["categories"];
                        $arrayCategoryIds = array();
                        foreach ($categories as $_cate) {
                            $arrayCategoryIds[] = $helperAnt->getCategory($_cate);
                        }
                        if (count($arrayCategoryIds) > 0) {
                            $product->setCategoryIds($arrayCategoryIds);
                        } else {
                            $product->setCategoryIds($helperAnt->getRootCategory());
                        }
                    }else{
                        $product->setCategoryIds($helperAnt->getRootCategory());
                    }
                    //TODO: add in manage stock as expected
                    $qty = (isset($data["inventories"]) && isset($data["inventories"]["quantity"]))? (float) $data["inventories"]["quantity"] : 0;
                    $manageStock = ($this->_checkAttribute("manage_stock", $data))? (int) filter_var($data["manage_stock"],FILTER_VALIDATE_BOOLEAN) : 1;

                    $helper = Mage::helper('ant_api/product_inventory_data');
                    /** @var Ant_Api_Helper_Product_Inventory_Data $helper */
                    $stockData = $helper->prepareDefaultStockArray($qty, $manageStock);
                    $product->setStockData($stockData);

//                    if(isset($data["inventories"])) {
//                        $qty = $data["inventories"]["quantity"];
//                        $product->setStockData(array(
//                                'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                                'manage_stock' => $data["manage_stock"], //manage stock
//                                'is_in_stock' => 1, //Stock Availability
//                                'qty' => $qty //qty
//                            )
//                        );
//                    }else{
//                        $product->setStockData(array(
//                                'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                                'manage_stock' => 0, //manage stock
//                                'is_in_stock' => 1, //Stock Availability
//                                'qty' => 0 //qty
//                            )
//                        );
//                    }
                    /** @var Ant_Api_Helper_Formatting $formattingHelper */
                    $formattingHelper = Mage::helper('ant_api/format');

                    //Check Product Options is Exist Or Not
                    if($this->_checkAttribute("product_options",$data)) {
                        $product_options = $data["product_options"];
                        foreach ($product_options as $p_opt) {
                            $nameAttribute = $p_opt["name"];
                            $attributeCode = $formattingHelper->createAttributeNameSlugFromLabel($nameAttribute);
                            $attribute_id=Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$attributeCode);
                            $attr_object = Mage::getModel('catalog/resource_eav_attribute')->load($attribute_id);
                            $attributes = Mage::getModel('catalog/product_attribute_api')->items($attributeSetId);
                            $isBelongtoDefault = false;
                            foreach($attributes as $_attribute){
                                if($_attribute["attribute_id"] == $attribute_id){
                                    $isBelongtoDefault = true;
                                    break;
                                }
                            }
                            if($isBelongtoDefault == false) {
                                $model_attribute_set = Mage::getModel('eav/entity_setup', 'core_setup');
                                $attr_code = $attr_object->getAttributeCode();
                                $model_attribute_set->addAttributeToSet(
                                    'catalog_product', 'Default', 'General', $attr_code
                                );
                            }
                            $p_values = $p_opt["values"];
                            $valStringArray = array();
                            foreach ($p_values as $_val) {
                                $v = $_val["name"];
                                $valStringArray[] = $v;
                            }
                            if (!$helperAnt->checkExistAttribute($attributeCode)) {
                                $helperAnt->createAttribute($nameAttribute, $attributeCode, -1, -1, -1,
                                    $valStringArray);
                            }
                            else {
                                $helperAnt->updateAttributeValue($attributeCode, $valStringArray);
                            }
                        }
                    }
                    //Get Configruable Product
                    $dataVariants = $data["variants"];
                    $arrayProductIds = array();
                    $errorOnChildProduct=false;
                    $tagsParent = "";
                    if(isset($data["tags"])) {
                        $tagsParent = $data["tags"];
                    }
                    if ($dataVariants && is_array($dataVariants)) {
                        foreach ($dataVariants as $_variant) {
                            $arrayProductIds[] = $this->setSimpleProductToConfigruableProduct($_variant, $attributeSetId,$tagsParent);
                        }
                        Mage::register('is_new_product_api',"noevent");
                        $arrayAttributeToset = array();
                        $firstData = $dataVariants[0]["options"];
                        foreach ($firstData as $first_options) {
                            $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $first_options["code"]);
                            $arrayAttributeToset[] = $attribute->getId();
                        }
                        $product->getTypeInstance()->setUsedProductAttributeIds($arrayAttributeToset); //attribute ID of attribute 'color' in my store
                        $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray();

                        $product->setCanSaveConfigurableAttributes(true);
                        $product->setConfigurableAttributesData($configurableAttributesData);
                        $configurableProductsData = array();
                        foreach ($arrayProductIds as $key => $value) {
                            if($value==false)
                            {
                                $errorOnChildProduct=true;
                                break;
                            }
                            $configurableProductsData[$value] = array( //['value'] = id of a simple product associated with this configurable

                            );
                        }
                        $product->setConfigurableProductsData($configurableProductsData);
                    }
                    if ($isErrorImage == false && $errorOnChildProduct==false) {
                        $product->save();
                        if(isset($data["tags"])) {
                            if (is_array($data["tags"])) {
                                $helperAnt->setTagsProduct($data["tags"], $product);
                            }
                        }
                        return $product->getId()."|configurable";
                    }
                    unset($product);
                }
            }
        }
        catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
    public function setSimpleProductToConfigruableProduct($data,$attributeSetId,$tagsParent){
        Mage::register('is_new_product_api',"noevent");
        /** @var Ant_Api_Helper_Data $helperAnt */
        $helperAnt = Mage::helper("ant_api");
        if(isset($data["id"])){
            $product=Mage::getModel("catalog/product")->load($data["id"]);
            if($product->getId()){
                $this->setSimpleProductToConfigruableProductCaseUpdate($data["id"],$data,$tagsParent);
            }
            else{
                if ($this->_validateVariantBeforeUpdate($data) == "") {
                    $product = Mage::getModel("catalog/product");
                    $arrayToExclude = array("id","product_name","images", "inventories", "tax", "full_price");
                    foreach ($data as $key => $value) {
                        if (!in_array($key, $arrayToExclude)) {
                            $product->setData($key, $value);
                        }
                    }
                    $product->setName($data["product_name"]);
                    $product->setWebsiteIds($helperAnt->getProductWebsiteIds()); //website ID the product is assigned to, as an array
                    $product->setAttributeSetId($attributeSetId); //ID of a attribute set named 'default'
                    $product->setTypeId("simple"); //product type
                    $product->setCreatedAt(strtotime('now')); //product creation time
                    $product->setStatus(1); //product status (1 - enabled, 2 - disabled)
                    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG);
                    /*if($this->_checkAttribute("handle", $data)) {
                        $handle = $data["handle"];
                        $urlRewrite = Mage::helper("ant_api")->rewriteUrl($data["product_name"], $handle);
                        $product->setUrlKey($urlRewrite);
                    }*/
                    if ($this->_checkAttribute("tax", $data)) {
                        $product->setTaxClassId($data["tax"]);
                    }else{
                        $tax_class = Mage::helper("ant_api")->getConfigTaxAnt();
                        $product->setTaxClassId($tax_class);
                    }
                    if ($this->_checkAttribute("full_price", $data)) {
                        $product->setPrice($data["full_price"]);
                    }
                    if ($this->_checkAttribute("options", $data)) {
                        $codeArray = $data["options"];
                        foreach ($codeArray as $_item) {
                            $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$_item["code"]);
                            $attr = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                            $value=$attr->setStoreId(0)->getSource()->getOptionId($_item["value"]);
                            $attribute_code = $attr->getAttributeCode();
                            $product->setData($attribute_code,$value);
                        }
                    }

                    //TODO: add in manage stock as expected
                    $qty = ($this->_checkAttribute("inventories", $data) && $this->_checkAttribute("quantity", $data["inventories"]))? (float) $data["inventories"]["quantity"] : 0;
                    $manageStock = ($this->_checkAttribute("manage_stock", $data))? (int) filter_var($data["manage_stock"],FILTER_VALIDATE_BOOLEAN) : 1;

                    $helper = Mage::helper('ant_api/product_inventory_data');
                    /** @var Ant_Api_Helper_Product_Inventory_Data $helper */
                    $stockData = $helper->prepareDefaultStockArray($qty, $manageStock);
                    $product->setStockData($stockData);

//                    if ($this->_checkAttribute("inventories", $data)) {
//                        if ($this->_checkAttribute("quantity", $data["inventories"])) {
//                            $qty = $data["inventories"]["quantity"];
//                            $product->setStockData(array(
//                                'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                                'manage_stock' => 1, //manage stock
//                                'is_in_stock' => 1, //Stock Availability
//                                'qty' => $qty //qty
//                            ));
//                        }
//                    }else{
//                        $product->setStockData(array(
//                                'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                                'manage_stock' => 0, //manage stock
//                                'is_in_stock' => 1, //Stock Availability
//                                'qty' => 0 //qty
//                            )
//                        );
//                    }
                    $product->save();
                    return $product->getId();
                }
                return false;
            }
        }else {
            if ($this->_validateVariantBeforeUpdate($data) == "") {
                $product = Mage::getModel("catalog/product");
                $arrayToExclude = array("id","product_name","images", "inventories", "tax", "full_price");
                foreach ($data as $key => $value) {
                    if (!in_array($key, $arrayToExclude)) {
                        $product->setData($key, $value);
                    }
                }
                $product->setName($data["product_name"]);
                $product->setWebsiteIds($helperAnt->getProductWebsiteIds()); //website ID the product is assigned to, as an array
                $product->setAttributeSetId($attributeSetId); //ID of a attribute set named 'default'
                $product->setTypeId("simple"); //product type
                $product->setCreatedAt(strtotime('now')); //product creation time
                $product->setStatus(1); //product status (1 - enabled, 2 - disabled)
                $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG);
                if ($this->_checkAttribute("tax", $data)) {
                    $product->setTaxClassId($data["tax"]);
                }else{
                    $tax_class = Mage::helper("ant_api")->getConfigTaxAnt();
                    $product->setTaxClassId($tax_class);
                }
                if ($this->_checkAttribute("full_price", $data)) {
                    $product->setPrice($data["full_price"]);
                }
                if ($this->_checkAttribute("options", $data)) {
                    $codeArray = $data["options"];
                    foreach ($codeArray as $_item) {
                        $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$_item["code"]);
                        $attr = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                        $value=$attr->setStoreId(0)->getSource()->getOptionId($_item["value"]);
                        $attribute_code = $attr->getAttributeCode();
                        $product->setData($attribute_code,$value);
                    }
                }
                //TODO: add in manage stock as expected
                $qty = ($this->_checkAttribute("inventories", $data) && $this->_checkAttribute("quantity", $data["inventories"]))? (float) $data["inventories"]["quantity"] : 0;
                $manageStock = ($this->_checkAttribute("manage_stock", $data))? (int) filter_var($data["manage_stock"],FILTER_VALIDATE_BOOLEAN) : 1;

                $helper = Mage::helper('ant_api/product_inventory_data');
                /** @var Ant_Api_Helper_Product_Inventory_Data $helper */
                $stockData = $helper->prepareDefaultStockArray($qty, $manageStock);
                $product->setStockData($stockData);

//                if ($this->_checkAttribute("inventories", $data)) {
//                    if ($this->_checkAttribute("quantity", $data["inventories"])) {
//                        $qty = $data["inventories"]["quantity"];
//                        $product->setStockData(array(
//                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                            'manage_stock' => 1, //manage stock
//                            'is_in_stock' => 1, //Stock Availability
//                            'qty' => $qty //qty
//                        ));
//                    }
//                }else{
//                    $product->setStockData(array(
//                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                            'manage_stock' => 0, //manage stock
//                            'is_in_stock' => 1, //Stock Availability
//                            'qty' => 0 //qty
//                        )
//                    );
//                }
                $product->save();
                return $product->getId();
            }
            return false;
        }
    }
    public function setSimpleProductToConfigruableProductCaseUpdate($id_product,$data,$tagsParent){
        Mage::register('is_new_product_api',"noevent");
        if ($this->_validateVariantBeforeUpdate($data) == "") {
            $product = Mage::getModel("catalog/product")->load($id_product);
            $arrayToExclude = array("id","product_name","images", "inventories", "tax", "full_price");
            foreach ($data as $key => $value) {
                if (!in_array($key, $arrayToExclude)) {
                    $product->setData($key, $value);
                }
            }
            $product->setName($data["product_name"]);
            if ($this->_checkAttribute("tax", $data)) {
                $product->setTaxClassId($data["tax"]);
            }else{
                $tax_class = Mage::helper("ant_api")->getConfigTaxAnt();
                $product->setTaxClassId($tax_class);
            }
            if ($this->_checkAttribute("full_price", $data)) {
                $product->setPrice($data["full_price"]);
            }
            if ($this->_checkAttribute("options", $data)) {
                $codeArray = $data["options"];
                foreach ($codeArray as $_item) {
                    $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$_item["code"]);
                    $attr = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                    $value=$attr->setStoreId(0)->getSource()->getOptionId($_item["value"]);
                    $attribute_code = $attr->getAttributeCode();
                    $product->setData($attribute_code,$value);
                }
            }

            //TODO: add in manage stock as expected
            $qty = ($this->_checkAttribute("inventories", $data) && $this->_checkAttribute("quantity", $data["inventories"]))? (float) $data["inventories"]["quantity"] : 0;

            $stockData = $product->getStockData();
            $minQty = (isset($stockData['min_qty']))? $stockData['min_qty'] : 0;

            $stockData['qty']         = $qty;
            $stockData['is_in_stock'] = ($qty > $minQty)? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK : Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;

            $stockData['use_config_manage_stock'] = 0;
            $stockData['manage_stock'] = ($this->_checkAttribute("manage_stock", $data))? (int) filter_var($data["manage_stock"],FILTER_VALIDATE_BOOLEAN) : 1;

            $product->setStockData($stockData);

//            if ($this->_checkAttribute("inventories", $data)) {
//                if ($this->_checkAttribute("quantity", $data["inventories"])) {
//                    $qty = $data["inventories"]["quantity"];
//                    $product->setStockData(array(
//                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                        'manage_stock' => 1, //manage stock
//                        'is_in_stock' => 1, //Stock Availability
//                        'qty' => $qty //qty
//                    ));
//                }
//            }else{
//                $product->setStockData(array(
//                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
//                        'manage_stock' => 0, //manage stock
//                        'is_in_stock' => 1, //Stock Availability
//                        'qty' => 0 //qty
//                    )
//                );
//            }
            $product->save();
            if($tagsParent != "") {
                if (is_array($tagsParent)) {
                    /** @var Ant_Api_Helper_Data $helperAnt */
                    $helperAnt = Mage::helper("ant_api");
                    $helperAnt->setTagsProduct($tagsParent, $product);
                }
            }
            return $product->getId();
        }
        return false;
    }
}