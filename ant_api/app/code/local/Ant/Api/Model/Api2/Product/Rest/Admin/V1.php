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
        if(!$this->_checkAttribute("name",$data)){
            $stringError.="Product name can not be empty , ";
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
            $defaultAttributeSetId = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getDefaultAttributeSetId();
            $product = Mage::getModel('catalog/product');
            $this->_validateDataBeforeUpdate($data);
            if($data["type"]=="simple") {
                if($this->_validateDataBeforeUpdate($data)=="") {
                    $product->setSku($data["sku"]);
                    $product->setDescription($data["description"]);
                    $product->setName($data["name"]);
                    $product->setTaxClassId($data["tax"]);
                    $product->setPrice($data["full_price"]);
                    $product->setData("supply_price", $data["supply_price"]);
                    $product->setData("markup", $data["markup"]);
                    $product->setData("brand", $data["brand"]);
                    $product->setData("supplier", $data["supplier"]);
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
                                $helperAnt = Mage::helper("ant_api");
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
                    $qty = $data["inventories"]["quantity"];
                    $product->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => 1, //manage stock
                            'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                            'max_sale_qty' => 10, //Maximum Qty Allowed in Shopping Cart
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
            if($data["type"]=="configurable") {
                if ($this->_validateDataBeforeUpdate($data) == "") {
                    $attributeSetId = $data["attribute_set_id"];
                    $product->setSku($data["sku"]);
                    $product->setName($data["name"]);
                    $product->setDescription($data["description"]);
                    $product->setData("brand", $data["supplier"]);
                    $product->setData("supplier", $data["supplier"]);
                    $product->setWebsiteIds(array(1)); //website ID the product is assigned to, as an array
                    $product->setAttributeSetId($attributeSetId); //ID of a attribute set named 'default'
                    $product->setTypeId("configurable"); //product type
                    $product->setCreatedAt(strtotime('now')); //product creation time
                    $product->setStatus(1); //product status (1 - enabled, 2 - disabled)
                    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                    $product->setPrice($data["full_price"]);
                    $product->setMediaGallery(array('images' => array(), 'values' => array()));
                    if (isset($data["tax_class"])) {
                        $product->setTaxClassId($data["tax_class"]);
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
                            $helperAnt = Mage::helper("ant_api");
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
                    $product->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => 1, //manage stock
                            'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                            'max_sale_qty' => 10, //Maximum Qty Allowed in Shopping Cart
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => $qty //qty
                        )
                    );
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
                        //var_dump($arrayAttributeToset);
                        //die();
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
                            $configurableProductsData[$value] = array( //['920'] = id of a simple product associated with this configurable

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
                $arrayToExclude = array("id", "images", "inventories", "tax", "full_price");
                foreach ($data as $key => $value) {
                    if (!in_array($key, $arrayToExclude)) {
                        $product->setData($key, $value);
                    }
                }
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
                        $product->setData($_item["code"], $_item["value"]);
                    }
                }
                if ($this->_checkAttribute("inventories", $data)) {
                    if ($this->_checkAttribute("quantity", $data["inventories"])) {
                        $qty = $data["inventories"]["quantity"];
                        $product->setStockData(array('qty' => $qty));
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
            $arrayToExclude = array("id", "images", "inventories", "tax", "full_price");
            foreach ($data as $key => $value) {
                if (!in_array($key, $arrayToExclude)) {
                    $product->setData($key, $value);
                }
            }
            if ($this->_checkAttribute("tax", $data)) {
                $product->setTaxClassId($data["tax"]);
            }
            if ($this->_checkAttribute("full_price", $data)) {
                $product->setPrice($data["full_price"]);
            }
            if ($this->_checkAttribute("options", $data)) {
                $codeArray = $data["options"];
                foreach ($codeArray as $_item) {
                    $product->setData($_item["code"], $_item["value"]);
                }
            }
            if ($this->_checkAttribute("inventories", $data)) {
                if ($this->_checkAttribute("quantity", $data["inventories"])) {
                    $qty = $data["inventories"]["quantity"];
                    $product->setStockData(array('qty' => $qty));
                }
            }
            $product->save();
            return $product->getId();
        }
        return false;
    }
}