<?php
class Ant_Api_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup{
    public function generateConsumerkeyAndSecretKey($admin_user){
        $reclaimHelper = Mage::helper('ant_api');
        $token=$reclaimHelper->autoGenerate(true);
    }
    public function generateRestRoleToOauth(){
        $role_data = array('role_name' => "Ant Admin Generated Role");
        $role_model = Mage::getModel('api2/acl_global_role')->setData($role_data);
        $role_id=$role_model->save()->getId();
        $rule_data = array('role_id' => $role_id, 'resource_id' => "all");
        $rule_model = Mage::getModel('api2/acl_global_rule')->setData($rule_data);
        $rule_model->save()->getId();
        $admin_user_collection = Mage::getModel('admin/user')->getCollection();
        $prefix = Mage::getConfig()->getTablePrefix();
        foreach($admin_user_collection as $admin_user) {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->query(sprintf("INSERT INTO `%sapi2_acl_user` (`admin_id`, `role_id`) VALUES ('%s', '%s') ON DUPLICATE KEY UPDATE `role_id` = '%s'", (string)$prefix, $admin_user->getId(), $role_id, $role_id));
            $this->generateConsumerkeyAndSecretKey($admin_user->getId());
        }
    }
}
