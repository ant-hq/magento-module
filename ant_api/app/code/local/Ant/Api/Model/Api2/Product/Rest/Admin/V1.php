<?php
class Ant_Api_Model_Api2_Product_Rest_Admin_V1 extends Ant_Api_Model_Api2_Product
{
    protected $_arrayIdSimpleProduct=array();
    protected function _retrieveCollection(){

        $modelProduct = Mage::getModel('catalog/product');
        $collectionProduct = $modelProduct->getCollection();
        $paramsSearch=$this->getRequest()->getParams();
        $page=$this->getRequest()->getParam("page");
        $limit=$this->getRequest()->getParam("limit");
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
            $collectionProduct = $collectionProduct->setCurPage($page)->setPageSize($limit);
        }
        $products = array();
        foreach($collectionProduct as $_product){
            $idProduct=$_product->getId();
            switch ($_product->getTypeId()){
                case "simple":
                $products[$idProduct] = Mage::helper("ant_api")->setTheHashProductSimple($idProduct);
                break;
                case "configurable":
                    $products[$idProduct] = Mage::helper("ant_api")->setTheHashConfigruableProduct($idProduct);
                    break;
            }
        }
        return $products;
    }
    protected function _criticalCustom($message, $code = null)
    {
        throw new Exception($message,$code);
    }
    protected function _checkAttribute($attribute_name,$data){
        if(!isset($data[$attribute_name]) || $data[$attribute_name] == "" ){
            return false;
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
        if(!$this->_checkAttribute("inventories",$data)){
            $stringError.="Product inventories can not be empty , ";
        }
        if(!$this->_checkAttribute("quantity",$data["inventories"])){
            $stringError.="Product quantity can not be empty , ";
        }
        if(!$this->_checkAttribute("images",$data)){
            $stringError.="Product image can not be empty , ";
        }
        if(!$this->_checkAttribute("categories",$data)){
            $stringError.="Product Categories can not be empty , ";
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
            $stringError="Product name can not be empty , ";
        }
        if(!$this->_checkAttribute("sku",$data)){
            $stringError.="Product sku can not be empty , ";
        }
        if(!$this->_checkAttribute("full_price",$data)){
            $stringError.="Product full_price can not be empty , ";
        }
        if(!$this->_checkAttribute("inventories",$data)){
            $stringError.="Product inventories can not be empty , ";
        }
        if(!$this->_checkAttribute("quantity",$data["inventories"])){
            $stringError.="Product quantity can not be empty , ";
        }
        if($product){
            $stringError.="Product has sku ".$data['sku']." has existed.Please check your sku data on product listing";
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
            $arrayToExclude=array("id","images","inventories","full_price","tags","tax","meta","manage_stock","special_price","product_options","categories","product_type");
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
                        $product->setTaxClassId(1);
                    }
                    $product->setPrice($data["full_price"]);
                    $product->setData("special_price",$data["special_price"]);
                    $product->setMetaTitle($data["name"]);
                    $stringTags="";
                    foreach($data["tags"] as $_tags){
                        $stringTags.=$_tags.",";
                    }
                    $product->setMetaKeyword($stringTags);
                    $stringMeta="";
                    foreach($data["meta"] as $_meta){
                        $stringMeta.=$_meta.",";
                    }
                    $product->setMetaDescription($stringMeta);
                    $product->setWebsiteIds(array(1)); //website ID the product is assigned to, as an array
                    $product->setAttributeSetId($defaultAttributeSetId); //ID of a attribute set named 'default'
                    $product->setTypeId('simple'); //product type
                    $product->setCreatedAt(strtotime('now')); //product creation time
                    $product->setStatus(1); //product status (1 - enabled, 2 - disabled)
                    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                    $dataImage = $data["images"];
                    $count = 0;
                    $product->setMediaGallery(array('images' => array(), 'values' => array()));
                    $isErrorImage=false;
                    if(count($dataImage) > 0) {
                        foreach ($dataImage as $_image) {
                            if ($this->_validateImages("id", $_image) == "" && $this->_validateImages("url", $_image) == "" && $this->_validateImages("position", $_image) == "") {
                                $urlImageInput = $_image["url"];
                                $position = $_image["position"];
                                $arrayImageInfor = $helperAnt->setImageProduct($urlImageInput);
                                $nameImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
                                $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
                                if ($count == 0) {
                                    $product->addImageToMediaGallery($absolutePathImage, array('image', 'thumbnail', 'small_image'), true, false);
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
                    else{
                        $isErrorImage=true;
                        $this->_criticalCustom("attribute image is not array or empty value", "400");
                    }
                    $categories = $data["categories"];
                    $arrayCategoryIds=array();
                    foreach($categories as $_cate) {
                        $arrayCategoryIds[]=$helperAnt->getCategory($_cate);
                    }
                    $product->setCategoryIds($arrayCategoryIds);
                    $qty = $data["inventories"]["quantity"];
                    $product->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => $data["manage_stock"], //manage stock
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => $qty //qty
                        )
                    );
                    if($isErrorImage==false) {
                        $product->save();
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
                    $product->setWebsiteIds(array(1)); //website ID the product is assigned to, as an array
                    $product->setAttributeSetId($attributeSetId); //ID of a attribute set named 'default'
                    $product->setTypeId("configurable"); //product type
                    $product->setCreatedAt(strtotime('now')); //product creation time
                    $product->setStatus(1); //product status (1 - enabled, 2 - disabled)
                    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                    $product->setPrice($data["full_price"]);
                    $product->setData("special_price",$data["special_price"]);
                    $product->setMetaTitle($data["name"]);
                    $stringTags="";
                    foreach($data["tags"] as $_tags){
                        $stringTags.=$_tags.",";
                    }
                    $product->setMetaKeyword($stringTags);
                    $stringMeta="";
                    foreach($data["meta"] as $_meta){
                        $stringMeta.=$_meta.",";
                    }
                    $product->setMetaDescription($stringMeta);
                    $product->setMediaGallery(array('images' => array(), 'values' => array()));
                    if (isset($data["tax"])) {
                        $product->setTaxClassId($data["tax"]);
                    } else {
                        $product->setTaxClassId(1);
                    }
                    $dataImage = $data["images"];
                    $count = 0;
                    $isErrorImage=false;
                    foreach ($dataImage as $_image) {
                        if ($this->_validateImages("id", $_image) == "" && $this->_validateImages("url", $_image) == "" && $this->_validateImages("position", $_image) == "") {
                            $urlImageInput = $_image["url"];
                            $position = $_image["position"];
                            $arrayImageInfor = $helperAnt->setImageProduct($urlImageInput);
                            $nameImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
                            $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
                            //$product->addImageToMediaGallery($absolutePathImage, array('image', 'thumbnail', 'small_image'), false, false);
                            if ($count == 0) {
                                $product->addImageToMediaGallery($absolutePathImage, array('image', 'thumbnail', 'small_image'), true, false);
                            } else {
                                $product->addImageToMediaGallery($absolutePathImage, null, true, false);
                            }
                            $count++;
                        }else {
                            $isErrorImage = true;
                            break;
                        }
                    }
                    $qty = $data["inventories"]["quantity"];
                    $categories = $data["categories"];
                    $arrayCategoryIds=array();
                    foreach($categories as $_cate) {
                        $arrayCategoryIds[]=$helperAnt->getCategory($_cate);
                    }
                    $product->setCategoryIds($arrayCategoryIds);
                    $product->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => $data["manage_stock"], //manage stock
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => $qty //qty
                        )
                    );
                    //Check Product Options is Exist Or Not
                    if($this->_checkAttribute("product_options",$data)) {
                        $product_options = $data["product_options"];
                        foreach ($product_options as $p_opt) {
                            $nameAttribute = $p_opt["name"];
                            $p_values = $p_opt["values"];
                            $valStringArray = array();
                            foreach ($p_values as $_val) {
                                $v = $_val["name"];
                                $valStringArray[] = $v;
                            }
                            if (!$helperAnt->checkExistAttribute($nameAttribute)) {
                                $helperAnt->createAttribute($nameAttribute, $nameAttribute, -1, -1, -1,
                                    $valStringArray);
                            }
                        }
                    }
                    //Get Configruable Product
                    $dataVariants = $data["variants"];
                    $arrayProductIds = array();
                    if ($dataVariants && is_array($dataVariants)) {
                        foreach ($dataVariants as $_variant) {
                            $arrayProductIds[] = $this->setSimpleProductToConfigruableProduct($_variant, $attributeSetId);
                        }
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
                        $errorOnChildProduct=false;
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
    public function setSimpleProductToConfigruableProduct($data,$attributeSetId){
        if(isset($data["id"])){
            $this->setSimpleProductToConfigruableProductCaseUpdate($data["id"],$data);
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
                $product->setWebsiteIds(array(1)); //website ID the product is assigned to, as an array
                $product->setAttributeSetId($attributeSetId); //ID of a attribute set named 'default'
                $product->setTypeId("simple"); //product type
                $product->setCreatedAt(strtotime('now')); //product creation time
                $product->setStatus(1); //product status (1 - enabled, 2 - disabled)
                $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                if ($this->_checkAttribute("tax", $data)) {
                    $product->setTaxClassId($data["tax"]);
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
                        $product->setData($_item["code"],$value);
                    }
                }
                if ($this->_checkAttribute("inventories", $data)) {
                    if ($this->_checkAttribute("quantity", $data["inventories"])) {
                        $qty = $data["inventories"]["quantity"];
                        $product->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => 1, //manage stock
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => $qty //qty
                        ));
                    }
                }
                $product->save();
                return $product->getId();
            }
            return false;
        }
    }
    public function setSimpleProductToConfigruableProductCaseUpdate($id_product,$data){
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
                    $product->setData($_item["code"],$value);
                }
            }
            if ($this->_checkAttribute("inventories", $data)) {
                if ($this->_checkAttribute("quantity", $data["inventories"])) {
                    $qty = $data["inventories"]["quantity"];
                    $product->setStockData(array(
                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
                        'manage_stock' => 1, //manage stock
                        'is_in_stock' => 1, //Stock Availability
                        'qty' => $qty //qty
                    ));
                }
            }
            $product->save();
            return $product->getId();
        }
        return false;
    }
}