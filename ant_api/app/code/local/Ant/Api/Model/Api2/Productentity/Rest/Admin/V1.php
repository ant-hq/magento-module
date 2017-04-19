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
            $product->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }
    protected function _update(array $data){
        $idProduct=$this->getRequest()->getParam("id_product");
        $product = Mage::getModel("catalog/product")->load($idProduct);
        // attribute set and product type cannot be updated
        $arrayToExclude=array("id","images","inventories","tax","full_price");
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
                if($this->_checkAttribute("inventories",$data)) {
                    if($this->_checkAttribute("quantity",$data["inventories"])) {
                        $qty = $data["inventories"]["quantity"];
                        $product->setStockData(array('qty' => $qty));
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
                        $product->setStockData(array('qty' => $qty));
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
    public function setSimpleProductToConfigruableProduct($idProduct,$data){
        $product = Mage::getModel("catalog/product")->load($idProduct);
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
}