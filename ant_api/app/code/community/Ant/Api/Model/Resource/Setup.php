<?php
class Ant_Api_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup{

    const DEFAULT_ADMINISTRATOR_ROLE_ID = 1;
    const ANT_ADMIN_ROLE_NAME = 'Ant Admin Generated Role';

    const ANT_ADMIN_USER_USERNAME  = 'anthq_system_user';
    const ANT_ADMIN_USER_FIRSTNAME = 'AntHQ User';
    const ANT_ADMIN_USER_LASTNAME  = 'Used for Syncing';
    const ANT_ADMIN_USER_EMAIL     = 'support@anthq.com';

    const APACHE_HTACCESS_FILEPATH_PATTERN = 'apache/htaccess-{{version}}.dist';

    const ANTHQ_SETUP_FLAG_CODE = 'anthq_setup_ongoing';

    /**
     * @var Mage_Api2_Model_Acl_Global_Role
     */
    private $role;

    /**
     * @var Mage_Admin_Model_User
     */
    private $adminUser;


    /**
     * @return Mage_Api2_Model_Acl_Global_Role
     * @throws Exception
     */
    private function generateRestRole(){
        /** @var Mage_Api2_Model_Acl_Global_Role $roleModel */
        $roleModel = Mage::getModel('api2/acl_global_role');
        $roleModel->setData(
            array(
                'role_name' => self::ANT_ADMIN_ROLE_NAME
            )
        );
        $roleModel->save();

        $ruleModel = Mage::getModel('api2/acl_global_rule');
        $ruleModel->setData(
            array(
                'role_id' => $roleModel->getId(), 'resource_id' => "all"
            )
        );
        $ruleModel->save();

        return $roleModel;
    }

    /***
     * @return Mage_Api2_Model_Acl_Global_Role
     * @throws Exception
     */
    private function getRestRole(){
        if (!$this->role){
            $role = Mage::getModel('api2/acl_global_role')->load(self::ANT_ADMIN_ROLE_NAME, 'role_name');
            if (!$role->getId()){
                $role = $this->generateRestRole();
            }
            $this->role = $role;
        }
        return $this->role;
    }

    private function generateAdminPassword($length = 8){
        $chars = Mage_Core_Helper_Data::CHARS_PASSWORD_LOWERS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_UPPERS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_DIGITS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_SPECIALS;
        return Mage::helper('core')->getRandomString($length, $chars);
    }

    private function generateAdminUser(){

        /** @var Mage_Admin_Model_User $adminUserModel */
        $adminUser = Mage::getModel('admin/user');
        $adminUserData = array(
            'firstname'         => self::ANT_ADMIN_USER_FIRSTNAME,
            'lastname'          => self::ANT_ADMIN_USER_LASTNAME,
            'email'             => self::ANT_ADMIN_USER_EMAIL,
            'username'          => self::ANT_ADMIN_USER_USERNAME,
            'new_password'      => $this->generateAdminPassword(32),
            'api2_roles'        => array(
                                     $this->getRestRole()->getId()
                                   )
        );
        $adminUser->setData($adminUserData);
        //run time flag to force saving entered password
        $adminUser->setForceNewPassword(true);
        $adminUser->save();

        $adminUser->setRoleIds(array(1))->saveRelations();

        return $adminUser;
    }

    /**
     * Retrieves the AntHQ Admin User and generates if it's not present
     *
     * @return Mage_Admin_Model_User
     */
    private function getAdminUser(){
        if (!$this->adminUser){
            /** @var Mage_Admin_Model_User $adminUser */
            $adminUser = Mage::getModel('admin/user')->load(self::ANT_ADMIN_USER_USERNAME,'username');
            if (!$adminUser->getId()){
                $adminUser = $this->generateAdminUser();
            }
            $this->adminUser = $adminUser;
        }
        return $this->adminUser;
    }

    /***
     * Generates the Admin User, Rest Role, and All Oauth Requirements
     *
     * @throws Exception
     */
    public function generateRestRoleToOauth(){

        /** @var Mage_Admin_Model_User $adminUser */
        $adminUser = $this->getAdminUser();

        /** @var Ant_Api_Helper_Data $antHelper */
        $antHelper = Mage::helper('ant_api');
        $antHelper->autoGenerateOAuthForUser($adminUser->getId());
    }



}
