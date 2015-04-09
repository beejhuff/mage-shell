<?php

require_once 'abstract.php';
/**
 * Class Ex_Shell_Abstract
 *
 * @category    Ex
 * @package     Ex_Shell
 * @author      Suky <suky3plex@outlook.com> (http://ex.pendabl.es)
 * @license     http://unlicense.org  Unlicensed Free Software
 */
class Ex_Shell_Abstract extends Mage_Shell_Abstract
{

    public function run()
    {}

    /**
     * Get read connection
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * Get write connection
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * @return Mage_Core_Model_Resource_Transaction
     */
    protected function _getTransaction()
    {
        return Mage::getModel('core/resource_transaction');
    }
    /**
     * Returns id of random customer entity
     * @return string
     */
    protected function _getRandCustomerId()
    {
        $readConnection = $this->_getReadConnection();
        $select = $readConnection->query("SELECT entity_id FROM customer_entity ORDER BY RAND() LIMIT 1");

        return $select->fetchColumn();
    }

    /**
     * Returns id of random product entity
     * @return string
     */
    protected function _getRandProductId()
    {
        $readConnection = $this->_getReadConnection();
        $select = $readConnection->query("SELECT entity_id FROM catalog_product_entity WHERE type_id = 'simple' ORDER BY RAND() LIMIT 1");

        return $select->fetchColumn();
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f ex.php -- [options]

  -h            Short alias for help
  help          This help
USAGE;
    }

}

$shell = new Ex_Shell_Abstract();
$shell->run();
