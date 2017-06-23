<?php
class Ant_Api_Model_Api2_ProductImage_Rest_Guest_V1 extends Ant_Api_Model_Api2_ProductImage
{

    protected function _delete(){
        $idProduct=$this->getRequest()->getParam("id_product");
        $position=$this->getRequest()->getParam("position");
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
            $detailProduct=Mage::getModel("catalog/product")->load($idProduct);
            $galleryData = $detailProduct->getMediaGalleryImages();
            $indexCheck=1;
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
    protected function _update(array $data){
        $idProduct=$this->getRequest()->getParam("id_product");
        $position=$this->getRequest()->getParam("position");
        $product = Mage::getModel("catalog/product")->load($idProduct);
        // attribute set and product type cannot be updated
        try {
            //Image
            $urlImageInput=$data["url"];
            $label=$data["label"];
            $exclude = 0;
            if(isset($data["exclude"])){
                $exclude = $data["exclude"];
            }
            $types=$data["types"];
            $helperAnt=Mage::helper("ant_api");
            $arrayImageInfor=$helperAnt->setImageProduct($urlImageInput);
            $nameImage=$arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
            $absolutePathImage=$arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
            $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
            $mimeType="";
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
            $detailProduct=Mage::getModel("catalog/product")->load($idProduct);
            $galleryData = $detailProduct->getMediaGalleryImages();
            $indexCheck=1;
            foreach ($galleryData as &$image) {
                if($image->getId() == $position){
                    break;
                }
                $indexCheck++;
            }
            Mage::register('is_new_product_api',"noevent");
            $items = $mediaApi->items($idProduct,Mage_Core_Model_App::ADMIN_STORE_ID);
            $index=1;
            $isUpdated=false;
            foreach($items as $item) {
                if($indexCheck == $index){
                    $dataImage = array(
                        'file' => array(
                            'name' => $nameImage,
                            'content' => base64_encode(file_get_contents($absolutePathImage)),
                            'mime' => $mimeType
                        ),
                        'label' => $label,
                        'position' => $data["position"],
                        'types' => $types,
                        'exclude' => $exclude
                    );
                    $mediaApi->update($idProduct, $item['file'],$dataImage,Mage_Core_Model_App::ADMIN_STORE_ID);
                    $isUpdated = true;
                }
                $index++;
            }
            if($isUpdated == false){
                $this->_criticalCustom("There is no image id on product [$idProduct]. Please check your data request.",400);
            }else {
                Mage::register('is_new_product_api',"noevent");
                $product->save();
            }
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
    protected function _create(array $data){
        /*Case 1:
         * try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $idProduct=$this->getRequest()->getParam("id_product");
            $position=$this->getRequest()->getParam("position");
            $product = Mage::getModel("catalog/product")->load($idProduct);
            $urlImageInput=$data["url"];
            $label=$data["label"];
            $types=$data["types"];
            $helperAnt = Mage::helper("ant_api");
            $arrayImageInfor = $helperAnt->setImageProduct($urlImageInput);
            $nameImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
            $absolutePathImage = $arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
            $product->setMediaGallery(array('images' => array(), 'values' => array()));
            $product->addImageToMediaGallery($absolutePathImage,$types, false, false);
            $product->save();
        }
        catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }*/
        $idProduct=$this->getRequest()->getParam("id_product");
        // attribute set and product type cannot be updated
        try {
            //Image
            $urlImageInput=$data["url"];
            $position=$data["position"];
            $label=$data["label"];
            $exclude = 0;
            if(isset($data["exclude"])){
                $exclude = $data["exclude"];
            }
            $types=$data["types"];
            $helperAnt=Mage::helper("ant_api");
            $arrayImageInfor=$helperAnt->setImageProduct($urlImageInput);
            $nameImage=$arrayImageInfor[Ant_Api_Helper_Data::KEY_NAME_IMAGE];
            $absolutePathImage=$arrayImageInfor[Ant_Api_Helper_Data::KEY_ABSOLUTE_PATH_IMG];
            $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
            $mimeType="";
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
            $dataImage = array(
                'file' => array(
                    'name' => $nameImage,
                    'content' => base64_encode(file_get_contents($absolutePathImage)),
                    'mime' => $mimeType
                ),
                'label' => $label,
                'position' => $position,
                'types' => $types,
                'exclude' => $exclude
            );
            Mage::register('is_new_product_api',"noevent");
            $mediaApi->create($idProduct,$dataImage,Mage_Core_Model_App::ADMIN_STORE_ID,null);
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
}