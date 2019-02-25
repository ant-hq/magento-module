<?php
/** @var Ant_Api_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$tableName =  $installer->getTable('ant_api/webhook');
if (!$installer->getConnection()->isTableExists($tableName)) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('ant_api/webhook'))
        ->addColumn('ant_api_webhook_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Webhook ID')
        ->addColumn('ant_api_webhook_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
            'default' => ''
        ), 'Webhook Url')
        ->addColumn('ant_api_webhook_action', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
            'default' => ''
        ), 'Webhook Action')
        ->setComment('Webhooks Table');

    $installer->getConnection()->createTable($table);
}

//attempt to modify the .htaccess file

$installer->endSetup();