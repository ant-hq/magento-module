<?php
/** @var Ant_Api_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$tableName =  $installer->getTable('ant_api/webhook_cron_schedule');
if (!$installer->getConnection()->isTableExists($tableName)) {
    $table = $installer->getConnection()
                       ->newTable($installer->getTable('ant_api/webhook_cron_schedule'))
                       ->addColumn('schedule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                           'identity' => true,
                           'unsigned' => true,
                           'nullable' => false,
                           'primary' => true,
                       ), 'Schedule ID')
                       ->addColumn('webhook_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                           'nullable' => false,
                       ), 'Webhook ID Link')
                       ->addColumn('checksum', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                           'nullable' => false,
                           'default' => ''
                       ), 'Checksum')
                       ->addColumn('request_data', Varien_Db_Ddl_Table::TYPE_VARCHAR, 1024, array(
                           'nullable' => false,
                           'default' => ''
                       ), 'Request Data')
                       ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, 4, array(
                           'nullable' => false,
                           'default' => 0
                       ), 'Status')
                       ->addColumn('response', Varien_Db_Ddl_Table::TYPE_VARCHAR, 1024, array(
                           'nullable' => true,
                           'default' => ''
                       ), 'Response')
                       ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                           'nullable' => true,
                           'default' => ''
                       ), 'Identifier to represent the entity, webhook action and entity_id')
                       ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                       ), 'Creation Time')
                       ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                       ), 'Update Time')
                       ->setComment('Ant HQ Process Webhooks Table. Handled by Cron');

    $installer->getConnection()->createTable($table);
}

$installer->endSetup();