<?php
/**
 * Data Helper
 *
 * @author EdgeWorks Team
 */

class Ant_Api_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Path to store config if frontend output is enabled
     * @var string
     */
    const XML_PATH_ENABLED = 'ant_api_config/general/enabled';

    /**
     * Path to store config where OAuth consumer key is stored
     * @var string
     */
    const XML_PATH_CONSUMER_KEY = 'ant_api_config/general/consumer_key';

    /**
     * Path to store config where OAuth consumer key is stored
     * @var string
     */
    const XML_PATH_CONSUMER_SECRET = 'ant_api_config/general/consumer_secret';

    /**
     * Path to store config where OAuth consumer key is stored
     * @var string
     */
    const XML_PATH_AUTHORIZATION_TOKEN = 'ant_api_config/general/authorization_token';

    /**
     * Path to store config where OAuth consumer key is stored
     * @var string
     */
    const XML_PATH_AUTHORIZATION_SECRET = 'ant_api_config/general/authorization_secret';


    const KEY_NAME_IMAGE="image_name";

    const KEY_ABSOLUTE_PATH_IMG="absolute_path";

    const OAUTH_CALLBACK_URL="http://requestb.in/q9g0w6q9";

    const OAUTH_CONSUMER_NAME = 'ANT REST';
    /**
     * Utility for fetching settings for our extension.
     * @param integer|string|Mage_Core_Model_Store $store
     * @return mixed
     */
    public function getConfigSetting($setting_key, $store=null)
    {
        $store = is_null($store) ? Mage::app()->getStore() : $store;

        $request_store = Mage::app()->getRequest()->getParam('store');

        // If the request explicitly sets the store, use that.
        if ($request_store && $request_store !== 'undefined') {
            $store = $request_store;
        }

        return Mage::getStoreConfig('reclaim/general/' . $setting_key, $store);
    }


    /**
     * Checks whether the extension is enabled
     * @param integer|string|Mage_Core_Model_Store $store
     * @return boolean
     */
    public function isEnabled($store=null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED, $store);
    }


    /**
     * Return the store's OAuth Consumer Key
     * @param integer|string|Mage_Core_Model_Store $store
     * @return string
     */
    public function getConsumerKey($store=null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CONSUMER_KEY, $store);
    }

    /**
     * Return the store's OAuth Consumer Secret
     * @param integer|string|Mage_Core_Model_Store $store
     * @return string
     */
    public function getConsumerSecret($store=null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CONSUMER_SECRET, $store);
    }

    /**
     * Return the store's OAuth Authorization Token
     * @param integer|string|Mage_Core_Model_Store $store
     * @return string
     */
    public function getAuthorizationToken($store=null)
    {
        return Mage::getStoreConfig(self::XML_PATH_AUTHORIZATION_TOKEN, $store);
    }

    /**
     * Return the store's OAuth Authorization Secret
     * @param integer|string|Mage_Core_Model_Store $store
     * @return string
     */
    public function getAuthorizationSecret($store=null)
    {
        return Mage::getStoreConfig(self::XML_PATH_AUTHORIZATION_SECRET, $store);
    }

    /**
     * Returns whether the current user is an admin.
     * @return bool
     */
    public function isAdmin()
    {
        return Mage::getSingleton('admin/session')->isLoggedIn();
    }

    /**
     * Set the store's OAuth Consumer Key
     * @param string
     * @return void
     */
    public function setConsumerKey($consumerKey)
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_CONSUMER_KEY, $consumerKey);
    }

    /**
     * Set the store's OAuth Consumer Secret
     * @param string
     * @return void
     */
    public function setConsumerSecret($consumerSecret)
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_CONSUMER_SECRET, $consumerSecret);
    }

    /**
     * Set the store's OAuth Authorization Token
     * @param string
     * @return void
     */
    public function setAuthorizationToken($authorizationToken)
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_AUTHORIZATION_TOKEN, $authorizationToken);
    }

    /**
     * Set the store's OAuth Authroization Secret
     * @param string
     * @return void
     */
    public function setAuthorizationSecret($authorizationSecret)
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_AUTHORIZATION_SECRET, $authorizationSecret);
    }

    public function log($data, $filename)
    {
        if ($this->config('enable_log') != 0) {
            return Mage::getModel('core/log_adapter', $filename)->log($data);
        }
    }
    /**
     * Make the function helper for hash product
     *
     */
    public function setTheHashProductSimple($idProduct){

        $modelDetailProduct = Mage::getModel("catalog/product");
        $detailProduct = $modelDetailProduct->load($idProduct);
        $arrayDetailProduct = array();
        $arrayDetailProduct["id"] = $detailProduct->getid();
        $arrayDetailProduct["name"] = $detailProduct->getname();
        $arrayDetailProduct["sku"] = $detailProduct->getsku();
        $arrayDetailProduct["description"] = $detailProduct->getdescription();
        $arrayDetailProduct["full_price"] = $detailProduct->getFinalPrice();
        $arrayDetailProduct["tax"] = $detailProduct->getTaxClassId();
        $arrayDetailProduct["supply_price"] = $detailProduct->getData("supply_price");
        $arrayDetailProduct["markup"] = $detailProduct->getData("markup");
        $galleryData = $detailProduct->getMediaGalleryImages();
        $arrayPutImageIn = array();
        foreach ($galleryData as &$image) {
            $arrayImage = array();
            $id = $image->getId();
            $url = $image->getUrl();
            $position = $image->getPosition();
            $arrayImage["id"] = $id;
            $arrayImage["url"] = $url;
            $arrayImage["position"] = $position;
            $arrayPutImageIn[] = $arrayImage;
        }
        $arrayDetailProduct["images"] = $arrayPutImageIn;
        $arrayInventory = array();
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($detailProduct);
        $arrayInventory["quantity"] = $stock->getQty();
        $arrayDetailProduct["inventories"] = $arrayInventory;
        $arrayDetailProduct["brand"] = $detailProduct->getBrand();
        $arrayDetailProduct["supplier"] = $detailProduct->getSupplier();
       return $arrayDetailProduct;
    }
    /**
     * Make the function helper for hash product
     *
     */
    public function setTheHashConfigruableProduct($idProduct){
        $modelDetailProduct = Mage::getModel("catalog/product");
        $detailProduct = $modelDetailProduct->load($idProduct);
        $arrayDetailProduct = array();
        $arrayDetailProduct["id"] = $detailProduct->getid();
        $arrayDetailProduct["name"] = $detailProduct->getname();
        $arrayDetailProduct["sku"] = $detailProduct->getsku();
        $arrayDetailProduct["description"] = $detailProduct->getdescription();
        $arrayDetailProduct["full_price"] = $detailProduct->getFinalPrice();
        $galleryData = $detailProduct->getMediaGalleryImages();
        $arrayPutImageIn = array();
        foreach ($galleryData as &$image) {
            $arrayImage = array();
            $id = $image->getId();
            $url = $image->getUrl();
            $position = $image->getPosition();
            $arrayImage["id"] = $id;
            $arrayImage["url"] = $url;
            $arrayImage["position"] = $position;
            $arrayPutImageIn[] = $arrayImage;
        }
        $arrayDetailProduct["images"] = $arrayPutImageIn;
        $arrayDetailProduct["variants"] = array();
        $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$detailProduct);
        $options = array();
        // Get any super_attribute settings we need
        $productAttributesOptions = $detailProduct->getTypeInstance(true)->getConfigurableOptions($detailProduct);
        foreach ($productAttributesOptions as $productAttributeOption) {
            $options[$idProduct] = array();
            foreach ($productAttributeOption as $optionValues) {
                $val = trim($optionValues['option_title']);
                $options[$idProduct][] = array(
                    $optionValues['sku'] => array("attribute_code" => $optionValues['attribute_code'] ,"value" => $val)
                );
            }
        }
        foreach($childProducts as $child) {
            $arrayChildProduct=array();
            $childProduct=Mage::getModel("catalog/product")->load($child->getid());
            $arrayChildProduct["id"] = $child->getid();
            $arrayChildProduct["sku"] = $child->getsku();
            $arrayChildProduct["supply_price"] = $childProduct->getData("supply_price");
            $arrayChildProduct["full_price"] = $childProduct->getFinalPrice();
            $arrayChildProduct["tax"] = $childProduct->getTaxClassId();
            $arrayChildProduct["markup"] = $childProduct->getData("markup");
            $arrayInventory = array();
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($child);
            $arrayInventory["quantity"] = $stock->getQty();
            $arrayChildProduct["inventories"] = $arrayInventory;
            $arrayOptionsAll=array();
            foreach($options[$idProduct] as $attribute) {
                if(key($attribute)==$child->getsku()) {
                    $arrayOptions = array(
                        "code" => $attribute[$child->getsku()]["attribute_code"],
                        "value" => $attribute[$child->getsku()]["value"]
                    );
                    $arrayOptionsAll[]=$arrayOptions;
                }
            }
            $arrayChildProduct["options"]=$arrayOptionsAll;
            $arrayDetailProduct["variants"][]=$arrayChildProduct;
        }
        $arrayInventory = array();
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($detailProduct);
        $arrayInventory["quantity"] = $stock->getQty();
        $arrayDetailProduct["inventories"] = $arrayInventory;
        $arrayDetailProduct["brand"] = $detailProduct->getBrand();
        $arrayDetailProduct["supplier"] = $detailProduct->getSupplier();
        return $arrayDetailProduct;
    }
    protected function _checkAttribute($attribute_name,$data){
        if(!isset($data[$attribute_name])){
            return false;
        }
        return true;
    }
    public function _update(array $data,$idProduct){
        $product = Mage::getModel("catalog/product")->load($idProduct);
        // attribute set and product type cannot be updated
        $arrayToExclude=array("id","images","inventories","tax","full_price");
        try {
            if($product->getTypeId()=="configurable"){
                foreach($data as $key=>$value){
                    if(!in_array($key,$arrayToExclude)) {
                        $product->setData($key, $value);
                    }
                }
                if($this->_checkAttribute("tax",$data)) {
                    $product->setTaxClassId($data["tax"]);
                }
                if($this->_checkAttribute("full_price",$data)) {
                    $product->setPrice($data["full_price"]);
                }
                //Image
                if($this->_checkAttribute("images",$data)) {
                    if ($this->_checkAttribute("id", $data["images"]) && $this->_checkAttribute("url", $data["images"]) && $this->_checkAttribute("position", $data["images"])) {
                        $urlImageInput = $data["images"]["url"];
                        $position = $data["images"]["position"];
                        $helperAnt = Mage::helper("ant_api");
                        $arrayImageInfor = $helperAnt->setImageProduct($urlImageInput);
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
                        foreach ($items as $item) {
                            if ($item["position"] == $position) {
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
                                $mediaApi->update($idProduct, $item['file'], $dataImage, Mage_Core_Model_App::ADMIN_STORE_ID);
                            }
                        }
                    }
                }
                //QTY
                if($this->_checkAttribute("inventories",$data)) {
                    if($this->_checkAttribute("quantity",$data["inventories"])) {
                        $qty = $data["inventories"]["quantity"];
                        $product->setStockData(array('qty' => $qty));
                    }
                }
                //assign simple product to configruable product
                $dataVariants=$data["variants"];
                if($dataVariants && is_array($dataVariants)) {
                    //var_dump($dataVariants);
                    foreach ($dataVariants as $_variant) {
                        $id_variant = $_variant["id"];
                        $this->setSimpleProductToConfigruableProduct($id_variant, $_variant);

                    }
                    //$product->setConfigurableProductsData($configurableProductsData);
                }
                $product->save();
            }
        } catch (Mage_Core_Exception $e) {
            throw new $e->getMessage();
        }
    }
    public function setSimpleProductToConfigruableProduct($idProduct,$data){
        $product = Mage::getModel("catalog/product")->load($idProduct);
        $product->setSku($data["sku"]);
        $product->setTaxClassId($data["tax"]);
        $product->setPrice($data["full_price"]);
        $product->setData("markup",$data["markup"]);
        $product->setData("supply_price", $data["supply_price"]);
        $codeArray = $data["options"];
        foreach ($codeArray as $_item) {
            $product->setData($_item["code"],$_item["value"]);
        }
        $qty = $data["inventories"]["quantity"];
        $product->setStockData(array('qty' => $qty));
        $product->save();

    }
    protected $_arrayIdSimpleProduct=array();
    public function _createProduct(array $data){
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $defaultAttributeSetId = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getDefaultAttributeSetId();
            $product = Mage::getModel('catalog/product');
            //$this->_validateDataBeforeUpdate($data);
            if($data["type"]=="simple") {
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
                $urlImageInput = $data["images"]["url"];
                $position = $data["images"]["position"];
                $helperAnt = Mage::helper("ant_api");
                $arrayImageInfor = $helperAnt->setImageProduct($urlImageInput);
                $nameImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
                $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
                $product->setMediaGallery(array('images' => array(), 'values' => array()));
                $product->addImageToMediaGallery($absolutePathImage, array('image', 'thumbnail', 'small_image'), false, false);
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
                $product->save();
            }
            if($data["type"]=="configurable"){
                $attributeSetId=$data["attribute_set_id"];
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
                if(isset($data["tax_class"])) {
                    $product->setTaxClassId($data["tax_class"]);
                }else{
                    $product->setTaxClassId(1);
                }
                $dataImage=$data["images"];
                $count=0;
                foreach($dataImage as $_image) {
                    $urlImageInput = $_image["url"];
                    $position = $_image["position"];
                    $helperAnt = Mage::helper("ant_api");
                    $arrayImageInfor = $helperAnt->setImageProduct($urlImageInput);
                    $nameImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
                    $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
                    //$product->addImageToMediaGallery($absolutePathImage, array('image', 'thumbnail', 'small_image'), false, false);
                    if ($count == 0){
                        $product->addImageToMediaGallery( $absolutePathImage , array('image', 'thumbnail', 'small_image'), true, false );
                    }else {
                        $product->addImageToMediaGallery( $absolutePathImage , null, true, false );
                    }
                    $count++;
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
                $dataVariants=$data["variants"];
                $arrayProductIds=array();
                if($dataVariants && is_array($dataVariants)) {
                    foreach ($dataVariants as $_variant) {
                        $arrayProductIds[]=$this->setSimpleProductToConfigruableProductCreate($_variant,$attributeSetId);
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
                    foreach($arrayProductIds as $key=>$value){
                        $configurableProductsData[$value] = array( //['920'] = id of a simple product associated with this configurable

                        );
                    }
                    var_dump($configurableAttributesData);
                    $product->setConfigurableProductsData($configurableProductsData);
                }
                $product->save();
            }
        }
        catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
    public function setSimpleProductToConfigruableProductCreate($data,$attributeSetId){
        if(isset($data["id"])){
            $this->setSimpleProductToConfigruableProductCaseUpdate($data["id"],$data);
        }else {
            $product=Mage::getModel("catalog/product");
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
    }
    public function setSimpleProductToConfigruableProductCaseUpdate($id_product,$data){
        $product = Mage::getModel("catalog/product")->load($id_product);
        $arrayToExclude=array("id","images","inventories","tax","full_price");
        foreach($data as $key=>$value){
            if(!in_array($key,$arrayToExclude)){
                $product->setData($key, $value);
            }
        }
        if($this->_checkAttribute("tax",$data)) {
            $product->setTaxClassId($data["tax"]);
        }
        if($this->_checkAttribute("full_price",$data)){
            $product->setPrice($data["full_price"]);
        }
        if($this->_checkAttribute("options",$data)){
            $codeArray = $data["options"];
            foreach ($codeArray as $_item) {
                $product->setData($_item["code"], $_item["value"]);
            }
        }
        if($this->_checkAttribute("inventories",$data)) {
            if($this->_checkAttribute("quantity",$data["inventories"])) {
                $qty = $data["inventories"]["quantity"];
                $product->setStockData(array('qty' => $qty));
            }
        }
        $product->save();
    }
    /**
     * Make the function helper for customer hash
     *
     */
    public function setTheHashCustomer($customerId){
        $customer=Mage::getModel('customer/customer')->load($customerId);
        $arrayCustomerHash=array();
        $firstName=$customer->getData("firstname");
        $lastName=$customer->getData("lastname");
        $arrayCustomerHash["first_name"]=$firstName;
        $arrayCustomerHash["last_name"]=$lastName;
        $arrayCustomerHash["full_name"]=$firstName.' '.$lastName;
        $arrayCustomerHash["email"]=$customer->getEmail();
        $arrayCustomerHash["address"]=array();
            $addressShipping = $customer->getDefaultShipping();
            if($addressShipping){
                $address = Mage::getModel('customer/address')->load($addressShipping);
                $arrayAddressShipping=array();
                $street = $address->getStreet();
                $arrayAddressShipping["address1"]=$street[0];
                $arrayAddressShipping["address2"]=$street[1];
                $arrayAddressShipping["suburb"]=$address->getRegion();
                $arrayAddressShipping["postcode"]=$address->getPostcode();
                $arrayAddressShipping["country"]=$address->getCountry();
                $arrayCustomerHash["address"][]=$arrayAddressShipping;
            }

            $addressBlling=$customer->getDefaultBilling();
            if($addressBlling){
                $address = Mage::getModel('customer/address')->load($addressBlling);
                $arrayAddressBlling=array();
                $street = $address->getStreet();
                $arrayAddressBlling["address1"]=$street[0];
                $arrayAddressBlling["address2"]=$street[1];
                $arrayAddressBlling["suburb"]=$address->getRegion();
                $arrayAddressBlling["postcode"]=$address->getPostcode();
                $arrayAddressBlling["country"]=$address->getCountry();
                $arrayCustomerHash["address"][]=$arrayAddressBlling;
            }
        return $arrayCustomerHash;
    }
    /**
     * Make the function helper for order hash
     *
     */
    public function setTheHashOrder($order,$taxOnFrontEnd=null,$status=null){
        $arrayOrder=array();
        $total_tax=0;
        $statusLabel="Pending";
        if($taxOnFrontEnd !=null){
            $total_tax=$taxOnFrontEnd;
        }
        else{
            $total_tax=$order->getData("shipping_tax_amount");
        }
        if($status != null){
            $statusLabel=$status;
            //Mage_Sales_Model_Order::STATE_PROCESSING;
        }else{
            $statusLabel=$order->getStatusLabel();
        }
        $arrayOrder["id"]=$order->getId();
        $arrayOrder["order_number"]=$order->getIncrementId();
        $arrayOrder["placed_on"]=$order->getData("created_at");
        $arrayOrder["total_price"]=$order->getData("base_grand_total");
        $arrayOrder["total_tax"]=$total_tax;
        $arrayOrder["status"]=$statusLabel;
        $arrayOrder["items"]=array();
        foreach($order->getAllItems() as $_item){
            $array["items"][]=array(
                "id"=>$_item->getData("product_id"),
                "type"=>$_item->getData("product_type"),
                "inventories"=>array("quantity"=>$_item->getQtyToShip()),
                "sale_price"=>$_item->getData("price")
            );
        }
        $billing_address=$order->getBillingAddress();
        $arrayBillingAddress=$this->getCustomerHashFromOrder($billing_address);
        $arrayOrder["billing_address"]=$arrayBillingAddress;
        $shippingAddress=$order->getShippingAddress();
        $arrayShippingAddress=$this->getCustomerHashFromOrder($shippingAddress);
        $arrayOrder["shipping_address"]=$arrayShippingAddress;
        return $arrayOrder;
    }
    public function getCustomerHashFromOrder($objectAddress){
        $customer=array();
        $customer["first_name"]=$objectAddress->firstname;
        $customer["last_name"]=$objectAddress->lastname;
        $customer["full_name"]=$objectAddress->lastname;
        $customer["email"]=$objectAddress->email;
        $customer["phone"]=$objectAddress->telephone;
        $customer["customer_company"]=$objectAddress->company;
        $street_address=$objectAddress->street;
        $street=explode(".",$street_address);
        $customer["address"]=array(
            "address1"  =>  $street[0],
            "address2"  =>  $street[1],
            "suburb"    =>  $objectAddress->region,
            "postcode"  =>  $objectAddress->poscode,
            "country"   =>  $objectAddress->country_id,
        );
        return $customer;
    }
    public function callUrl($url,$postData){
        $requestUrl=$url->getData("ant_api_webhook_url");
        $postData=json_encode($postData);
        $header=array(
            "Accept:application/json",
            "Content-Type:application/json",
            "X-ANTHQ-TOKEN:".$this->getAuthorizationToken(0),
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_exec($ch);
        curl_close($ch);
    }
    public function setImageProduct($url){
        $image_name=basename($url); // replace https tp http
        $image_type = "jpg"; //find the image extension
        $filename   = "upload_api"; //give a new name, you can modify as per your requirement
        $filePathOnDir   = Mage::getBaseDir('media') . DS  . $filename. DS . $image_name; //path for temp storage folder: ./media/import/ , absolute path
        file_put_contents($filePathOnDir,file_get_contents($url)); //store the image from external url to the temp storage folder file_get_contents(trim($image_url))
        $absolutePath=$filePathOnDir;
        return array(
            self::KEY_NAME_IMAGE    =>  $image_name,
            self::KEY_ABSOLUTE_PATH_IMG =>  $absolutePath
        );
    }
    public function getDataWebHook($condition){
        $modelWebhook=Mage::getModel("ant_api/webhook");
        $data=$modelWebhook->getCollection()->addFieldToFilter("ant_api_webhook_action",array("like"=>"%".$condition."%"));
        echo (string)$data->getSelect();
        return $data;
    }
    public function autoGenerate($isSetup = false){
        $oauthHelper = Mage::helper('oauth');
        $consumerKey = $oauthHelper->generateConsumerKey();
        $consumerSecret = $oauthHelper->generateConsumerSecret();
        $consumerModel = Mage::getModel('oauth/consumer');
        $consumerData = array(
            'name' => self::OAUTH_CONSUMER_NAME,
            'key' => $consumerKey,
            'secret' => $consumerSecret);
        $consumerModel->addData($consumerData);
        $consumerModel->save();
        $token = Mage::getModel('oauth/token');
        $token->createRequestToken($consumerModel->getId(), self::OAUTH_CALLBACK_URL);
        $token->convertToAccess();
        $requestToken = $token->getToken();
        $requestTokenSecret = $token->getSecret();
        $user = Mage::getSingleton('admin/session')->getUser();
        if ($user) {
            $userId = $user->getId();
        } else if ($isSetup) {
            $userId = 1;
        }
        $token->authorize($userId, Mage_Oauth_Model_Token::USER_TYPE_ADMIN);
        $reclaimHelper = Mage::helper('ant_api');
        $reclaimHelper->setConsumerKey($consumerKey);
        $reclaimHelper->setConsumerSecret($consumerSecret);
        $reclaimHelper->setAuthorizationToken($requestToken);
        $reclaimHelper->setAuthorizationSecret($requestTokenSecret);
        return $token;
    }
}
