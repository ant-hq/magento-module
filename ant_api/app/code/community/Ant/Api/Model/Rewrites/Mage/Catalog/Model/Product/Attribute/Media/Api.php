<?php
class Ant_Api_Model_Rewrites_Mage_Catalog_Model_Product_Attribute_Media_Api extends Mage_Catalog_Model_Product_Attribute_Media_Api
{
    /**
     * Update image data
     *
     * @param int|string $productId
     * @param string $file
     * @param array $data
     * @param string|int $store
     * @return boolean
     */
    public function update($productId, $file, $data, $store = null, $identifierType = null)
    {
        $data = $this->_prepareImageData($data);

        $product = $this->_initProduct($productId, $store, $identifierType);

        $gallery = $this->_getGalleryAttribute($product);

        if (!$gallery->getBackend()->getImage($product, $file)) {
            $this->_fault('not_exists');
        }

        if (isset($data['file']['mime']) && isset($data['file']['content'])) {
            if (!isset($this->_mimeTypes[$data['file']['mime']])) {
                $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid image type.'));
            }

            $fileContent = @base64_decode($data['file']['content'], true);
            if (!$fileContent) {
                $this->_fault('data_invalid', Mage::helper('catalog')->__('Image content is not valid base64 data.'));
            }

            unset($data['file']['content']);

            $ioAdapter = new Ant_Api_Helper_Io_File();
            try {
                $fileName = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product' . $file;
                $ioAdapter->open(array('path'=>dirname($fileName)));
                $ioAdapter->write(basename($fileName), $fileContent, 0666);

            } catch(Exception $e) {
                $this->_fault('not_created', Mage::helper('catalog')->__('Can\'t create image.'));
            }
        }

        $gallery->getBackend()->updateImage($product, $file, $data);

        if (isset($data['types']) && is_array($data['types'])) {
            $oldTypes = array();
            foreach ($product->getMediaAttributes() as $attribute) {
                if ($product->getData($attribute->getAttributeCode()) == $file) {
                    $oldTypes[] = $attribute->getAttributeCode();
                }
            }

            $clear = array_diff($oldTypes, $data['types']);

            if (count($clear) > 0) {
                $gallery->getBackend()->clearMediaAttribute($product, $clear);
            }

            $gallery->getBackend()->setMediaAttribute($product, $data['types'], $file);
        }

        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_updated', $e->getMessage());
        }

        return true;
    }

    /**
     * Create new image for product and return image filename
     *
     * @param int|string $productId
     * @param array $data
     * @param string|int $store
     * @return string
     */
    public function create($productId, $data, $store = null, $identifierType = null)
    {
        $data = $this->_prepareImageData($data);

        $product = $this->_initProduct($productId, $store, $identifierType);

        $gallery = $this->_getGalleryAttribute($product);

        if (!isset($data['file']) || !isset($data['file']['mime']) || !isset($data['file']['content'])) {
            $this->_fault('data_invalid', Mage::helper('catalog')->__('The image is not specified.'));
        }

        if (!isset($this->_mimeTypes[$data['file']['mime']])) {
            $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid image type.'));
        }

        $fileContent = @base64_decode($data['file']['content'], true);
        if (!$fileContent) {
            $this->_fault('data_invalid', Mage::helper('catalog')->__('The image contents is not valid base64 data.'));
        }

        unset($data['file']['content']);

        $tmpDirectory = Mage::getBaseDir('var') . DS . 'api' . DS . $this->_getSession()->getSessionId();

        if (isset($data['file']['name']) && $data['file']['name']) {
            $fileName  = $data['file']['name'];
        } else {
            $fileName  = 'image';
        }
        $fileName .= '.' . $this->_mimeTypes[$data['file']['mime']];

        $ioAdapter = new Ant_Api_Helper_Io_File();
        try {
            // Create temporary directory for api
            $ioAdapter->checkAndCreateFolder($tmpDirectory);
            $ioAdapter->open(array('path'=>$tmpDirectory));
            // Write image file
            $ioAdapter->write($fileName, $fileContent, 0666);
            unset($fileContent);

            // try to create Image object - it fails with Exception if image is not supported
            try {
                new Varien_Image($tmpDirectory . DS . $fileName);
            } catch (Exception $e) {
                // Remove temporary directory
                $ioAdapter->rmdir($tmpDirectory, true);

                throw new Mage_Core_Exception($e->getMessage());
            }

            // Adding image to gallery
            $file = $gallery->getBackend()->addImage(
                $product,
                $tmpDirectory . DS . $fileName,
                null,
                true
            );

            // Remove temporary directory
            $ioAdapter->rmdir($tmpDirectory, true);

            $gallery->getBackend()->updateImage($product, $file, $data);

            if (isset($data['types'])) {
                $gallery->getBackend()->setMediaAttribute($product, $data['types'], $file);
            }

            $product->save();
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $this->_fault('not_created', $e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_fault('not_created', Mage::helper('catalog')->__('Cannot create image.'));
        }

        return $gallery->getBackend()->getRenamedImage($file);
    }

}