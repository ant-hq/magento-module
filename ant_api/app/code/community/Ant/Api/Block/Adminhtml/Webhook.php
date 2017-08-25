<?php
class Ant_Api_Block_Adminhtml_Webhook extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct(){
        parent::__construct();
        $this->_controller="adminhtml_webhook";
        $this->_blockGroup="ant_api"; // thường hay có lỗi setSaveParametersInSession cho nên block group là tên module
        $this->_headerText=$this->__("Web Hooks");
        $this->_removeButton('add');
    }
}

