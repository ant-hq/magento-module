<?php
class Ant_Api_Model_Observer{
    public function ProductSaveAfter($observer){
        $product = $observer->getProduct();
        $idProduct=$product->getId();
        $urlDetected=Mage::helper('core/url')->getCurrentUrl();
        $arrayCreate=explode("set",$urlDetected);
        $isCreate=false;
        if(count($arrayCreate) > 1){
            $isCreate=true;
        }
        $productType=$product->getTypeId();
        $helperAntApi=Mage::helper("ant_api");
        switch ($productType){
            case "simple":
                if($isCreate){
                    $postData=$helperAntApi->setTheHashProductSimple($idProduct);
                    $urlOfCreate=$helperAntApi->getDataWebHook("product.create");
                    foreach($urlOfCreate as $_url){
                        $helperAntApi->callUrl($_url,$postData);
                    }
                }
                else{
                    $postData=$helperAntApi->setTheHashProductSimple($idProduct);
                    $urlOfCreate=$helperAntApi->getDataWebHook("product.update");
                    foreach($urlOfCreate as $_url){
                        $helperAntApi->callUrl($_url,$postData);
                    }
                }
                break;
            case "configurable":
                if($isCreate){
                    $postData=$helperAntApi->setTheHashConfigruableProduct($idProduct);
                    $urlOfCreate=$helperAntApi->getDataWebHook("product.create");
                    foreach($urlOfCreate as $_url){
                        $helperAntApi->callUrl($_url,$postData);
                    }
                }
                else{
                    $postData=$helperAntApi->setTheHashConfigruableProduct($idProduct);
                    $urlOfCreate=$helperAntApi->getDataWebHook("product.update");
                    foreach($urlOfCreate as $_url){
                        $helperAntApi->callUrl($_url,$postData);
                    }
                }
                break;
        }
    }
    public function ProductDeleteAfter($observer){
        $product = $observer->getProduct();
        $idProduct=$product->getId();
        $helperAntApi=Mage::helper("ant_api");
        $urlOfdelete=$helperAntApi->getDataWebHook("product.delete");
        $postData=array("id_product"=>$idProduct);
        foreach($urlOfdelete as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    public function PrepareSaveCustomerAfterInAdmin($observer){
        $customer=$observer->getCustomer();
        $isNewCustomer = $customer->isObjectNew();
        if($isNewCustomer){
            Mage::register('is_new_customer',"new");
        }
        else{
            Mage::register('is_new_customer',"edit");
        }
    }
    public function SaveCustomerAfterInAdmin($observer){
        $customer=$observer->getCustomer();
        if(Mage::registry('is_new_customer')=="new"){
            $helperAntApi=Mage::helper("ant_api");
            $postData=$helperAntApi->setTheHashCustomer($customer->getId());
            $urlOfCreateCustomer=$helperAntApi->getDataWebHook("customer.create");
            foreach($urlOfCreateCustomer as $_url){
                $helperAntApi->callUrl($_url,$postData);
            }
        }
        else{
            $helperAntApi=Mage::helper("ant_api");
            $postData=$helperAntApi->setTheHashCustomer($customer->getId());
            $urlOfCreateCustomer=$helperAntApi->getDataWebHook("customer.update");
            foreach($urlOfCreateCustomer as $_url){
                $helperAntApi->callUrl($_url,$postData);
            }
        }
        Mage::unregister('is_new_customer');
    }
    public function DeleteCustomerAfterInAdmin($observer){
        $customer=$observer->getCustomer();
        $postData=array("id_customer"=>$customer->getId());
        $helperAntApi=Mage::helper("ant_api");
        $urlOfDeleteCustomer=$helperAntApi->getDataWebHook("customer.delete");
        foreach($urlOfDeleteCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    //Front End For Customer
    public function SaveCustomerAfterInFrontEnd($observer){
        $customer=$observer->getCustomer();
        $helperAntApi=Mage::helper("ant_api");
        $postData=$helperAntApi->setTheHashCustomer($customer->getId());
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("customer.create");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    public function UpdateCustomerAddressAfterInFrontEnd($observer){
        $customer=$observer->getCustomerAddress();
        $helperAntApi=Mage::helper("ant_api");
        $postData=$helperAntApi->setTheHashCustomer($customer->getCustomerId());
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("customer.update");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    public function UpdateCustomerInfoAfterInFrontEnd($observer){
        $customer=$observer->getCustomer();
        $helperAntApi=Mage::helper("ant_api");
        $postData=$helperAntApi->setTheHashCustomer($customer->getId());
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("customer.update");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    //End Front End For Customer
    public function SaveOrderAfter($observer){
        $order=$observer->getOrder();
        $helperAntApi=Mage::helper("ant_api");
        $postData=$helperAntApi->setTheHashOrder($order);
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("order.create");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    public function SaveShippmentOrderAfter($observer){
        $shipment = $observer->getEvent()->getShipment();
        $order=$shipment->getOrder();
        $helperAntApi=Mage::helper("ant_api");
        $postData=$helperAntApi->setTheHashOrder($order,null,Mage_Sales_Model_Order::STATE_PROCESSING);
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("order.update");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    public function SaveInvoiceOrderAfter($observer){
        $invoice = $observer->getEvent()->getInvoice();
        $order=$invoice->getOrder();
        $helperAntApi=Mage::helper("ant_api");
        $postData=$helperAntApi->setTheHashOrder($order,null,Mage_Sales_Model_Order::STATE_COMPLETE);
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("order.update");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    public function SaveCreditMemoOrderAfter($observer){
        $invoice = $observer->getEvent()->getCreditmemo();
        $order=$invoice->getOrder();
        $helperAntApi=Mage::helper("ant_api");
        $postData=$helperAntApi->setTheHashOrder($order,null,Mage_Sales_Model_Order::STATE_CLOSED);
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("order.update");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    public function SaveHoldOrderAfter($observer){
        $order=$observer->getHold();
        $helperAntApi=Mage::helper("ant_api");
        $postData=$helperAntApi->setTheHashOrder($order,null,Mage_Sales_Model_Order::STATE_HOLDED);
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("order.update");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
    public function SaveOrderAfterOnFrontend($observer){
        $order = $observer->getEvent()->getOrder();
        $helperAntApi=Mage::helper("ant_api");
        $tax_total=Mage::helper('checkout')->getQuote()->getShippingAddress()->getData('tax_amount');
        $postData=$helperAntApi->setTheHashOrder($order,$tax_total);
        $urlOfCreateCustomer=$helperAntApi->getDataWebHook("order.create");
        foreach($urlOfCreateCustomer as $_url){
            $helperAntApi->callUrl($_url,$postData);
        }
    }
}