<?php
class Ant_Api_Model_Api2_ProductImage_Rest_Admin_V1 extends Ant_Api_Model_Api2_ProductImage
{
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

    /**
     * @param $absolutePathImage
     * @return string
     */
    private function getMimeType($absolutePathImage)
    {
        $mimeType = '';
        if (file_exists($absolutePathImage)) {
            $pathInfo = pathinfo($absolutePathImage);
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
        return $mimeType;
    }


    protected function _delete(){
        $idProduct = $this->getRequest()->getParam("id_product");
        $position  = $this->getRequest()->getParam("position");
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

            $product       = Mage::getModel("catalog/product")->load($idProduct);
            $galleryData   = $product->getMediaGalleryImages();
            $mediaApi      = $this->getProductAttributeMediaApiModel();

            $indexCheck    = 1;
            foreach ($galleryData as &$image) {
                if($image->getId() == $position){
                    break;
                }
                $indexCheck++;
            }
            $items = $mediaApi->items($idProduct,Mage_Core_Model_App::ADMIN_STORE_ID);

            $index = 1;
            foreach($items as $item) {
                if($index == $indexCheck){
                    Mage::register('is_new_product_api',"noevent");
                    $mediaApi->remove($idProduct,$item['file'],0,null);
                }
                $index++;
            }

        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }
    protected function _criticalCustom($message, $code = null)
    {
        throw new Exception($message,$code);
    }

    private function getImageBase64DataFromUrl($url){
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $encodedData = base64_encode(curl_exec($curlSession));
        curl_close($curlSession);
        return $encodedData;
    }

    protected function _update(array $data){
        $idProduct = $this->getRequest()->getParam("id_product");
        $position  = $this->getRequest()->getParam("position");

        try {
            $product   = Mage::getModel("catalog/product")->load($idProduct);

            //Image
            $urlImageInput = $data["url"];
            //$position      = $data["position"];
            $label         = $data["label"];
            $types         = $data["types"];
            $exclude       = (!isset($data["exclude"]))? 0 : $data["exclude"];

            //Image
            $mimeType      = "";

            $arrayImageInfor   = $this->getAntHelper()->setImageProduct($urlImageInput);
            $nameImage         = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
            $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
            $mediaApi          = $this->getProductAttributeMediaApiModel();

            $mimeType = $this->getMimeType($absolutePathImage);


            $galleryId = $data['id'];
            $galleryItem = $product->getMediaGalleryImages()->getItemById($galleryId);
            $isImageRemote =  (filter_var($absolutePathImage, FILTER_VALIDATE_URL));
            $base64Content = ($isImageRemote)? $this->getImageBase64DataFromUrl($absolutePathImage) : base64_encode(file_get_contents($absolutePathImage));

            $dataImage = array(
                'file' => array(
                    'name'    => $nameImage,
                    'content' => $base64Content,
                    'mime'    => $mimeType
                ),
                'label'    => $label,
                'position' => $data["position"],
                'types'    => $types,
                'exclude'  => $exclude
            );
            Mage::register('is_new_product_api',"noevent");
            $mediaApi->update($idProduct, $galleryItem['file'], $dataImage, Mage_Core_Model_App::ADMIN_STORE_ID);


//            $galleryData = $product->getMediaGalleryImages();
//            $indexCheck  = 1;
//            foreach ($galleryData as &$image) {
//                if($image->getId() == $position){
//                    break;
//                }
//                $indexCheck++;
//            }
//            Mage::register('is_new_product_api',"noevent");
//            $items = $mediaApi->items($idProduct,Mage_Core_Model_App::ADMIN_STORE_ID);
//
//            $index     = 1;
//            $isUpdated = false;
//            foreach($items as $item) {
//                if($indexCheck == $index){
//                    $dataImage = array(
//                        'file' => array(
//                            'name'    => $nameImage,
//                            'content' => base64_encode(file_get_contents($absolutePathImage)),
//                            'mime'    => $mimeType
//                        ),
//                        'label'    => $label,
//                        'position' => $data["position"],
//                        'types'    => $types,
//                        'exclude'  => $exclude
//                    );
//                    $mediaApi->update($idProduct, $item['file'], $dataImage, Mage_Core_Model_App::ADMIN_STORE_ID);
//                    $isUpdated = true;
//                }
//                $index++;
//            }
            //if($isUpdated == false){
            //    $this->_criticalCustom("There is no image id on product [$idProduct]. Please check your data request.",400);
            //}else {

            //$product->save();
            //}
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }

    /** @var Ant_Api_Model_Rewrites_Mage_Catalog_Model_Product_Attribute_Media_Api */
    private $_productAttributeMediaApiModel;

    /***
     * Returns the Media API Model
     * @return Ant_Api_Model_Rewrites_Mage_Catalog_Model_Product_Attribute_Media_Api
     */
    private function getProductAttributeMediaApiModel(){
        if (!$this->_productAttributeMediaApiModel){
            $this->_productAttributeMediaApiModel = Mage::getModel("ant_api/rewrites_mage_catalog_model_product_attribute_media_api");
        }
        return $this->_productAttributeMediaApiModel;
    }



    protected function _create(array $data){

        // attribute set and product type cannot be updated
        try {
            $idProduct     = $this->getRequest()->getParam("id_product");
            //Image
            $urlImageInput = $data["url"];
            $position      = $data["position"];
            $label         = $data["label"];
            $types         = $data["types"];
            $exclude       = (!isset($data["exclude"]))? 0 : $data["exclude"];

            $arrayImageInfor   = $this->getAntHelper()->setImageProduct($urlImageInput);
            $nameImage         = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
            $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];

            $mimeType = $this->getMimeType($absolutePathImage);

            $isImageRemote =  (filter_var($absolutePathImage, FILTER_VALIDATE_URL));
            $base64Content = ($isImageRemote)? $this->getImageBase64DataFromUrl($absolutePathImage) : base64_encode(file_get_contents($absolutePathImage));


            $dataImage = array(
                'file' => array(
                    'name' => $nameImage,
                    'content' => $base64Content,
                    'mime' => $mimeType
                ),
                'label'    => $label,
                'position' => $position,
                'types'    => $types,
                'exclude'  => $exclude
            );
            Mage::register('is_new_product_api',"noevent");
            $this->getProductAttributeMediaApiModel()->create($idProduct, $dataImage,Mage_Core_Model_App::ADMIN_STORE_ID,null);

        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }


}