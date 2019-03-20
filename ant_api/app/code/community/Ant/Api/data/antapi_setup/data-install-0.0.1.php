<?php
/** @var Ant_Api_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$installer->generateRestRoleToOauth();
//detect if running apache
try{
    //$installer->tryEnableRestApiApache();
}
catch (Ant_Api_ApacheNotEditableException $e){
    Mage::logException($e);
}
$installer->endSetup();