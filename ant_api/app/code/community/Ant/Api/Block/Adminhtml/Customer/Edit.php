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
class Ant_Api_Block_Adminhtml_Customer_Edit extends Mage_Adminhtml_Block_Customer_Edit
{
    public function __construct()
    {
        $syncCustomerAntUrl = Mage::helper('ant_api')->getWebhookUrl(Ant_Api_Model_Webhook::CUSTOMER_CREATE, $this->getCustomerId());
        $onclickJs = 'deleteConfirm(\'' . 'Sync to Ant?' . '\', \'' . $syncCustomerAntUrl . '\');';


        $this->_addButton('sync_ant_customer', array(
            'label'    => Mage::helper('customer')->__('Sync With Ant'),
            'onclick' => $onclickJs,
            'class'  => 'ant-hq-sync-button'
        ));
        parent::__construct();
    }

}
