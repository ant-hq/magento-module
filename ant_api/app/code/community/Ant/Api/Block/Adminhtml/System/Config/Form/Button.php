<?php
class Ant_Api_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $oauthKeysUrl = Mage::helper('adminhtml')->getUrl('adminhtml/ant/oauthkeys');

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'ant_api_generate_oauth_tokens_button',
                'label'     => 'Generate OAuth Tokens',
                'onclick'   => 'setLocation(\'' . $oauthKeysUrl . '\');'
            ));

        return $button->toHtml();
    }
}
