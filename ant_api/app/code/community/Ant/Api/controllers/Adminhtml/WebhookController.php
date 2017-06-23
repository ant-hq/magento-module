<?php
class Ant_Api_Adminhtml_WebhookController extends Mage_Adminhtml_Controller_Action{
    protected function _initAction(){
        $this->loadLayout()->_setActiveMenu("api/webhook")->_addBreadcrumb($this->__('Web-Hooks'),$this->__("Web-Hooks"));
        return $this;
    }
    public function indexAction(){
        $this->_initAction()->renderLayout();
    }
    public function massDeleteAction() {
        $webhookIds = $this->getRequest()->getParam('webhook');
        if(!is_array($webhookIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($webhookIds as $_webhook_id) {
                    $webhook = Mage::getModel('ant_api/webhook')->load($_webhook_id);
                    $webhook->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($webhookIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}