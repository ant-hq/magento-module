<?php
class Ant_Api_AntController extends Mage_Adminhtml_Controller_Action{
    public function oauthkeysAction(){
        $reclaimHelper = Mage::helper('ant_api');
        $token=$reclaimHelper->autoGenerate();
        // redirect to the extension configuration page
        $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/ant_api_config'));
        return $this;
    }
}