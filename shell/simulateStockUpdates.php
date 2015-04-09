<?php

require_once 'ex.php';
/**
 * Class Ex_Shell_SimulateStockUpdates
 *
 * @category    Ex
 * @package     Ex_Shell
 * @author      Suky <suky3plex@outlook.com> (http://ex.pendabl.es)
 * @license     http://unlicense.org  Unlicensed Free Software
 */
class Ex_Shell_SimulateStockUpdates extends Ex_Shell_Abstract
{

    protected $_qty;

    /**
     * Run script
     */
    public function run()
    {
        $i = 1;
        $n = 100;

        $this->_qty = rand(100,999);

        /**
         * Fetch number of iterations as argument
         */
        if($this->getArg('n')){
            $n = $this->getArg('n');
        }

        while ($i <= $n) {
            $this->_modelStock($this->_getRandProductId(), $this->_qty);
            echo microtime(true) . ": $i \n";
            $i++;
        }
    }

    /**
     * Update stock
     * @param $productId
     * @param $qty
     * @throws Exception
     */
    private function _modelStock($productId, $qty)
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);

        if (!$stockItem->getId()) {
            $stockItem->setData('product_id', $productId);
            $stockItem->setData('stock_id', 1);
        }

        $stockItem->setData('is_in_stock', 1);     // is 0 or 1
        $stockItem->setData('manage_stock', 1);    // should be 1 to make something out of stock
        $stockItem->setData('qty', $qty);

        try {
            $stockItem->save();
        } catch (Exception $e) {
            echo "{$e}";
        }

        /** Mage_CatalogInventory_Model_Resource_Indexer_Stock */
        Mage::getResourceModel('cataloginventory/indexer_stock')->reindexProducts($productId);
    }

    /**
     * @note: NOT USED
     * @param $_productId
     * @param $_qty
     */
    private function _updateStock($_productId, $_qty)
    {
        $writeConnection = $this->_getWriteConnection();

        $stockStatus = "UPDATE cataloginventory_stock_status SET qty = $_qty WHERE product_id = $_productId";
        $stockItem = "UPDATE cataloginventory_stock_item SET qty = $_qty WHERE product_id = $_productId";

        $writeConnection->query($stockStatus);
        $writeConnection->query($stockItem);

    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f simulateStockUpdates.php -- [options]

  --n <products>    Number of stock items that will be updated
  <products>        int

  help              This help

USAGE;
    }

}

$shell = new Ex_Shell_SimulateStockUpdates();
$shell->run();
