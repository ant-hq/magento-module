<?php
/** @var Ant_Api_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$installer->generateRestRoleToOauth();
$installer->endSetup();