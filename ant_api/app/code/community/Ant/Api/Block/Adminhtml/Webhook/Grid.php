<?php

class Ant_Api_Block_Adminhtml_Webhook_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct(){
        parent::__construct();
        $this->setId('webhookGrid');
        $this->setDefaultSort('ant_api_webhook_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('ant_api/webhook')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns(){
        $this->addColumn('ant_api_webhook_id', array(
            'header'    => Mage::helper('ant_api')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'ant_api_webhook_id',
        ));

        $this->addColumn('ant_api_webhook_url', array(
            'header'    => Mage::helper('ant_api')->__('Web-Hook Url'),
            'align'     =>'left',
            'width'     => '200px',
            'index'     => 'ant_api_webhook_url',
        ));

        $this->addColumn('ant_api_webhook_action', array(
            'header'    => Mage::helper('ant_api')->__('Web-Hook Action'),
            'align'     => 'left',
            'width'     => '200px',
            'index'     => 'ant_api_webhook_action'
        ));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction(){
        $this->setMassactionIdField('ant_api_webhook_id');
        $this->getMassactionBlock()->setFormFieldName('webhook');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('ant_api')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('ant_api')->__('Are you sure?')
        ));
        return $this;
    }
}