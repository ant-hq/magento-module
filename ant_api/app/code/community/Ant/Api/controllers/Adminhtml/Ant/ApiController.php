<?php
/**
 *  NeoTheme (Neo Industries Pty Ltd)
 *
 *   NOTICE OF LICENSE
 *
 *   This source file is subject to Neo Industries Pty LTD Non-Distributable Software Modification License (NDSML)
 *   that is bundled with this package in the file LICENSE.txt.
 *   It is also available through the world-wide-web at this URL:
 *   http://www.neotheme.com.au/legal/licenses/NDSM.html
 *   If the license is not included with the package or for any other reason,
 *   you did not receive your licence please send an email to
 *   license@neotheme.com.au so we can send you a copy immediately.
 *
 *   This software comes with no warranty of any kind. By Using this software, the user agrees to hold
 *   Neo Industries Pty Ltd harmless of any damage it may cause.
 *
 *   @category    Modules
 *   @module      Ant_Api
 *   @copyright   Copyright (c) 2019 Neo Industries Pty Ltd (http://www.neotheme.com.au)
 *   @license     http://www.neotheme.com.au/  Non-Distributable Software Modification License(NDSML 1.0)
 */

class Ant_Api_Adminhtml_Ant_ApiController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Manually create product webhook to sync product to ant
     * @throws \Varien_Exception
     */
    public function syncProductAction() {
        $productId = $this->getRequest()->getParam('product_id', null);
        if (!$productId) {
            $this->_getSession()->addError($this->__('Product ID is required'));
            $this->_redirect('*/catalog_product/index');
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        $helperAntApi=Mage::helper("ant_api");
        $isCreate = false;
        $productType = $product->getTypeId();
        $url= Mage::helper('core/url')->getCurrentUrl();
        $isPopup = explode("popup",$url);
        $isCreateQuick = explode("quickCreate",$url);
        switch ($productType) {
            case "simple":
                if ($isCreate) {
                    if(count($isPopup) < 2 && count($isCreateQuick) < 2) {
                        $postData = $helperAntApi->setTheHashProductSimple($productId);
                        $urlOfCreate = $helperAntApi->getDataWebHook(Ant_Api_Model_Webhook::PRODUCT_CREATE);
                        foreach($urlOfCreate as $_url){
                            $response = $helperAntApi->triggerWebhook($_url,$postData);
                            if (!$response['result']) {
                                $helperAntApi->logHistory($response['message'], true);
                                $this->_getSession()->addError($this->__('Failed to sync product with id: ' . $productId));
                                $this->_redirect('*/catalog_product/edit', array('id' => $productId));
                                return;
                            }
                        }
                    }
                } else {
                    if(!Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId)) {
                        $postData = $helperAntApi->setTheHashProductSimple($productId);
                        $urlOfUpdate = $helperAntApi->getDataWebHook(Ant_Api_Model_Webhook::PRODUCT_UPDATE);
                        foreach ($urlOfUpdate as $_url) {
                            $response = $helperAntApi->triggerWebhook($_url,$postData);
                            if (!$response['result']) {
                                $helperAntApi->logHistory($response['message'], true);
                                $this->_getSession()->addError($this->__('Failed to sync product with id: ' . $productId));
                                $this->_redirect('*/catalog_product/edit', array('id' => $productId));
                                return;
                            }
                        }
                    } else {
                        $ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
                        $postData = $helperAntApi->setTheHashConfigruableProduct($ids[0]);
                        $urlOfUpdate = $helperAntApi->getDataWebHook(Ant_Api_Model_Webhook::PRODUCT_UPDATE);
                        foreach ($urlOfUpdate as $_url) {
                            $response = $helperAntApi->triggerWebhook($_url,$postData);
                            if (!$response['result']) {
                                $helperAntApi->logHistory($response['message'], true);
                                $this->_getSession()->addError($this->__('Failed to sync product with id: ' . $productId));
                                $this->_redirect('*/catalog_product/edit', array('id' => $productId));
                                return;
                            }
                        }
                    }
                }
                break;
            case "configurable":
                if ($isCreate) {
                    $postData = $helperAntApi->setTheHashConfigruableProduct($productId);
                    $urlOfCreate = $helperAntApi->getDataWebHook(Ant_Api_Model_Webhook::PRODUCT_CREATE);
                    foreach($urlOfCreate as $_url){
                        $response = $helperAntApi->triggerWebhook($_url,$postData);
                        if (!$response['result']) {
                            $helperAntApi->logHistory($response['message'], true);
                            $this->_getSession()->addError($this->__('Failed to sync product with id: ' . $productId));
                            $this->_redirect('*/catalog_product/edit', array('id' => $productId));
                            return;
                        }
                    }
                } else {
                    $postData = $helperAntApi->setTheHashConfigruableProduct($productId);
                    $urlOfUpdate = $helperAntApi->getDataWebHook(Ant_Api_Model_Webhook::PRODUCT_UPDATE);
                    foreach ($urlOfUpdate as $_url) {
                        $response = $helperAntApi->triggerWebhook($_url,$postData);
                        if (!$response['result']) {
                            $helperAntApi->logHistory($response['message'], true);
                            $this->_getSession()->addError($this->__('Failed to sync product with id: ' . $productId));
                            $this->_redirect('*/catalog_product/edit', array('id' => $productId));
                            return;
                        }
                    }
                }
                break;
        }

        $this->_getSession()->addSuccess($this->__('Product has been synchronised with AntHQ'));
        $this->_redirect('*/catalog_product/edit', array('id' => $productId));
        return;
    }

    /**
     * Manually create order webhook to sync order to ant
     * @throws \Varien_Exception
     */
    public function syncOrderAction() {
        $orderId = $this->getRequest()->getParam('order_id', null);
        if (!$orderId) {
            $this->_getSession()->addError($this->__('Order ID is required'));
            $this->_redirect('*/sales_order/view');
            return;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        $helperAntApi=Mage::helper("ant_api");
        try {
            $postData=$helperAntApi->setTheHashOrder($order,null,null);
        } catch (\Exception $exception) {
            $postData = [];
        }
        $createOrderWebhookCollection=$helperAntApi->getDataWebHook(Ant_Api_Model_Webhook::ORDER_CREATE);
        foreach($createOrderWebhookCollection as $webhook){
            $response = $helperAntApi->triggerWebhook($webhook,$postData);
            if (!$response['result']) {
                $helperAntApi->logHistory($response['message'], true);
                $this->_getSession()->addError($this->__('Failed to sync order with id: ' . $orderId));
                $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
                return;
            }
        }

        $this->_getSession()->addSuccess($this->__('Order has been synchronised with AntHQ'));
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        return;
    }

}