<?php
class Ant_Api_AntController extends Mage_Adminhtml_Controller_Action{

    /** @var Ant_Api_Helper_Data */
    private $_helper;

    /**
     * @return Ant_Api_Helper_Data
     */
    private function getHelper(){
        if (!$this->_helper) {
            /** @var Ant_Api_Helper_Data _helper */
            $this->_helper = Mage::helper('ant_api');
        }
        return $this->_helper;
    }

    /***
     * Controller action for regenerate button in backend
     * @return $this
     * @throws Exception
     */
    public function oauthkeysAction(){

        try {
            $helper = $this->getHelper();
            $helper->setupOAuth();
            $this->_getSession()->addSuccess($helper->__('OAuth Successfully Regenerated'));
        }
        catch(Mage_Oauth_Exception $e){
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
        }
        // redirect to the extension configuration page
        $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/ant_api_config'));
        return $this;
    }
}