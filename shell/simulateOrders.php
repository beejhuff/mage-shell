<?php

require_once 'ex.php';
/**
 * Class Ex_Shell_SimulateOrders
 *
 * @category    Ex
 * @package     Ex_Shell
 * @author      Suky <suky3plex@outlook.com> (http://ex.pendabl.es)
 * @license     http://unlicense.org  Unlicensed Free Software
 */
class Ex_Shell_SimulateOrders extends Ex_Shell_Abstract
{

    /**
     * Run script
     */
    public function run()
    {
        $i = 1;
        $n = 100;

        /**
         * fetch number of iterations as argument
         */
        if($this->getArg('n')){
            $n = $this->getArg('n');
        }

        while ($i <= $n) {
            $this->_createOrder();
            echo microtime(true) . ": $i \n";
            $i++;
        }
    }

    /**
     * @todo: Shipping method: flatrate has to be enabled
     * @todo: Payment method: checkmo has to be enabled
     * @todo: Disable stock management
     * @throws Exception
     */
    private function _createOrder()
    {
        $store = Mage::app()->getStore('default');

        $customer = $this->getCustomer();

        $customer->setStore($store);

        $quote = Mage::getModel('sales/quote');
        $quote->setStore($store);
        $quote->assignCustomer($customer);

        $product1 = Mage::getModel('catalog/product')->load($this->_getRandProductId());
        $buyInfo1 = array('qty' => 1);

        $quote->addProduct($product1, new Varien_Object($buyInfo1));
        $quote->getBillingAddress()->addData($customer->getPrimaryBillingAddress()->toArray());

        $shippingAddress = $quote->getShippingAddress()->addData($customer->getPrimaryShippingAddress()->toArray());
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate')
            ->setPaymentMethod('checkmo');

        $quote->getPayment()->importData(array('method' => 'checkmo'));
        $quote->collectTotals()->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();

        $items = Mage::getResourceModel('sales/order_item_collection')->setOrderFilter($order);
        $itemQty = array();
        foreach($items as $item){
            $itemQty[$item["item_id"]] = (string)(int)$item["qty_ordered"];
        }

        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
        $shipment->register();

        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->register();

        $transaction = $this->_getTransaction();
        $transaction->addObject($invoice->getOrder());
        $transaction->addObject($shipment);
        $transaction->addObject($invoice);

        try {
            $transaction->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Customer needs to exist in Magento system with billing and shipping addresses associated
     * @return Mage_Customer_Model_Customer
     * @throws Exception
     */
    public function getCustomer()
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');
        $customer->load($this->_getRandCustomerId());

        if(!$customer->getPrimaryBillingAddress()->getId()){
            return $this->getCustomer();
        }

        if(!$customer->getPrimaryShippingAddress()->getId()){
            return $this->getCustomer();
        }

        return $customer;
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f simulateOrders.php -- [options]

  --n <orders>      Number of orders, shipments and invoices to generate
  <orders>          int

  help              This help

USAGE;
    }

}

$shell = new Ex_Shell_SimulateOrders();
$shell->run();
