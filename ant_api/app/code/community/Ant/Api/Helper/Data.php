<?php
/**
 * Data Helper
 *
 * @author EdgeWorks Team
 */
set_time_limit(0);
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

    /**
     * Path to store config where OAuth consumer key is stored
     * @var string
     */
    const XML_PATH_AUTHORIZATION_URL = 'ant_api_config/general/url_front';

    /**
     * Path to store config where last generated time is stored
     * @var string
     */
    const XML_PATH_LAST_GENERATED = 'ant_api_config/general/last_generated';

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
     * @deprecated This setter was incorrectly used to save config, please use Mage::getModel('core/config')->saveConfig() directly
     * @param string
     * @return void
     */
    public function setConsumerKey($consumerKey)
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_CONSUMER_KEY, $consumerKey);
    }

    /**
     * Set the store's OAuth Consumer Secret
     * @deprecated This setter was incorrectly used to save config, please use Mage::getModel('core/config')->saveConfig() directly
     * @param string
     * @return void
     */
    public function setConsumerSecret($consumerSecret)
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_CONSUMER_SECRET, $consumerSecret);
    }

    /**
     * Set the store's OAuth Authorization Token
     * @deprecated This setter was incorrectly used to save config, please use Mage::getModel('core/config')->saveConfig() directly
     * @param string
     * @return void
     */
    public function setAuthorizationToken($authorizationToken)
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_AUTHORIZATION_TOKEN, $authorizationToken);
    }

    /**
     * Set the store's OAuth Authroization Secret
     * @deprecated This setter was incorrectly used to save config, please use Mage::getModel('core/config')->saveConfig() directly
     * @param string
     * @return void
     */
    public function setAuthorizationSecret($authorizationSecret)
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_AUTHORIZATION_SECRET, $authorizationSecret);
    }

    /**
     * Set the store's Url
     * @deprecated This setter was incorrectly used to save config, please use Mage::getModel('core/config')->saveConfig() directly
     * @param string
     * @return void
     */
    public function setUrl($url){
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_AUTHORIZATION_URL, $url);
    }

    public function getUrl($store=null){
        return Mage::getStoreConfig(self::XML_PATH_AUTHORIZATION_URL,$store);
    }

    public function log($data, $filename)
    {
        if ($this->config('enable_log') != 0) {
            return Mage::getModel('core/log_adapter', $filename)->log($data);
        }
    }

    public function logHistory($message,$isError = false){
        if($isError == true) {
            Mage::log($message, null, "error_log_ant_api.log");
        }else{
            Mage::log($message, null, "log_ant_api.log");
        }
    }
    /** Begin Save Tax Product **/
    public function createCustomer(){
        $websiteId = 1;
        $store =  Mage::getModel('core/store')->load(0); ;
        $customer = Mage::getModel("customer/customer");
        $customer   ->setWebsiteId($websiteId)
            ->setStore($store)
            ->setFirstname('Tag Admin')
            ->setLastname('Tag Admin')
            ->setEmail('tagAdmin@gmail.com')
            ->setPassword('1234567');
        try{
            $customer->save();
            return $customer->getId();
        }
        catch (Exception $e) {

        }
    }
    public function getCustomerId(){
        $collectionCustomer=Mage::getModel('customer/customer')->getCollection();
        $idCustomer = 0 ;
        if($collectionCustomer->count() > 0) {
            foreach ($collectionCustomer as $_customer) {
                $idCustomer = $_customer->getId();
                break;
            }
        }else{
            $idCustomer = $this->createCustomer();
        }
        return $idCustomer;
    }
    public function setTagsProduct($tagArray,$product){
        $tagName = "" ;
        foreach ( $tagArray as $_tag){
            $tagName.=$_tag." ";
        }
        if(!$product->getId()){
            $this->logHistory("Product id doest not exsit",true);
        } else {
            try {
                $customerId = $this->getCustomerId(); //$customerSession->getCustomerId();
                $storeId = 0;
                $productId = $product->getId();
                $tagNamesArr = $this->_cleanTags($this->_extractTags($tagName));
                $counter = new Varien_Object(array(
                        "new" => 0,
                        "exist" => array(),
                        "success" => array(),
                        "recurrence" => array())
                );
                $tagModel = Mage::getModel('tag/tag');
                $tagRelationModel = Mage::getModel('tag/tag_relation');

                foreach ($tagNamesArr as $tagName) {
                    $tagModel->unsetData()
                        ->loadByName($tagName)
                        ->setStoreId($storeId)
                        ->setName($tagName);

                    $tagRelationModel->unsetData()
                        ->setStoreId($storeId)
                        ->setProductId($productId)
                        ->setCustomerId($customerId)
                        ->setActive(1)
                        ->setCreatedAt( $tagRelationModel->getResource()->formatDate(time()) );

                    if (!$tagModel->getId()) {
                        $tagModel->setFirstCustomerId($customerId)
                            ->setFirstStoreId($storeId)
                            ->setStatus($tagModel->getApprovedStatus())
                            ->save();

                        $tagRelationModel->setTagId($tagModel->getId())->save();
                        $counter->setNew($counter->getNew() + 1);
                    } else {
                        $tagStatus = $tagModel->getStatus();
                        $tagRelationModel->setTagId($tagModel->getId());

                        switch($tagStatus) {
                            case $tagModel->getApprovedStatus():
                                if($this->_checkLinkBetweenTagProduct($tagRelationModel)) {
                                    $relation =$this->_getLinkBetweenTagCustomerProduct($tagRelationModel, $tagModel);
                                    if ($relation->getId()) {
                                        if (!$relation->getActive()) {
                                            $tagRelationModel
                                                ->setId($relation->getId())
                                                ->save();
                                        }
                                    } else {
                                        $tagRelationModel->save();
                                    }
                                    $counter->setExist(array_merge($counter->getExist(), array($tagName)));
                                } else {
                                    $tagRelationModel->save();
                                    $counter->setSuccess(array_merge($counter->getSuccess(), array($tagName)));
                                }
                                break;
                            case $tagModel->getPendingStatus():
                                $relation = $this->_getLinkBetweenTagCustomerProduct($tagRelationModel, $tagModel);
                                if ($relation->getId()) {
                                    if (!$relation->getActive()) {
                                        $tagRelationModel
                                            ->setId($relation->getId())
                                            ->save();
                                    }
                                } else {
                                    $tagRelationModel->save();
                                }
                                $counter->setNew($counter->getNew() + 1);
                                break;
                            case $tagModel->getDisabledStatus():
                                if($this->_checkLinkBetweenTagCustomerProduct($tagRelationModel, $tagModel)) {
                                    $counter->setRecurrence(array_merge($counter->getRecurrence(), array($tagName)));
                                } else {
                                    $tagModel->setStatus($tagModel->getPendingStatus())->save();
                                    $tagRelationModel->save();
                                    $counter->setNew($counter->getNew() + 1);
                                }
                                break;
                        }
                    }
                }

            } catch (Exception $e) {
                $this->logHistory("Unable to save tags",true);
            }
        }
    }
    public function getTagsProduct($product_id){
        $model = Mage::getModel('tag/tag');
        $tagCollection = $model->getResourceCollection()
            ->addPopularity()
            ->addProductFilter($product_id);
        $arrayTags = array ();
        foreach($tagCollection as $_tag){
            $arrayTags[] =$_tag->getName();
        }
        return $arrayTags;
    }
    function _extractTags($tagNamesInString)
    {
        return explode("\n", preg_replace("/(\'(.*?)\')|(\s+)/i", "$1\n", $tagNamesInString));
    }
    /**
     * Clears the tag from the separating characters.
     *
     * @param array $tagNamesArr
     * @return array
     */
    function _cleanTags(array $tagNamesArr)
    {
        foreach( $tagNamesArr as $key => $tagName ) {
            $tagNamesArr[$key] = trim($tagNamesArr[$key], '\'');
            $tagNamesArr[$key] = trim($tagNamesArr[$key]);
            if( $tagNamesArr[$key] == '' ) {
                unset($tagNamesArr[$key]);
            }
        }
        return $tagNamesArr;
    }
    /**
     * Checks whether the already marked this product in this store by this tag.
     *
     * @param Mage_Tag_Model_Tag_Relation $tagRelationModel
     * @return boolean
     */
    function _checkLinkBetweenTagProduct($tagRelationModel)
    {
        $customerId = $tagRelationModel->getCustomerId();
        $tagRelationModel->setCustomerId(null);
        $res = in_array($tagRelationModel->getProductId(), $tagRelationModel->getProductIds());
        $tagRelationModel->setCustomerId($customerId);
        return $res;
    }
    /**
     * Checks whether the already marked this product in this store by this tag and by this customer.
     *
     * @param Mage_Tag_Model_Tag_Relation $tagRelationModel
     * @param Mage_Tag_Model_Tag $tagModel
     * @return boolean
     */
    function _checkLinkBetweenTagCustomerProduct($tagRelationModel, $tagModel)
    {
        return (count($this->_getLinkBetweenTagCustomerProduct($tagRelationModel, $tagModel)->getProductIds()) > 0);
    }
    /**
     * Get relation model for marked product in this store by this tag and by this customer.
     *
     * @param Mage_Tag_Model_Tag_Relation $tagRelationModel
     * @param Mage_Tag_Model_Tag $tagModel
     * @return Mage_Tag_Model_Tag_Relation
     */
    function _getLinkBetweenTagCustomerProduct($tagRelationModel, $tagModel)
    {
        return Mage::getModel('tag/tag_relation')->loadByTagCustomer(
            $tagRelationModel->getProductId(),
            $tagModel->getId(),
            $tagRelationModel->getCustomerId(),
            $tagRelationModel->getStoreId()
        );
    }
    /** End Save Tax Product **/

    /**
     * Function Config default tax was set by Extension
     **/
    public function getConfigTaxAnt(){
        $path = "ant_api_config/tax_class_ant/ant_taxs";
        return Mage::getStoreConfig($path);
    }
    /**
     * @param Mage_Core_Catalog_Product
     * Get rate tax base on tax class id of product
     * @return Mage_Core_Tax_Calculation_Rate
     **/
    public function getTaxCalculation($_product){
        //$taxClasses  = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
        $collection=Mage::getModel('tax/calculation')->getCollection()->addFieldToFilter("product_tax_class_id",array("eq" => $_product->getTaxClassId()))->getFirstItem();
        $rate=Mage::getModel("tax/calculation_rate")->load($collection->getData("tax_calculation_rate_id"));
        //$taxClassId = $_product->getTaxClassId();
        //$taxRate   = $taxClasses["value_".$taxClassId];
        $taxRate = $rate->getRate();
        return $taxRate;
    }
    public function getCountProductInStore(){
        $collectionProduct = Mage::getModel("catalog/product")->getCollection();
        $countProduct = 0;
        foreach($collectionProduct as $_product){
            $idProduct=$_product->getId();
            switch ($_product->getTypeId()){
                case "simple":
                    if(!Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($idProduct)) {
                        $countProduct++;
                    }
                    break;
                case "configurable":
                    $countProduct++;
                    break;
            }
        }
        return $countProduct;
    }
    /**
     * Make the function helper for hash product
     *
     **/
    public function setTheHashProductSimple($idProduct){

        $modelDetailProduct = Mage::getModel("catalog/product");
        $detailProduct = $modelDetailProduct->load($idProduct);
        $suffix = Mage::getStoreConfig('catalog/seo/product_url_suffix');
        $arrayDetailProduct = array();
        $arrayDetailProduct["id"] = $detailProduct->getId();
        $arrayDetailProduct["name"] = $detailProduct->getName();
        $arrayDetailProduct["sku"] = $detailProduct->getSku();
        $arrayDetailProduct["description"] = $detailProduct->getdescription();
        $arrayDetailProduct["short_description"] = $detailProduct->getShortDescription();
        $arrayDetailProduct["visibility"] = $detailProduct->getVisibility();
        //$arrayDetailProduct["store_front_url"] = $this->getUrl().$detailProduct->getUrlKey().$suffix;
        $arrayDetailProduct["store_front_url"] = $detailProduct->getProductUrl();
        $arrayDetailProduct["backend_url"] = $this->getUrl()."admin/catalog_product/edit/store/0/id/".$detailProduct->getid()."/";
        $arrayDetailProduct["weight"] = $detailProduct->getWeight();
        $arrayDetailProduct["full_price"] = $detailProduct->getFinalPrice();
        $arrayDetailProduct["special_price"] = $detailProduct->getSpecialPrice();
        $arrayDetailProduct["tags"] = $this->getTagsProduct($detailProduct->getid());
        $arrayTags=explode(",",$detailProduct->getMetaKeyword());
        $arrayOutPutTags=array();
        foreach($arrayTags as $_tag){
            $arrayOutPutTags[]=$_tag;
        }
        $arrayDetailProduct["meta"] = $arrayOutPutTags;
        $percent = $this->getTaxCalculation($detailProduct);
        $arrayDetailProduct["tax"] = $percent;
        $arrayDetailProduct["supply_price"] = $detailProduct->getData("supply_price");
        $arrayDetailProduct["markup"] = $detailProduct->getData("markup");
        $_categories = $detailProduct->getCategoryCollection()->addAttributeToSelect("name");
        $arrayCategory=array();
        foreach ($_categories as $_category){
            $arrayCate=array(
                "name"=>$_category->getData("name"),
                "id"=>$_category->getId(),
                "parent_id"=>$_category->getParentId()
            );
            $arrayCategory[]=$arrayCate;
        }
        $arrayDetailProduct["categories"]=$arrayCategory;
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
        $manager_stock=false;
        if($stock->getData("manage_stock")==1){
            $manager_stock=true;
        }
        $arrayDetailProduct["manage_stock"] = $manager_stock;
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
        $suffix = Mage::getStoreConfig('catalog/seo/product_url_suffix');
        $arrayDetailProduct = array();
        $arrayDetailProduct["id"] = $detailProduct->getId();
        $arrayDetailProduct["name"] = $detailProduct->getName();
        $arrayDetailProduct["sku"] = $detailProduct->getSku();
        $arrayDetailProduct["description"] = $detailProduct->getdescription();
        $arrayDetailProduct["full_price"] = $detailProduct->getFinalPrice();
        $arrayDetailProduct["short_description"] = $detailProduct->getShortDescription();
        $arrayDetailProduct["visibility"] = $detailProduct->getVisibility();
        //$arrayDetailProduct["store_front_url"] = $this->getUrl().$detailProduct->getUrlKey().$suffix;
        $arrayDetailProduct["store_front_url"] = $detailProduct->getProductUrl();
        $arrayDetailProduct["backend_url"] = $this->getUrl()."admin/catalog_product/edit/store/0/id/".$detailProduct->getid()."/";
        $arrayDetailProduct["weight"] = $detailProduct->getWeight();
        $arrayDetailProduct["special_price"] = $detailProduct->getSpecialPrice();
        $arrayDetailProduct["tags"] = $this->getTagsProduct($detailProduct->getid());
        $arrayTags=explode(",",$detailProduct->getMetaKeyword());
        $arrayOutPutTags=array();
        foreach($arrayTags as $_tag){
            $arrayOutPutTags[]=$_tag;
        }
        $arrayDetailProduct["meta"] = $arrayOutPutTags;
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
                $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product',$optionValues["attribute_code"]);
                $position_of_value=$this->getPositionByValue($val,$optionValues["attribute_code"]);
                $options[$idProduct][] = array(
                    $optionValues['sku'] => array("attribute_code" => $attributeModel->getStoreLabel(0) ,"value" => $val,"position" => $position_of_value,"code" => $optionValues["attribute_code"])
                );
            }
        }
        $arrayProductOptions=array();
        foreach($childProducts as $child) {
            $arrayChildProduct=array();
            //TODO: change this to not load a database connectino within a foreach. This should be handled by a collection with attributes added to the collection.
            $childProduct=Mage::getModel("catalog/product")->load($child->getId());
            $arrayChildProduct["id"] = $child->getId();
            $arrayChildProduct["sku"] = $child->getSku();
            $arrayChildProduct["supply_price"] = $childProduct->getData("supply_price");
            $arrayChildProduct["full_price"] = $childProduct->getFinalPrice();
            $percent = $this->getTaxCalculation($childProduct);
            $arrayChildProduct["tax"] = $percent;
            $arrayChildProduct["markup"] = $childProduct->getData("markup");

            //Making a point of this - naming conventions could get confusing//
            // as per issue #1 in github, child weight needs to be set to parent product
            $arrayChildProduct["weight"] = $detailProduct->getWeight();

            $arrayInventory = array();
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($child);
            $arrayInventory["quantity"] = $stock->getQty();
            $arrayChildProduct["inventories"] = $arrayInventory;
            $arrayOptionsAll=array();
            foreach($options[$idProduct] as $attribute) {
                if(key($attribute)==$child->getSku()) {
                    $arrayOptions = array(
                        "code" => $attribute[$child->getSku()]["attribute_code"],
                        "value" => $attribute[$child->getSku()]["value"]
                    );
                    $arrayValues=array(
                        "name" => $attribute[$child->getSku()]["value"],
                        "position" => $attribute[$child->getSku()]["position"]
                    );
                    $arrayOptionsAll[]=$arrayOptions;
                    $key=$arrayOptions['code']."|".$attribute[$child->getSku()]['code'];
                    if (!isset($arrayProductOptions[$key])) {
                        $arrayProductOptions[$key] = [];
                    }
                    $arrayProductOptions[$key][] = $arrayValues;
                }
            }
            $arrayChildProduct["options"]=$arrayOptionsAll;
            $arrayDetailProduct["variants"][]=$arrayChildProduct;
        }
        $arrayInventory = array();
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($detailProduct);
        $arrayInventory["quantity"] = $stock->getQty();
        $manager_stock=false;
        if($stock->getData("manage_stock")=="1"){
            $manager_stock=true;
        }
        $arrayDetailProduct["manage_stock"] = $manager_stock;
        $arrayDetailProduct["inventories"] = $arrayInventory;
        $arrayDetailProduct["brand"] = $detailProduct->getBrand();
        $arrayDetailProduct["supplier"] = $detailProduct->getSupplier();
        $_categories = $detailProduct->getCategoryCollection()->addAttributeToSelect("name");
        $arrayCategory=array();
        foreach ($_categories as $_category){
            $arrayCate=array(
                "name"=>$_category->getData("name"),
                "id"=>$_category->getId(),
                "parent_id"=>$_category->getParentId()
            );
            $arrayCategory[]=$arrayCate;
        }
        $arrayDetailProduct["categories"]=$arrayCategory;
        $arrayDetailProduct["product_options"]=array();
        foreach($arrayProductOptions as $_key => $values){
            $arrayKey=explode("|",$_key);
            $arrayRetValue=$this->getValueAttributeByCode($arrayKey[1]);
            $arrayParent=array(
                "name"=>$arrayKey[0],
                "position"=>$arrayRetValue[0]["position_parent"],
                "values"=>$values
            );
            $arrayDetailProduct["product_options"][]=$arrayParent;
        }
        return $arrayDetailProduct;
    }
    //Check Attribute Code Exits
    public function checkExistAttribute($code){
        $attribute_id=Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$code);
        //$attr=Mage::getModel("eav/attribute")->loadByCode("catalog_product",$code);
        if($attribute_id){
            return true;
        }
        return false;
    }
    //
    // Create an attribute.
    //
    // For reference, see Mage_Adminhtml_Catalog_Product_AttributeController::saveAction().
    //
    // @return int|false
    //
    public function createAttribute($labelText, $attributeCode, $values = -1, $productTypes = -1, $setInfo = -1, $options = -1)
    {
        $labelText = trim($labelText);
        $attributeCode = trim($attributeCode);

        if($labelText == '' || $attributeCode == '')
        {
            echo "Can't import the attribute with an empty label or code.  LABEL= [$labelText]  CODE= [$attributeCode]"."<br/>";
            return false;
        }

        if($values === -1)
            $values = array();

        if($productTypes === -1)
            $productTypes = array();

        if($setInfo !== -1 && (isset($setInfo['SetID']) == false || isset($setInfo['GroupID']) == false))
        {
            echo "Please provide both the set-ID and the group-ID of the attribute-set if you'd like to subscribe to one."."<br/>";
            return false;
        }
        //Build the data structure that will define the attribute. See
        //Mage_Adminhtml_Catalog_Product_AttributeController::saveAction().

        $data = array(
            'is_global'                     => '1',
            'frontend_input'                => 'select',
            'default_value_text'            => '',
            'default_value_yesno'           => '0',
            'default_value_date'            => '',
            'default_value_textarea'        => '',
            'is_unique'                     => '0',
            'is_required'                   => '0',
            'frontend_class'                => '',
            'is_searchable'                 => '1',
            'is_visible_in_advanced_search' => '1',
            'is_comparable'                 => '1',
            'is_used_for_promo_rules'       => '0',
            'is_html_allowed_on_front'      => '1',
            'is_visible_on_front'           => '0',
            'used_in_product_listing'       => '0',
            'used_for_sort_by'              => '0',
            'is_configurable'               => '0',
            'is_filterable'                 => '1',
            'is_filterable_in_search'       => '1',
            'backend_type'                  => 'varchar',
            'default_value'                 => '',
            'is_user_defined'               => '0',
            'is_visible'                    => '1',
            'is_used_for_price_rules'       => '0',
            'position'                      => '0',
            'is_wysiwyg_enabled'            => '0',
            'backend_model'                 => '',
            'attribute_model'               => '',
            'backend_table'                 => '',
            'frontend_model'                => '',
            'source_model'                  => '',
            'note'                          => '',
            'frontend_input_renderer'       => '',
        );

        // Now, overlay the incoming values on to the defaults.
        foreach($values as $key => $newValue) {
            if (isset($data[$key]) == false) {
                echo "Attribute feature [$key] is not valid." . "<br/>";
                return false;
            } else {
                $data[$key] = $newValue;
            }
        }

        // Valid product types: simple, grouped, configurable, virtual, bundle, downloadable, giftcard
        $data['apply_to']       = $productTypes;
        $data['attribute_code'] = $attributeCode;
        $data['frontend_label'] = array(
            0 => $labelText,
            1 => '',
            3 => '',
            2 => '',
            4 => '',
        );
        $model = Mage::getModel('catalog/resource_eav_attribute');

        $model->addData($data);

        if($setInfo !== -1)
        {
            $model->setAttributeSetId($setInfo['SetID']);
            $model->setAttributeGroupId($setInfo['GroupID']);
        }

        $entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $model->setEntityTypeId($entityTypeID);

        $model->setIsUserDefined(1);
        try
        {
            $model->save();
        }
        catch(Exception $ex)
        {
            echo "Attribute [$labelText] could not be saved: " . $ex->getMessage()."<br/>";
            if($ex->getMessage() == "Attribute with the same code already exists."){
                if(is_array($options)){
                    foreach($options as $_opt){
                        $this->addAttributeValue($attributeCode, $_opt);
                    }
                }
            }
            return false;
        }
        if(is_array($options)){
            foreach($options as $_opt){
                $this->addAttributeValue($attributeCode, $_opt);
            }
        }
        $id = $model->getId();
        //echo "Attribute [$labelText] has been saved as ID ($id).<br/>";

        // Asssign to attribute set.
        $model1 = Mage::getModel('eav/entity_setup','core_setup');
        $model1->addAttributeToSet(
            'catalog_product', 'Default', 'General', $attributeCode
        ); //Default = attribute set, General = attribute group

        return $id;
    }
    public function updateAttributeValue($arg_attribute, $arg_value) {
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);
        foreach($arg_value as $_opt) {
            if (!$this->attributeValueExists($arg_attribute, $_opt)) {
                $value['option'] = array($_opt,$_opt);
                $result = array('value' => $value);
                $attribute->setData('option', $result);
                $attribute->save();
            }
        }
    }
    function addAttributeValue($arg_attribute, $arg_value)
    {
        $attribute_model        = Mage::getModel('eav/entity_attribute');

        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);

        if(!$this->attributeValueExists($arg_attribute, $arg_value))
        {
            $value['option'] = array($arg_value,$arg_value);
            $result = array('value' => $value);
            $attribute->setData('option',$result);
            $attribute->save();
        }

        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);

        foreach($options as $option)
        {
            if ($option['label'] == $arg_value)
            {
                return $option['value'];
            }
        }
        return false;
    }
    public function attributeValueExists($arg_attribute, $arg_value)
    {
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;

        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);

        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);

        foreach($options as $option)
        {
            if ($option['label'] == $arg_value)
            {
                return true;
            }
        }

        return false;
    }
    public function getPositionByValue($value,$attribute_code){
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $attribute_code);
        $attribute              = $attribute_model->load($attribute_code);
        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);
        $index=0;
        foreach($options as $option)
        {
            $index++;
            if ($option['label'] == $value)
            {
                //return $option["value"];
                return $index;
            }
        }
    }
    public function getValueAttributeByCode($attributeName){
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
        $attribute_code         = $attribute_model->getIdByCode('catalog_product',$attributeName);
        $attribute              = $attribute_model->load($attribute_code);
        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);
        $valueArray=array();
        $index=1;
        foreach($options as $option) {
            $arrayDetail=array(
                "value"=>$option['label'],
                "position"=>$index,
                "position_parent"=>$attribute->getPosition()
            );
            $valueArray[]=$arrayDetail;
            $index++;
        }
        return $valueArray;
    }
    public function getRootCategory(){
        $dataParent=Mage::getModel("catalog/category")->getCollection()->addFieldToFilter("level",array("eq"=>1))->getFirstItem();
        $parentId = $dataParent->getId();
        return $parentId;
    }
    public function getCategory($categoryData){
        $modelCategory = Mage::getModel('catalog/category');
        $entityCategory=null;
        if(isset($categoryData["id"])){
            $entityCategory=$modelCategory->load($categoryData["id"]);
        }
        if(isset($categoryData["name"])){
            $entityCategory=Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('name',array("eq"=>$categoryData["name"]))
                ->getFirstItem();
        }
        if($entityCategory->getId()){
            return $entityCategory->getId();

        }else {
            $category = Mage::getModel('catalog/category');
            $nameCateogry=$categoryData["name"];
            $category->setName($nameCateogry);
            $category->setUrlKey($nameCateogry);
            $category->setIsActive(1);
            $category->setDisplayMode('PRODUCTS');
            $category->setIsAnchor(1); //for active anchor
            $parentId=0;
            if(isset($categoryData["parent_id"])){
                $parentId=$categoryData["parent_id"];
            }else{
                $dataParent=Mage::getModel("catalog/category")->getCollection()->addFieldToFilter("level",array("eq"=>1))->getFirstItem();
                $parentId = $dataParent->getId();
            }
            $parentCategory = Mage::getModel('catalog/category')->load($parentId);
            $category->setPath($parentCategory->getPath());
            $category->save();
            return $category->getId();
        }
    }
    protected function _checkAttribute($attribute_name,$data){
        if(!isset($data[$attribute_name])){
            return false;
        }
        return true;
    }
    public function _update(array $data,$idProduct){
        //$idProduct=$this->getRequest()->getParam("id_product");
        $product = Mage::getModel("catalog/product")->load($idProduct);
        // attribute set and product type cannot be updated
        $arrayToExclude=array("id","images","inventories","full_price","tags","tax","meta","manage_stock","special_price","product_options","categories","product_type");
        try {
            if($product->getTypeId()=="simple") {
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
                if($this->_checkAttribute("special_price",$data)) {
                    $product->setSpecialPrice($data["special_price"]);
                }
                if($this->_checkAttribute("tags",$data)) {
                    $stringTags = "";
                    foreach ($data["tags"] as $_tags) {
                        $stringTags .= $_tags . ",";
                    }
                    $product->setMetaKeyword($stringTags);
                }
                if($this->_checkAttribute("meta",$data)) {
                    $stringMeta="";
                    foreach($data["meta"] as $_meta){
                        $stringMeta.=$_meta.",";
                    }
                    $product->setMetaDescription($stringMeta);
                }
                //Image
                $count=0;
                if($this->_checkAttribute("images",$data)) {
                    $dataImage=$data["images"];
                    foreach($dataImage as $_image) {
                        if($this->_checkAttribute("id",$_image) && $this->_checkAttribute("url",$_image) && $this->_checkAttribute("position",$_image)) {
                            $urlImageInput = $_image["url"];
                            $position = $_image["position"];
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
                            $isEmptyImage=true;
                            foreach ($items as $item) {
                                if ($item["position"] == $position) {
                                    $isEmptyImage=false;
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
                                    $mediaApi->update($idProduct, $item['file'], $dataImage, Mage_Core_Model_App::ADMIN_STORE_ID);
                                }
                            }
                            if($isEmptyImage){
                                if ($count == 0){
                                    $product->addImageToMediaGallery( $absolutePathImage , array('image', 'thumbnail', 'small_image'), true, false );
                                }else {
                                    $product->addImageToMediaGallery( $absolutePathImage , null, true, false );
                                }
                                $count++;
                            }
                        }
                    }
                }
                //QTY
                if($this->_checkAttribute("categories",$data)) {
                    $categories = $data["categories"];
                    $arrayCategoryIds = array();
                    foreach ($categories as $_cate) {
                        $arrayCategoryIds[] = $helperAnt->getCategory($_cate);
                    }
                    $product->setCategoryIds($arrayCategoryIds);
                }
                if($this->_checkAttribute("inventories",$data)) {
                    if($this->_checkAttribute("quantity",$data["inventories"])) {
                        $qty = $data["inventories"]["quantity"];
                        $managerStock=1;
                        if($this->_checkAttribute("manage_stock",$data)) {
                            $managerStock=$data["manage_stock"];
                        }
                        $product->setStockData(array(
                            'use_config_manage_stock' => 0,
                            'manage_stock' => $managerStock,
                            'is_in_stock' => 1,
                            'qty' => $qty
                        ));
                    }
                }
                $product->save();
            }
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
                if($this->_checkAttribute("special_price",$data)) {
                    $product->setPrice($data["special_price"]);
                }
                if($this->_checkAttribute("tags",$data)) {
                    $stringTags = "";
                    foreach ($data["tags"] as $_tags) {
                        $stringTags .= $_tags . ",";
                    }
                    $product->setMetaKeyword($stringTags);
                }
                if($this->_checkAttribute("meta",$data)) {
                    $stringMeta="";
                    foreach($data["meta"] as $_meta){
                        $stringMeta.=$_meta.",";
                    }
                    $product->setMetaDescription($stringMeta);
                }
                //Image
                $count=0;
                if($this->_checkAttribute("images",$data)) {
                    $dataImage=$data["images"];
                    foreach($dataImage as $_image) {
                        if($this->_checkAttribute("id",$_image) && $this->_checkAttribute("url",$_image) && $this->_checkAttribute("position",$_image)) {
                            $urlImageInput = $_image["url"];
                            $position = $_image["position"];
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
                            $isEmptyImage=true;
                            foreach ($items as $item) {
                                if ($item["position"] == $position) {
                                    $count++;
                                    $isEmptyImage=false;
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
                            if($isEmptyImage){
                                if ($count == 0){
                                    $product->addImageToMediaGallery( $absolutePathImage , array('image', 'thumbnail', 'small_image'), true, false );
                                }else {
                                    $product->addImageToMediaGallery( $absolutePathImage , null, true, false );
                                }
                                $count++;
                            }
                        }
                    }
                }
                //QTY
                if($this->_checkAttribute("inventories",$data)) {
                    if($this->_checkAttribute("quantity",$data["inventories"])) {
                        $qty = $data["inventories"]["quantity"];
                        $managerStock=1;
                        if($this->_checkAttribute("manage_stock",$data)) {
                            $managerStock=$data["manage_stock"];
                        }
                        $product->setStockData(array(
                            'use_config_manage_stock' => 0,
                            'qty' => $qty,
                            'manage_stock' => $managerStock, //manage stock
                            'is_in_stock' => 1 //Stock Availability
                        ));
                    }
                }
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
                            $helperAnt->createAttribute($nameAttribute, $nameAttribute, -1, -1, -1, $valStringArray);
                        } else {
                            $helperAnt->updateAttributeValue($nameAttribute, $valStringArray);
                        }
                    }
                }
                //assign simple product to configruable product
                $dataVariants=$data["variants"];
                if($dataVariants && is_array($dataVariants)) {
                    foreach ($dataVariants as $_variant) {
                        $id_variant = $_variant["id"];
                        $this->setSimpleProductToConfigruableProduct($id_variant, $_variant);
                    }
                }
                $product->save();
            }
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
    protected $_arrayIdSimpleProduct=array();
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
        if($product){
            $stringError.="Product has sku ".$data['sku']." has existed.Please check your sku data on product listing";
        }
        if($stringError != "") {
            $this->_criticalCustom($stringError, "400");
        }
        return $stringError;
    }
    protected function _criticalCustom($message, $code = null)
    {
        throw new Exception($message,$code);
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
    public function _createProduct(array $data){
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $helperAnt = $this;
            $defaultAttributeSetId = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getDefaultAttributeSetId();
            $product = Mage::getModel('catalog/product');
            $arrayToExclude=array("id","images","inventories","full_price","tags","tax","meta","manage_stock","manage_stock","special_price","product_options","product_type");
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
                    $product->setMetaDescription($data["meta"]);
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
                    $qty = $data["inventories"]["quantity"];
                    if($data["manage_stock"]==true ||  $data["manage_stock"]=="true"){
                        $data["manage_stock"]=1;
                    }
                    if($data["manage_stock"]==false ||  $data["manage_stock"]=="false") {
                        $data["manage_stock"]=0;
                    }
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
                    $product->setMetaDescription($data["meta"]);
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
                            $arrayImageInfor = $this->setImageProduct($urlImageInput);
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
                    if($data["manage_stock"]==true ||  $data["manage_stock"]=="true"){
                        $data["manage_stock"]=1;
                    }
                    if($data["manage_stock"]==false ||  $data["manage_stock"]=="false") {
                        $data["manage_stock"]=0;
                    }
                    $product->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => 1, //manage stock
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => $qty //qty
                        )
                    );
                    //Check Product Options is Exist Or Not
                    $product_options=$data["product_options"];
                    foreach($product_options as $p_opt){
                        $nameAttribute=$p_opt["name"];
                        $p_values=$p_opt["values"];
                        $valStringArray=array();
                        foreach($p_values as $_val){
                            $v=$_val["name"];
                            $valStringArray[]=$v;
                        }
                        if(!$this->checkExistAttribute($nameAttribute)) {
                            $this->createAttribute($nameAttribute,$nameAttribute,-1,-1,-1,$valStringArray);
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
    /**
     * Make the function helper for customer hash
     *
     */
    public function setTheHashCustomer($customerId){
        $customer=Mage::getModel('customer/customer')->load($customerId);
        $arrayCustomerHash=array();
        $firstName=$customer->getData("firstname");
        $lastName=$customer->getData("lastname");
        $arrayCustomerHash["customer_id"]=$customerId;
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
        $quote = $order->getQuote();
        if($taxOnFrontEnd !=null){
            $total_tax=$taxOnFrontEnd;
        }
        else{
            $total_tax=$order->getData("shipping_tax_amount");
        }
        if($status != null){
            $statusLabel=$status;
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
        $qtyParent=0;
        $priceParent=0;
        $taxAmountParent=0;
        $taxPercentParent=0;
        $disCountParent=0;

        foreach($order->getAllItems() as $_item){
            $arrayOtherDataProduct = array();
            if($_item->getData("parent_item_id")!= ""){
                $arrayOrder["items"][] = array(
                    "id" => $_item->getId(),
                    "name" => $_item->getData("name"),
                    "product_id" => $_item->getData("product_id"),
                    "sku" => $_item->getData("sku"),
                    "type" => "variant",
                    "shipments" => array("quantity" => $qtyParent),
                    "tax_amount" => $taxAmountParent,
                    "tax_percent" => $taxPercentParent,
                    "sale_price" => $priceParent,
                    "discount" => $disCountParent
                );
                foreach($_item->getData() as $key => $val){
                    if($key !="method_instance") {
                        $arrayOtherDataProduct[$key]=$val;
                    }
                }
                $arrayOrder["other_data_product"][]=$arrayOtherDataProduct;
                continue;
            }
            if($_item->getData("product_type")=="simple") {

                if(!Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($_item->getData("product_id"))) {
                    $arrayOrder["items"][] = array(
                        "id" => $_item->getId(),
                        "name" => $_item->getData("name"),
                        "product_id" => $_item->getData("product_id"),
                        "sku" => $_item->getData("sku"),
                        "type" => "product",
                        "shipments" => array("quantity" => $_item->getQtyOrdered()),
                        "sale_price" => $_item->getData("base_row_total_incl_tax"),
                        "tax_amount" => $_item->getData("tax_amount"),
                        "tax_percent" => $_item->getData("tax_percent"),
                        "discount" => $_item->getData("discount_amount")
                    );
                }else{
                    $arrayOrder["items"][] = array(
                        "id" => $_item->getId(),
                        "name" => $_item->getData("name"),
                        "product_id" => $_item->getData("product_id"),
                        "sku" => $_item->getData("sku"),
                        "type" => "variant",
                        "shipments" => array("quantity" => $_item->getQtyOrdered()),
                        "sale_price" => $_item->getData("base_row_total_incl_tax"),
                        "tax_amount" => $_item->getData("tax_amount"),
                        "tax_percent" => $_item->getData("tax_percent"),
                        "discount" => $_item->getData("discount_amount")
                    );
                }
                $qtyParent=0;
                $priceParent=0;
                $taxAmountParent=0;
                $taxPercentParent=0;
                $disCountParent=0;
            }else{
                $qtyParent=$_item->getQtyOrdered();
                $priceParent=$_item->getData("base_row_total_incl_tax");
                $taxAmountParent=$_item->getData("tax_amount");
                $taxPercentParent=$_item->getData("tax_percent");
                $disCountParent=$_item->getData("discount_amount");
                continue;
            }
            foreach($_item->getData() as $key => $val){
                if($key !="method_instance") {
                    $arrayOtherDataProduct[$key]=$val;
                }
            }
            $arrayOrder["other_data_product"][]=$arrayOtherDataProduct;
        }
        $billing_address=$order->getBillingAddress();
        $arrayBillingAddress=$this->getCustomerHashFromOrder($billing_address);
        $arrayOrder["billing_address"]=$arrayBillingAddress;
        $shippingAddress=$order->getShippingAddress();
        $arrayShippingAddress=$this->getCustomerHashFromOrder($shippingAddress);
        $arrayOrder["shipping_address"]=$arrayShippingAddress;
        $arrayOrder["payment"] = array ();
        $arrayOrder["payment"]["payment_code"]=$order->getPayment()->getMethodInstance()->getCode();
        $arrayOrder["payment"]["payment_title"]=$order->getPayment()->getMethodInstance()->getTitle();
        $arrayOrder["payment"]["payment_provider"]=$order->getPayment()->getData('cc_type');
        $arrayOrder["shipping_other_data"]=array();
        foreach($shippingAddress->getData() as $key => $val){
            $arrayOrder["shipping_other_data"][$key] = $val;
        }
        $arrayOrder["payment_other_data"]=array();
        $payment=$order->getPayment();
        foreach($payment->getData() as $key => $val){
            if($key!="method_instance") {
                $arrayOrder["payment_other_data"][$key] = $val;
            }
        }
        $arrayOrder["other_data"] = array();
        $order = Mage::getModel("sales/order")->load($order->getId());
        foreach($order->getData() as $key => $val){
            $arrayOrder["other_data"][$key] = $val;
        }

        return $arrayOrder;
    }
    public function getCustomerHashFromOrder($objectAddress){
        $customer=array();
        $customer["customer_id"]=$objectAddress->customer_id;
        $customer["first_name"]=$objectAddress->firstname;
        $customer["last_name"]=$objectAddress->lastname;
        $customer["full_name"]=$objectAddress->lastname;
        $customer["email"]=$objectAddress->email;
        $customer["phone"]=$objectAddress->telephone;
        $customer["customer_company"]=$objectAddress->company;
        $street_address=$objectAddress->street;
        $street=explode(".",$street_address);
        if(count($street) < 2){
            $street[0]=$street_address;
            $street[1]="";
        }
        $customer["address"]=array(
            "address1"  =>  $street[0],
            "address2"  =>  $street[1],
            "suburb"    =>  $objectAddress->region,
            "postcode"  =>  $objectAddress->postcode,
            "country"   =>  $objectAddress->country_id,
        );
        return $customer;
    }

    /***
     * @deprecated
     * @param Ant_Api_Model_Mysql4_Webhook $url Object from the
     * @param $postData
     */
    public function callUrl($url,$postData){
        $this->triggerHook($url, $postData);
        return;
    }

    /**
     * Trigger the webhook
     *
     * @param $webhook
     * @param $postData
     *
     * @return mixed
     */
    public function triggerWebhook($webhook, $postData){
        $requestUrl=$webhook->getData("ant_api_webhook_url");
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, JSON_OBJECT_AS_ARRAY);
    }

    public function rewriteUrl($name=null,$handle){
        //$url = preg_replace('#[^0-9a-z]+#i', '-', $name);
        //$url = strtolower($url);
        //return $url."-".$handle;
        return $handle;
    }
    public function setImageProduct($url){
        $image_name=basename($url); // replace https tp http
        $image_type = "jpg"; //find the image extension
        $filename   = "upload_api"; //give a new name, you can modify as per your requirement
        $directoryName = Mage::getBaseDir("var") . DS . $filename;
        if(!is_dir($directoryName)){
            //Directory does not exist, so lets create it.
            mkdir($directoryName, 0755);
        }
        $filePathOnDir   = Mage::getBaseDir('var') . DS  . $filename. DS . $image_name; //path for temp storage folder: ./media/import/ , absolute path
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
        return $data;
    }


    /***
     * @param $userId
     * @return Mage_Oauth_Model_Token
     * @throws Exception
     */
    public function autoGenerateOAuthForUser($userId){
        /** @var Mage_Oauth_Helper_Data $oauthHelper */
        $oauthHelper = Mage::helper('oauth');
        $consumerKey = $oauthHelper->generateConsumerKey();
        $consumerSecret = $oauthHelper->generateConsumerSecret();

        /** @var Mage_Oauth_Model_Consumer $consumerModel */
        $consumerModel = Mage::getModel('oauth/consumer');
        $consumerData = array(
            'name' => self::OAUTH_CONSUMER_NAME,
            'key' => $consumerKey,
            'secret' => $consumerSecret);
        $consumerModel->addData($consumerData);
        $consumerModel->save();

        /** @var Mage_Oauth_Model_Token $requestToken */
        $requestToken = Mage::getModel('oauth/token');
        $requestToken->createRequestToken($consumerModel->getId(), self::OAUTH_CALLBACK_URL);
        $requestToken->convertToAccess();

        $defaultStore           = Mage::app()->getDefaultStoreView();
        $useSecureUrlInFrontEnd = (bool) Mage::getStoreConfig(Mage_Core_Model_Url::XML_PATH_SECURE_IN_FRONT, $defaultStore);
        $urlConfigPath          = ($useSecureUrlInFrontEnd)? Mage_Core_Model_Url::XML_PATH_SECURE_URL : Mage_Core_Model_Url::XML_PATH_UNSECURE_URL;
        $url                    = Mage::getStoreConfig($urlConfigPath, $defaultStore);

        $requestToken->authorize($userId, Mage_Oauth_Model_Token::USER_TYPE_ADMIN);

        /** @var Mage_Core_Model_Config $configModel */
        $configModel = Mage::getModel('core/config');

        $configModel->saveConfig(self::XML_PATH_CONSUMER_KEY, $consumerKey);
        $configModel->saveConfig(self::XML_PATH_CONSUMER_SECRET, $consumerSecret);
        $configModel->saveConfig(self::XML_PATH_AUTHORIZATION_TOKEN, $requestToken->getToken());
        $configModel->saveConfig(self::XML_PATH_AUTHORIZATION_SECRET, $requestToken->getSecret());
        $configModel->saveConfig(self::XML_PATH_AUTHORIZATION_URL, $url);
        $configModel->saveConfig(self::XML_PATH_LAST_GENERATED, now());

        $cacheToClear = 'config';
        Mage::app()->getCacheInstance()->cleanType($cacheToClear);
        Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $cacheToClear));

        return $requestToken;
    }

    /***
     * Setup OAuth for the dedicated System User
     * @throws Exception
     */
    public function setupOAuth(){
        $user = Mage::getModel('admin/user')->load(Ant_Api_Model_Resource_Setup::ANT_ADMIN_USER_USERNAME,'username');
        if (!$user->getId()){
            //generated
            throw new Mage_Oauth_Exception($this->__(
                    "AntHQ system user not found, please ensure user %s exists",
                    Ant_Api_Model_Resource_Setup::ANT_ADMIN_USER_USERNAME
                )
            );
        }
        $this->autoGenerateOAuthForUser($user->getId());
    }


    /***
     * @deprecated Please use setupOAuth() instead
     * @param  bool $isSetup
     * @return Mage_Oauth_Model_Token
     * @throws Exception
     */
    public function autoGenerate($isSetup = false){
        try {
            $this->setupOAuth();
        }
        catch(Mage_Oauth_Exception $ex){
            Mage::logException($ex);
        }

    }

    /**
     * Get the webhook url for the given type of webhook and specify the entity id
     *
     * @param $type
     * @param $entityId
     *
     * @return null|string
     */
    public function getWebhookUrl($type, $entityId) {
        if (!$type) {
            return null;
        }
        if (!$entityId) {
            return null;
        }
        switch ($type) {
            case Ant_Api_Model_Webhook::ORDER_CREATE:
                $path = 'adminhtml/ant_api/syncOrder';
                $entity_key = 'order_id';
                break;
            case Ant_Api_Model_Webhook::CUSTOMER_CREATE:
                $path = 'adminhtml/ant_api/syncCustomer';
                $entity_key = 'customer_id';
                break;
            case Ant_Api_Model_Webhook::PRODUCT_CREATE:
                $path = 'adminhtml/ant_api/syncProduct';
                $entity_key = 'product_id';
                break;
            default:
                Mage::log('Unhandled webhook type: ' . $type, Zend_Log::NOTICE, 'exception.log');
                return null;
        }
        $url = Mage::helper("adminhtml")->getUrl($path, array($entity_key => $entityId));
        return $url;
    }

    /**
     * Get module version as a string
     * @return string
     */
    public function getModuleVersion() {
        $antConfig = Mage::getConfig()->getModuleConfig('Ant_Api');
        if (!isset($antConfig->version)) {
            Mage::log('Ant_Api module config is malformed. ' . json_encode($antConfig), Zend_Log::ERR, 'exception.log');
            return "Invalid Version";
        }
        return (string) $antConfig->version;
    }
}
