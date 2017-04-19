<?php
$installer = $this;
$installer->startSetup();
$installer->generateRestRoleToOauth();
$installer->run("
CREATE TABLE IF NOT EXISTS `ant_api_webhook` (
  `ant_api_webhook_id` int(11) unsigned NOT NULL auto_increment,
  `ant_api_webhook_url` varchar(255) NOT NULL default '',
  `ant_api_webhook_action` varchar(255) NOT NULL default '',
  PRIMARY KEY (`ant_api_webhook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();